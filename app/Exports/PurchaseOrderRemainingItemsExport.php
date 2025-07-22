<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Log;

class PurchaseOrderRemainingItemsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $status;
    protected $startDate;
    protected $endDate;
    protected $partyId;

    public function __construct($status = null, $startDate = null, $endDate = null, $partyId = null)
    {
        $this->status = $status;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->partyId = $partyId;
    }

    public function collection()
    {
        $query = PurchaseOrder::with([
            'party', 
            'items.product', 
            'receiptNoteItems', 
            'purchaseEntryItems'
        ]);

        // Apply filters
        if ($this->status && $this->status !== 'all') {
            // We'll filter by status after loading to use the accessor
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('order_date', [$this->startDate, $this->endDate]);
        }

        if ($this->partyId) {
            $query->where('party_id', $this->partyId);
        }

        $purchaseOrders = $query->get();

        // Filter by status using the accessor if needed
        if ($this->status && $this->status !== 'all') {
            $purchaseOrders = $purchaseOrders->filter(function ($po) {
                return strtolower($po->receipt_status) === strtolower($this->status);
            });
        }

        // Flatten the collection to include one row per remaining item
        $flattened = collect();

        foreach ($purchaseOrders as $po) {
            // Calculate received quantities from both sources
            $receivedViaNote = $po->receiptNoteItems
                ->where('status', 'received')
                ->groupBy('product_id')
                ->map(fn($items) => $items->sum('quantity'));
            
            $receivedViaEntry = $po->purchaseEntryItems
                ->where('status', 'received')
                ->groupBy('product_id')
                ->map(fn($items) => $items->sum('quantity'));

            foreach ($po->items as $item) {
                $fromNote = $receivedViaNote->get($item->product_id, 0);
                $fromEntry = $receivedViaEntry->get($item->product_id, 0);
                $totalReceived = $fromNote + $fromEntry;
                $remaining = $item->quantity - $totalReceived;

                // Only include items with remaining quantity > 0
                if ($remaining > 0) {
                    $flattened->push((object) [
                        'purchase_order' => $po,
                        'item' => $item,
                        'total_received' => $totalReceived,
                        'remaining_quantity' => $remaining,
                    ]);
                }
            }
        }

        return $flattened;
    }

    public function headings(): array
    {
        return [
            'PO Number',
            'Party/Vendor',
            'Order Date',
            'Product Name',
            'Item Code',
            'HSN',
            'Ordered Quantity',
            'Received Quantity',
            'Remaining Quantity',
            'Unit Price',
            'Remaining Value',
            'Status',
        ];
    }

    public function map($row): array
    {
        $po = $row->purchase_order;
        $item = $row->item;
        $totalReceived = $row->total_received;
        $remaining = $row->remaining_quantity;

        return [
            $po->purchase_order_number,
            $po->party->name ?? 'N/A',
            $po->order_date ? $po->order_date->format('d M, Y') : 'N/A',
            $item->product->name ?? 'N/A',
            $item->product->item_code ?? 'N/A',
            $item->product->hsn ?? 'N/A',
            $item->quantity,
            $totalReceived,
            $remaining,
            number_format($item->unit_price, 2),
            number_format($remaining * $item->unit_price, 2),
            $po->receipt_status,
        ];
    }
}