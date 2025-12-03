<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Exception;

class SaleService extends BaseService
{
    /**
     * Create a new sale with items
     */
    public function createSale(array $data): Sale
    {
        return $this->executeInTransaction(function () use ($data) {
            $this->validateRequired($data, ['customer_id', 'products']);
            
            // Validate products and calculate totals
            $saleData = $this->processSaleData($data);
            
            // Create sale
            $sale = Sale::create([
                'customer_id' => $data['customer_id'],
                'ref_no' => $data['ref_no'] ?? null,
                'total_price' => $saleData['total_price'],
                'status' => $data['status'] ?? 'pending'
            ]);

            // Create sale items and update stock
            $this->createSaleItems($sale, $saleData['items']);
            
            $this->logOperation('sale_created', [
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'total_price' => $sale->total_price
            ]);

            return $sale->load(['customer', 'saleItems.product']);
        }, 'create_sale');
    }

    /**
     * Process sale data and validate products
     */
    private function processSaleData(array $data): array
    {
        $totalPrice = 0;
        $items = [];

        foreach ($data['products'] as $index => $productData) {
            $product = Product::findOrFail($productData['product_id']);
            
            // Validate stock
            if ($product->stock < $productData['quantity']) {
                throw new Exception("Insufficient stock for product {$product->name}. Available: {$product->stock}, Required: {$productData['quantity']}");
            }

            // Calculate item total
            $itemTotal = $this->calculateTotalWithDiscount(
                $productData['sale_price'],
                $productData['quantity'],
                $productData['discount'] ?? 0
            );

            $items[] = [
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
                'unit_price' => $productData['sale_price'],
                'discount' => $productData['discount'] ?? 0,
                'total_price' => $itemTotal,
                'barcode' => $productData['barcode'] ?? null,
                'itemcode' => $productData['itemcode'] ?? null,
                'secondary_itemcode' => $productData['secondary_itemcode'] ?? null,
            ];

            $totalPrice += $itemTotal;
        }

        return [
            'total_price' => $totalPrice,
            'items' => $items
        ];
    }

    /**
     * Create sale items and update stock
     */
    private function createSaleItems(Sale $sale, array $items): void
    {
        foreach ($items as $itemData) {
            $saleItem = $sale->saleItems()->create($itemData);
            
            // Update product stock
            $product = Product::find($itemData['product_id']);
            $product->decrement('stock', $itemData['quantity']);
            
            $this->logOperation('sale_item_created', [
                'sale_item_id' => $saleItem->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'new_stock' => $product->fresh()->stock
            ]);
        }
    }

    /**
     * Update sale status
     */
    public function updateSaleStatus(Sale $sale, string $status): Sale
    {
        return $this->executeInTransaction(function () use ($sale, $status) {
            $oldStatus = $sale->status;
            $sale->update(['status' => $status]);
            
            $this->logOperation('sale_status_updated', [
                'sale_id' => $sale->id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);

            return $sale;
        }, 'update_sale_status');
    }

    /**
     * Get sales with filters
     */
    public function getSales(array $filters = []): array
    {
        $query = Sale::with(['customer', 'saleItems.product']);

        // Apply filters
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_range'])) {
            $this->applyDateFilters($query, $filters['date_range'], 'created_at');
        }

        if (isset($filters['search'])) {
            $this->buildSearchQuery($query, $filters['search'], ['ref_no']);
        }

        // Apply sorting
        $this->applySorting($query, $filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc');

        // Get results
        $sales = $this->paginateResults($query, $filters['per_page'] ?? 15);
        
        // Get summary stats
        $summaryQuery = Sale::query();
        if (isset($filters['date_range'])) {
            $this->applyDateFilters($summaryQuery, $filters['date_range'], 'created_at');
        }
        $summary = $this->getSummaryStats($summaryQuery, 'total_price');

        return [
            'sales' => $sales,
            'summary' => $summary
        ];
    }

    /**
     * Get sale statistics
     */
    public function getSaleStatistics(array $filters = []): array
    {
        $query = Sale::query();

        if (isset($filters['date_range'])) {
            $this->applyDateFilters($query, $filters['date_range'], 'created_at');
        }

        $totalSales = $query->count();
        $totalRevenue = $query->sum('total_price');
        $averageSale = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Sales by status
        $salesByStatus = $query->groupBy('status')
            ->selectRaw('status, count(*) as count, sum(total_price) as total')
            ->get()
            ->keyBy('status');

        // Top customers
        $topCustomers = Sale::with('customer')
            ->selectRaw('customer_id, count(*) as sales_count, sum(total_price) as total_revenue')
            ->groupBy('customer_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_sales' => $totalSales,
            'total_revenue' => $totalRevenue,
            'average_sale' => $averageSale,
            'sales_by_status' => $salesByStatus,
            'top_customers' => $topCustomers
        ];
    }

    /**
     * Cancel sale and restore stock
     */
    public function cancelSale(Sale $sale): Sale
    {
        return $this->executeInTransaction(function () use ($sale) {
            // Restore stock for all items
            foreach ($sale->saleItems as $item) {
                $product = Product::find($item->product_id);
                $product->increment('stock', $item->quantity);
            }

            // Update sale status
            $sale->update(['status' => 'cancelled']);

            $this->logOperation('sale_cancelled', [
                'sale_id' => $sale->id,
                'stock_restored' => true
            ]);

            return $sale;
        }, 'cancel_sale');
    }
}
