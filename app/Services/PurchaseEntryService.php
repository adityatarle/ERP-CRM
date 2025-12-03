<?php

namespace App\Services;

use App\Models\PurchaseEntry;
use App\Models\PurchaseEntryItem;
use App\Models\Product;
use App\Models\Party;
use Illuminate\Support\Facades\Log;
use Exception;

class PurchaseEntryService extends BaseService
{
    /**
     * Create a new purchase entry with items
     */
    public function createPurchaseEntry(array $data): PurchaseEntry
    {
        return $this->executeInTransaction(function () use ($data) {
            $this->validateRequired($data, ['party_id', 'products']);
            
            // Process purchase entry data
            $purchaseData = $this->processPurchaseEntryData($data);
            
            // Create purchase entry
            $purchaseEntry = PurchaseEntry::create([
                'party_id' => $data['party_id'],
                'invoice_number' => $data['invoice_number'],
                'purchase_date' => $data['purchase_date'],
                'total_amount' => $purchaseData['total_amount'],
                'cash_discount' => $data['cash_discount'] ?? 0,
                'status' => $data['status'] ?? 'pending'
            ]);

            // Create purchase entry items and update stock
            $this->createPurchaseEntryItems($purchaseEntry, $purchaseData['items']);
            
            $this->logOperation('purchase_entry_created', [
                'purchase_entry_id' => $purchaseEntry->id,
                'party_id' => $purchaseEntry->party_id,
                'total_amount' => $purchaseEntry->total_amount
            ]);

            return $purchaseEntry->load(['party', 'items.product']);
        }, 'create_purchase_entry');
    }

    /**
     * Process purchase entry data and validate products
     */
    private function processPurchaseEntryData(array $data): array
    {
        $totalAmount = 0;
        $items = [];

        foreach ($data['products'] as $index => $productData) {
            $product = Product::findOrFail($productData['product_id']);
            
            // Calculate item total
            $itemTotal = $this->calculateTotalWithDiscount(
                $productData['purchase_price'],
                $productData['quantity'],
                $productData['discount'] ?? 0
            );

            $items[] = [
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
                'unit_price' => $productData['purchase_price'],
                'discount' => $productData['discount'] ?? 0,
                'total_price' => $itemTotal,
            ];

            $totalAmount += $itemTotal;
        }

        return [
            'total_amount' => $totalAmount,
            'items' => $items
        ];
    }

    /**
     * Create purchase entry items and update stock
     */
    private function createPurchaseEntryItems(PurchaseEntry $purchaseEntry, array $items): void
    {
        foreach ($items as $itemData) {
            $purchaseItem = $purchaseEntry->items()->create($itemData);
            
            // Update product stock
            $product = Product::find($itemData['product_id']);
            $product->increment('stock', $itemData['quantity']);
            
            $this->logOperation('purchase_entry_item_created', [
                'purchase_entry_item_id' => $purchaseItem->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'new_stock' => $product->fresh()->stock
            ]);
        }
    }

