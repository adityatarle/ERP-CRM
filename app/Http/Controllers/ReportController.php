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
                'sku' => $items->first()->product->sku,
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
}