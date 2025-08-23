<?php

namespace App\Exports;

use App\Models\SaleItem;
use App\Models\PurchaseEntryItem;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CategoryDetailsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $categoryName;
    protected $products;

    public function __construct($categoryName)
    {
        $this->categoryName = $categoryName;
        $this->loadCategoryData();
    }

    private function loadCategoryData()
    {
        try {
            // Get all sale items for this category
            $saleItems = SaleItem::with(['product:id,name,category,subcategory,item_code,hsn'])
                ->whereHas('product', function($query) {
                    $query->where('category', $this->categoryName);
                })
                ->get();

            if ($saleItems->isEmpty()) {
                $this->products = [];
                return;
            }

            // Calculate product costs
            $allPurchaseItems = PurchaseEntryItem::where('status', 'received')->get();
            $productCosts = [];

            $groupedPurchases = $allPurchaseItems->groupBy('product_id');
            foreach ($groupedPurchases as $productId => $items) {
                $totalCost = $items->sum(function($item) {
                    return ($item->unit_price * (1 - ($item->discount ?? 0) / 100)) * $item->quantity;
                });
                $totalQuantity = $items->sum('quantity');
                $productCosts[$productId] = $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;
            }

            // Group sales by product
            $salesByProduct = $saleItems->groupBy('product_id');
            $products = [];

            foreach ($salesByProduct as $productId => $items) {
                if (!$items->first()->product) continue;

                $product = $items->first()->product;
                $totalQuantity = $items->sum('quantity');
                $totalProductRevenue = $items->sum(function($item) {
                    return $item->quantity * $item->unit_price;
                });
                $averageCost = $productCosts[$productId] ?? 0;
                $totalProductCogs = $averageCost * $totalQuantity;
                $profit = $totalProductRevenue - $totalProductCogs;
                $profitMargin = $totalProductRevenue > 0 ? ($profit / $totalProductRevenue) * 100 : 0;

                $products[] = [
                    'name' => $product->name,
                    'item_code' => $product->item_code,
                    'subcategory' => $product->subcategory,
                    'quantity_sold' => $totalQuantity,
                    'revenue' => $totalProductRevenue,
                    'cogs' => $totalProductCogs,
                    'profit' => $profit,
                    'profit_margin' => $profitMargin,
                ];
            }

            // Sort products by revenue (highest first)
            usort($products, function($a, $b) {
                return $b['revenue'] <=> $a['revenue'];
            });

            $this->products = $products;

        } catch (\Exception $e) {
            \Log::error('Error in CategoryDetailsExport: ' . $e->getMessage());
            $this->products = [];
        }
    }

    public function collection()
    {
        return collect($this->products);
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Item Code',
            'Subcategory',
            'Quantity Sold',
            'Revenue (₹)',
            'COGS (₹)',
            'Profit (₹)',
            'Profit Margin (%)',
        ];
    }

    public function map($row): array
    {
        return [
            $row['name'],
            $row['item_code'] ?? 'N/A',
            $row['subcategory'] ?? 'N/A',
            $row['quantity_sold'],
            $row['revenue'],
            $row['cogs'],
            $row['profit'],
            round($row['profit_margin'], 2),
        ];
    }

    public function title(): string
    {
        return 'Category Details';
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
                $lastRow = count($this->products) + 1;
                
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

                // Add category summary row
                $event->sheet->setCellValue('A' . ($lastRow + 2), 'CATEGORY SUMMARY');
                $event->sheet->setCellValue('A' . ($lastRow + 3), 'Category: ' . $this->categoryName);
                
                // Calculate totals
                $totalRevenue = collect($this->products)->sum('revenue');
                $totalCogs = collect($this->products)->sum('cogs');
                $totalProfit = collect($this->products)->sum('profit');
                $totalQuantity = collect($this->products)->sum('quantity_sold');
                $avgProfitMargin = collect($this->products)->avg('profit_margin');

                $event->sheet->setCellValue('A' . ($lastRow + 4), 'Total Quantity Sold: ' . $totalQuantity);
                $event->sheet->setCellValue('A' . ($lastRow + 5), 'Total Revenue: ₹' . number_format($totalRevenue, 2));
                $event->sheet->setCellValue('A' . ($lastRow + 6), 'Total COGS: ₹' . number_format($totalCogs, 2));
                $event->sheet->setCellValue('A' . ($lastRow + 7), 'Total Profit: ₹' . number_format($totalProfit, 2));
                $event->sheet->setCellValue('A' . ($lastRow + 8), 'Average Profit Margin: ' . number_format($avgProfitMargin, 2) . '%');

                // Style summary section
                $event->sheet->getStyle('A' . ($lastRow + 2) . ':A' . ($lastRow + 8))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E8F4FD'],
                    ],
                ]);

                // Add borders
                $event->sheet->getStyle('A1:H' . $lastRow)->applyFromArray([
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

                // Auto-size columns
                foreach (range('A', 'H') as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}