    /**
     * Update purchase entry status
     */
    public function updatePurchaseEntryStatus(PurchaseEntry $purchaseEntry, string $status): PurchaseEntry
    {
        return $this->executeInTransaction(function () use ($purchaseEntry, $status) {
            $oldStatus = $purchaseEntry->status;
            $purchaseEntry->update(['status' => $status]);
            
            $this->logOperation('purchase_entry_status_updated', [
                'purchase_entry_id' => $purchaseEntry->id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);

            return $purchaseEntry;
        }, 'update_purchase_entry_status');
    }

    /**
     * Get purchase entries with filters
     */
    public function getPurchaseEntries(array $filters = []): array
    {
        $query = PurchaseEntry::with(['party', 'items.product']);

        // Apply filters
        if (isset($filters['party_id'])) {
            $query->where('party_id', $filters['party_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['invoice_number'])) {
            $query->where('invoice_number', 'like', '%' . $filters['invoice_number'] . '%');
        }

        if (isset($filters['party_name'])) {
            $query->whereHas('party', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['party_name'] . '%');
            });
        }

        if (isset($filters['date_range'])) {
            $this->applyDateFilters($query, $filters['date_range'], 'purchase_date');
        }

        // Apply sorting
        $this->applySorting($query, $filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc');

        // Get results
        $purchaseEntries = $this->paginateResults($query, $filters['per_page'] ?? 15);
        
        // Calculate totals
        $filteredTotal = $query->clone()->get()->flatMap->items->sum('total_price');
        $overallTotal = PurchaseEntry::with('items')->get()->flatMap->items->sum('total_price');
        $overallCount = PurchaseEntry::count();

        return [
            'purchase_entries' => $purchaseEntries,
            'filtered_total' => $filteredTotal,
            'overall_total' => $overallTotal,
            'overall_count' => $overallCount
        ];
    }

    /**
     * Get purchase entry statistics
     */
    public function getPurchaseEntryStatistics(array $filters = []): array
    {
        $query = PurchaseEntry::query();

        if (isset($filters['date_range'])) {
            $this->applyDateFilters($query, $filters['date_range'], 'purchase_date');
        }

        $totalEntries = $query->count();
        $totalAmount = $query->with('items')->get()->flatMap->items->sum('total_price');
        $averageEntry = $totalEntries > 0 ? $totalAmount / $totalEntries : 0;

        // Purchase entries by status
        $entriesByStatus = $query->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->get()
            ->keyBy('status');

        // Top parties by purchase amount
        $topParties = PurchaseEntry::with('party')
            ->selectRaw('party_id, count(*) as entry_count')
            ->groupBy('party_id')
            ->orderBy('entry_count', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_entries' => $totalEntries,
            'total_amount' => $totalAmount,
            'average_entry' => $averageEntry,
            'entries_by_status' => $entriesByStatus,
            'top_parties' => $topParties
        ];
    }

    /**
     * Get purchase orders with remaining items for a party
     */
    public function getPurchaseOrdersWithRemainingItems(int $partyId): array
    {
        $party = Party::with(['purchaseOrders.items.product'])->findOrFail($partyId);
        
        $purchaseOrdersWithRemaining = [];
        
        foreach ($party->purchaseOrders as $purchaseOrder) {
            $remainingItems = [];
            
            foreach ($purchaseOrder->items as $item) {
                $purchasedQuantity = PurchaseEntryItem::whereHas('purchaseEntry', function ($query) use ($purchaseOrder) {
                    $query->where('purchase_order_id', $purchaseOrder->id);
                })->where('product_id', $item->product_id)->sum('quantity');
                
                $remainingQuantity = $item->quantity - $purchasedQuantity;
                
                if ($remainingQuantity > 0) {
                    $remainingItems[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'ordered_quantity' => $item->quantity,
                        'purchased_quantity' => $purchasedQuantity,
                        'remaining_quantity' => $remainingQuantity,
                        'unit_price' => $item->unit_price
                    ];
                }
            }
            
            if (!empty($remainingItems)) {
                $purchaseOrdersWithRemaining[] = [
                    'purchase_order' => $purchaseOrder,
                    'remaining_items' => $remainingItems
                ];
            }
        }
        
        return $purchaseOrdersWithRemaining;
    }

    /**
     * Cancel purchase entry and restore stock
     */
    public function cancelPurchaseEntry(PurchaseEntry $purchaseEntry): PurchaseEntry
    {
        return $this->executeInTransaction(function () use ($purchaseEntry) {
            // Restore stock for all items
            foreach ($purchaseEntry->items as $item) {
                $product = Product::find($item->product_id);
                $product->decrement('stock', $item->quantity);
            }

            // Update purchase entry status
            $purchaseEntry->update(['status' => 'cancelled']);

            $this->logOperation('purchase_entry_cancelled', [
                'purchase_entry_id' => $purchaseEntry->id,
                'stock_restored' => true
            ]);

            return $purchaseEntry;
        }, 'cancel_purchase_entry');
    }
}
