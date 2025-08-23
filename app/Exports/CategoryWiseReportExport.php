<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\PurchaseEntryItem;
use App\Models\SaleItem;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class CategoryWiseReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithColumnFormatting, WithEvents, WithMultipleSheets
{
    protected $categoryStats;
    protected $grandTotals;

    public function __construct()
    {
        // Calculate the same data as the controller
        $this->calculateCategoryStats();
    }

    private function calculateCategoryStats()
    {
        try {
            // --- 1. Calculate Average Purchase Cost for All Products ---
            $allPurchaseItems = PurchaseEntryItem::where('status', 'received')->get();
            $productCosts = [];

            // Group purchases by product to calculate weighted average cost efficiently
            $groupedPurchases = $allPurchaseItems->groupBy('product_id');

            foreach ($groupedPurchases as $productId => $items) {
                $totalCost = $items->sum(function($item) {
                    return ($item->unit_price * (1 - ($item->discount ?? 0) / 100)) * $item->quantity;
                });
                $totalQuantity = $items->sum('quantity');

                $productCosts[$productId] = $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;
            }

            // --- 2. Get All Sale Items with Product and Category Information ---
            $saleItems = SaleItem::with(['product:id,name,category,subcategory,item_code,hsn'])
                ->whereHas('product', function($query) {
                    $query->whereNotNull('category');
                })
                ->get();

        // --- 3. Group by Category and Calculate Stats ---
        $categoryStats = [];
        $grandTotalRevenue = 0;
        $grandTotalCogs = 0;
        $grandTotalQuantity = 0;

        // Group sales by category
        $salesByCategory = $saleItems->groupBy('product.category');

        foreach ($salesByCategory as $category => $items) {
            $categoryRevenue = 0;
            $categoryCogs = 0;
            $categoryQuantity = 0;
            $productCount = 0;
            $subcategories = [];

            foreach ($items as $item) {
                if (!$item->product) continue;

                $quantity = $item->quantity;
                $revenue = $quantity * $item->unit_price;
                $averageCost = $productCosts[$item->product_id] ?? 0;
                $cogs = $averageCost * $quantity;

                $categoryRevenue += $revenue;
                $categoryCogs += $cogs;
                $categoryQuantity += $quantity;

                // Track unique products and subcategories
                if (!in_array($item->product->id, array_column($subcategories, 'product_id'))) {
                    $productCount++;
                }

                if ($item->product->subcategory && !in_array($item->product->subcategory, array_column($subcategories, 'subcategory'))) {
                    $subcategories[] = [
                        'subcategory' => $item->product->subcategory,
                        'product_id' => $item->product->id
                    ];
                }
            }

            $categoryProfit = $categoryRevenue - $categoryCogs;
            $profitMargin = $categoryRevenue > 0 ? ($categoryProfit / $categoryRevenue) * 100 : 0;

            $categoryStats[] = (object) [
                'category' => $category,
                'subcategories' => collect($subcategories)->pluck('subcategory')->unique()->filter()->values(),
                'product_count' => $productCount,
                'total_quantity_sold' => $categoryQuantity,
                'total_revenue' => $categoryRevenue,
                'total_cogs' => $categoryCogs,
                'profit_loss' => $categoryProfit,
                'profit_margin' => $profitMargin,
            ];

            // Add to grand totals
            $grandTotalRevenue += $categoryRevenue;
            $grandTotalCogs += $categoryCogs;
            $grandTotalQuantity += $categoryQuantity;
        }

        $grandTotalProfit = $grandTotalRevenue - $grandTotalCogs;
        $grandTotalProfitMargin = $grandTotalRevenue > 0 ? ($grandTotalProfit / $grandTotalRevenue) * 100 : 0;

        // Sort categories by most profitable
        usort($categoryStats, function($a, $b) {
            return $b->profit_loss <=> $a->profit_loss;
        });

        $this->categoryStats = $categoryStats;
        $this->grandTotals = (object) [
            'revenue' => $grandTotalRevenue,
            'cogs' => $grandTotalCogs,
            'profit' => $grandTotalProfit,
            'quantity' => $grandTotalQuantity,
            'profit_margin' => $grandTotalProfitMargin
        ];
        
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in CategoryWiseReportExport: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Set empty data to prevent further errors
            $this->categoryStats = [];
            $this->grandTotals = (object) [
                'revenue' => 0,
                'cogs' => 0,
                'profit' => 0,
                'quantity' => 0,
                'profit_margin' => 0
            ];
        }
    }

    public function collection()
    {
        return collect($this->categoryStats);
    }

    public function headings(): array
    {
        return [
            'Category',
            'Subcategories',
            'Product Count',
            'Total Quantity Sold',
            'Total Revenue (₹)',
            'Total COGS (₹)',
            'Profit/Loss (₹)',
            'Profit Margin (%)',
        ];
    }

    public function map($row): array
    {
        return [
            $row->category,
            $row->subcategories->implode(', '),
            $row->product_count,
            $row->total_quantity_sold,
            $row->total_revenue,
            $row->total_cogs,
            $row->profit_loss,
            round($row->profit_margin, 2),
        ];
    }

    public function title(): string
    {
        return 'Category Report';
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
            'E' => '#,##0.00',
            'F' => '#,##0.00',
            'G' => '#,##0.00',
            'H' => '0.00',
        ];
    }

    public function sheets(): array
    {
        return [
            'Category Summary' => new CategorySummarySheet($this->categoryStats, $this->grandTotals),
            'Detailed Analysis' => new CategoryDetailedSheet($this->categoryStats),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Add borders to all cells
                $event->sheet->getStyle('A1:H' . (count($this->categoryStats) + 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Style profit/loss column
                $event->sheet->getStyle('G2:G' . (count($this->categoryStats) + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                // Add conditional formatting for profit/loss
                foreach (range(2, count($this->categoryStats) + 1) as $row) {
                    $profitValue = $event->sheet->getCell('G' . $row)->getValue();
                    if ($profitValue > 0) {
                        $event->sheet->getStyle('G' . $row)->getFont()->setColor(new Color('008000'));
                    } elseif ($profitValue < 0) {
                        $event->sheet->getStyle('G' . $row)->getFont()->setColor(new Color('FF0000'));
                    }
                }
            },
        ];
    }
}

class CategorySummarySheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $categoryStats;
    protected $grandTotals;

    public function __construct($categoryStats, $grandTotals)
    {
        $this->categoryStats = $categoryStats;
        $this->grandTotals = $grandTotals;
    }

    public function collection()
    {
        return collect($this->categoryStats);
    }

    public function headings(): array
    {
        return [
            'Category',
            'Subcategories',
            'Product Count',
            'Total Quantity Sold',
            'Total Revenue (₹)',
            'Total COGS (₹)',
            'Profit/Loss (₹)',
            'Profit Margin (%)',
        ];
    }

    public function map($row): array
    {
        return [
            $row->category,
            $row->subcategories->implode(', '),
            $row->product_count,
            $row->total_quantity_sold,
            $row->total_revenue,
            $row->total_cogs,
            $row->profit_loss,
            round($row->profit_margin, 2),
        ];
    }

    public function title(): string
    {
        return 'Category Summary';
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
            'E' => '#,##0.00',
            'F' => '#,##0.00',
            'G' => '#,##0.00',
            'H' => '0.00',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = count($this->categoryStats) + 1;
                
                // Style headers
                $event->sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Add grand totals row
                $event->sheet->setCellValue('A' . ($lastRow + 2), 'GRAND TOTALS');
                $event->sheet->setCellValue('D' . ($lastRow + 2), $this->grandTotals->quantity);
                $event->sheet->setCellValue('E' . ($lastRow + 2), $this->grandTotals->revenue);
                $event->sheet->setCellValue('F' . ($lastRow + 2), $this->grandTotals->cogs);
                $event->sheet->setCellValue('G' . ($lastRow + 2), $this->grandTotals->profit);
                $event->sheet->setCellValue('H' . ($lastRow + 2), round($this->grandTotals->profit_margin, 2));

                // Style grand totals row
                $event->sheet->getStyle('A' . ($lastRow + 2) . ':H' . ($lastRow + 2))->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E75B6'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Add borders
                $event->sheet->getStyle('A1:H' . ($lastRow + 2))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Style profit/loss column
                $event->sheet->getStyle('G2:G' . $lastRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                // Conditional formatting for profit/loss
                foreach (range(2, $lastRow) as $row) {
                    $profitValue = $event->sheet->getCell('G' . $row)->getValue();
                    if ($profitValue > 0) {
                        $event->sheet->getStyle('G' . $row)->getFont()->setColor(new Color('008000'));
                    } elseif ($profitValue < 0) {
                        $event->sheet->getStyle('G' . $row)->getFont()->setColor(new Color('FF0000'));
                    }
                }
            },
        ];
    }
}

class CategoryDetailedSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $categoryStats;

    public function __construct($categoryStats)
    {
        $this->categoryStats = $categoryStats;
    }

    public function collection()
    {
        $detailedData = [];
        
        foreach ($this->categoryStats as $category) {
            // Add category header
            $detailedData[] = [
                'type' => 'header',
                'category' => $category->category,
                'subcategories' => '',
                'product_count' => '',
                'total_quantity_sold' => '',
                'total_revenue' => '',
                'total_cogs' => '',
                'profit_loss' => '',
                'profit_margin' => '',
            ];

            // Add subcategory details
            foreach ($category->subcategories as $subcategory) {
                $detailedData[] = [
                    'type' => 'subcategory',
                    'category' => '',
                    'subcategories' => $subcategory,
                    'product_count' => '',
                    'total_quantity_sold' => '',
                    'total_revenue' => '',
                    'total_cogs' => '',
                    'profit_loss' => '',
                    'profit_margin' => '',
                ];
            }

            // Add category summary
            $detailedData[] = [
                'type' => 'summary',
                'category' => '',
                'subcategories' => 'TOTAL',
                'product_count' => $category->product_count,
                'total_quantity_sold' => $category->total_quantity_sold,
                'total_revenue' => $category->total_revenue,
                'total_cogs' => $category->total_cogs,
                'profit_loss' => $category->profit_loss,
                'profit_margin' => round($category->profit_margin, 2),
            ];

            // Add empty row for spacing
            $detailedData[] = [
                'type' => 'spacer',
                'category' => '',
                'subcategories' => '',
                'product_count' => '',
                'total_quantity_sold' => '',
                'total_revenue' => '',
                'total_cogs' => '',
                'profit_loss' => '',
                'profit_margin' => '',
            ];
        }

        return collect($detailedData);
    }

    public function headings(): array
    {
        return [
            'Category',
            'Subcategories',
            'Product Count',
            'Total Quantity Sold',
            'Total Revenue (₹)',
            'Total COGS (₹)',
            'Profit/Loss (₹)',
            'Profit Margin (%)',
        ];
    }

    public function map($row): array
    {
        return [
            $row['category'],
            $row['subcategories'],
            $row['product_count'],
            $row['total_quantity_sold'],
            $row['total_revenue'],
            $row['total_cogs'],
            $row['profit_loss'],
            $row['profit_margin'],
        ];
    }

    public function title(): string
    {
        return 'Detailed Analysis';
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
            'E' => '#,##0.00',
            'F' => '#,##0.00',
            'G' => '#,##0.00',
            'H' => '0.00',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = $event->sheet->getHighestRow();
                
                // Style headers
                $event->sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Style different row types
                for ($row = 2; $row <= $lastRow; $row++) {
                    $category = $event->sheet->getCell('A' . $row)->getValue();
                    $subcategory = $event->sheet->getCell('B' . $row)->getValue();
                    
                    if ($category && $subcategory === '') {
                        // Category header row
                        $event->sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '70AD47'],
                            ],
                        ]);
                    } elseif ($subcategory === 'TOTAL') {
                        // Category summary row
                        $event->sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFE699'],
                            ],
                        ]);
                    }
                }

                // Add borders
                $event->sheet->getStyle('A1:H' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}