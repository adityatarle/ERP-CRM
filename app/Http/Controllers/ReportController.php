<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PurchaseEntryItem;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display a sales and profit/loss report.
     * This method is computationally intensive and should be used by admins.
     */
    public function salesProfitLoss()
    {
        // --- 1. Calculate Average Purchase Cost for All Products ---
        // This is the same consistent COGS logic from your dashboard.
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

        // --- 2. Get All Sale Items and Group by Product ---
        $saleItems = SaleItem::with('product')->get()->groupBy('product.id');
        
        $productStats = [];
        $grandTotalRevenue = 0;
        $grandTotalCogs = 0;

        // --- 3. Process Each Product's Sales ---
        foreach ($saleItems as $productId => $items) {
            if ($items->isEmpty() || !$items->first()->product) {
                continue; // Skip if there's no product associated
            }

            $totalQuantitySold = $items->sum('quantity');
            $totalRevenue = $items->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });

            // Get pre-calculated average cost for this product
            $averageCostPerUnit = $productCosts[$productId] ?? 0;
            
            $totalCogsForProduct = $averageCostPerUnit * $totalQuantitySold;
            $profitOrLoss = $totalRevenue - $totalCogsForProduct;

            // Add to grand totals
            $grandTotalRevenue += $totalRevenue;
            $grandTotalCogs += $totalCogsForProduct;

            // Store stats for this product
            $productStats[] = (object) [
                'name' => $items->first()->product->name,
                'item_code' => $items->first()->product->item_code,
                'quantity_sold' => $totalQuantitySold,
                'total_revenue' => $totalRevenue,
                'total_cogs' => $totalCogsForProduct,
                'profit_loss' => $profitOrLoss,
            ];
        }

        $grandTotalProfit = $grandTotalRevenue - $grandTotalCogs;
        
        // Sort products by most profitable
        usort($productStats, function($a, $b) {
            return $b->profit_loss <=> $a->profit_loss;
        });

        return view('reports.sales_profit_loss', compact(
            'productStats', 
            'grandTotalRevenue', 
            'grandTotalCogs',
            'grandTotalProfit'
        ));
    }

    /**
     * Display a category-wise business report.
     * Groups sales data by product categories and shows performance metrics.
     */
    public function categoryWiseReport()
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

            // Debug: Check if we have sale items
            if ($saleItems->isEmpty()) {
                return view('reports.category_wise_report', [
                    'categoryStats' => [],
                    'grandTotalRevenue' => 0,
                    'grandTotalCogs' => 0,
                    'grandTotalProfit' => 0,
                    'grandTotalQuantity' => 0,
                    'grandTotalProfitMargin' => 0
                ]);
            }

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

                $categoryStats[] = [
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
                return $b['profit_loss'] <=> $a['profit_loss'];
            });

            // Convert array to collection for the view
            $categoryStats = collect($categoryStats);

            return view('reports.category_wise_report', compact(
                'categoryStats',
                'grandTotalRevenue',
                'grandTotalCogs',
                'grandTotalProfit',
                'grandTotalQuantity',
                'grandTotalProfitMargin'
            ));

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in categoryWiseReport: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return a simple error view or redirect
            return back()->with('error', 'An error occurred while generating the report. Please check the logs for details.');
        }
    }

    /**
     * Export category-wise business report to Excel.
     */
    public function categoryWiseExport()
    {
        $filename = 'category_wise_business_report_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CategoryWiseReportExport(),
            $filename
        );
    }

    /**
     * Get detailed information for a specific category (AJAX endpoint).
     */
    public function categoryDetails($category)
    {
        try {
            // Decode the category name from URL
            $categoryName = urldecode($category);
            
            // Get all sale items for this category
            $saleItems = SaleItem::with(['product:id,name,category,subcategory,item_code,hsn'])
                ->whereHas('product', function($query) use ($categoryName) {
                    $query->where('category', $categoryName);
                })
                ->get();

            if ($saleItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found for this category'
                ]);
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
            $totalRevenue = 0;
            $totalCogs = 0;

            foreach ($salesByProduct as $productId => $items) {
                if (!$items->first()->product) continue;

                $product = $items->first()->product;
                $totalQuantity = $items->sum('quantity');
                $totalProductRevenue = $items->sum(function($item) {
                    return $item->quantity * $item->unit_price;
                });
                $averageCost = $productCosts[$productId] ?? 0;
                $totalProductCogs = $averageCost * $totalQuantity;

                $products[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'item_code' => $product->item_code,
                    'subcategory' => $product->subcategory,
                    'quantity_sold' => $totalQuantity,
                    'revenue' => $totalProductRevenue,
                    'cogs' => $totalProductCogs,
                ];

                $totalRevenue += $totalProductRevenue;
                $totalCogs += $totalProductCogs;
            }

            $totalProfit = $totalRevenue - $totalCogs;
            $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

            // Sort products by revenue (highest first)
            usort($products, function($a, $b) {
                return $b['revenue'] <=> $a['revenue'];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $categoryName,
                    'totalRevenue' => $totalRevenue,
                    'totalCogs' => $totalCogs,
                    'totalProfit' => $totalProfit,
                    'profitMargin' => $profitMargin,
                    'products' => $products
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in categoryDetails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching category details'
            ], 500);
        }
    }

    /**
     * Export detailed information for a specific category to Excel.
     */
    public function categoryDetailsExport($category)
    {
        try {
            $categoryName = urldecode($category);
            $filename = 'category_details_' . str_replace(' ', '_', $categoryName) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\CategoryDetailsExport($categoryName),
                $filename
            );
        } catch (\Exception $e) {
            \Log::error('Error in categoryDetailsExport: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while exporting category details');
        }
    }
}