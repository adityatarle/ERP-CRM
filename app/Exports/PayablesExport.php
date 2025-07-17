<?php

namespace App\Exports;

use App\Models\Payable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PayablesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        Log::info('PayablesExport initialized with date range', [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);
    }

    public function collection()
    {
        $query = Payable::with(['purchaseEntry', 'purchaseEntry.items.product', 'party'])
            ->where('is_paid', false)
            ->join('purchase_entries', 'payables.purchase_entry_id', '=', 'purchase_entries.id');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween(\DB::raw('DATE(purchase_entries.purchase_date)'), [$this->startDate, $this->endDate]);
        } else {
            Log::warning('PayablesExport: No date range provided, exporting all records');
        }

        $payables = $query->select('payables.*')->get();

        // Flatten the collection to include one row per item, with special handling for the first item
        $flattened = collect();
        foreach ($payables as $payable) {
            if ($payable->purchaseEntry && $payable->purchaseEntry->items && $payable->purchaseEntry->items->isNotEmpty()) {
                $items = $payable->purchaseEntry->items;
                $totalItemsPrice = $items->sum('total_price'); // Sum of total_price for all items

                foreach ($items as $index => $item) {
                    $flattened->push((object) [
                        'payable' => $payable,
                        'item' => $item,
                        'is_first_item' => $index === 0,
                        'total_items_price' => $totalItemsPrice,
                    ]);
                }
            } else {
                // Include a row for payables with no items
                $flattened->push((object) [
                    'payable' => $payable,
                    'item' => null,
                    'is_first_item' => true,
                    'total_items_price' => 0,
                ]);
            }
        }

        Log::info('Exporting payables to Excel', [
            'count' => $flattened->count(),
            'payables' => $flattened->map(function ($row) {
                return [
                    'payable_id' => $row->payable->id,
                    'purchase_entry_id' => $row->payable->purchase_entry_id,
                    'purchase_date' => $row->payable->purchaseEntry ? $row->payable->purchaseEntry->purchase_date : null,
                    'party_id' => $row->payable->party_id,
                    'item_id' => $row->item ? $row->item->id : null,
                    'is_first_item' => $row->is_first_item,
                ];
            })->toArray(),
        ]);

        return $flattened;
    }

    public function headings(): array
    {
        return [
            'Purchase Number',
            'Party Invoice No',
            'Party',
            'GST Number',
            'Item Code',
            'HSN',
            'Quantity',
            'Rate',
            'Total',
            'Purchase GST',
            'Discount',
            'CGST',
            'SGST',
            'IGST',
            'Payable Amount',
            'Status',
        ];
    }

    public function map($row): array
    {
        $payable = $row->payable;
        $item = $row->item;
        $isFirstItem = $row->is_first_item;
        $totalItemsPrice = $row->total_items_price;

        // Only include purchase-level details in the first item row
        return [
            $isFirstItem ? ($payable->purchaseEntry->purchase_number ?? 'N/A') : '',
            $isFirstItem ? ($payable->purchaseEntry->invoice_number ?? 'N/A') : '',
            $isFirstItem ? ($payable->party->name ?? 'N/A') : '',
            $isFirstItem ? ($payable->party->gst_in ?? 'N/A') : '',
            $item && $item->product ? ($item->product->item_code ?? 'N/A') : 'N/A',
            $item && $item->product ? ($item->product->hsn ?? 'N/A') : 'N/A',
            $item ? ($item->quantity ?? 0) : 0,
            $item ? number_format($item->unit_price, 2) : '0.00',
            $item ? number_format($item->total_price, 2) : '0.00', // Individual item total_price
            $isFirstItem ? ($payable->purchaseEntry->gst_amount ? number_format($payable->purchaseEntry->gst_amount, 2) : '0.00') : '',
            $isFirstItem ? ($payable->purchaseEntry->discount ? number_format($payable->purchaseEntry->discount, 2) : '0.00') : '',
            $isFirstItem ? ($payable->purchaseEntry->cgst ? number_format($payable->purchaseEntry->cgst, 2) : '0.00') : '',
            $isFirstItem ? ($payable->purchaseEntry->sgst ? number_format($payable->purchaseEntry->sgst, 2) : '0.00') : '',
            $isFirstItem ? ($payable->purchaseEntry->igst ? number_format($payable->purchaseEntry->igst, 2) : '0.00') : '',
            $isFirstItem ? number_format($payable->amount, 2) : '',
            $isFirstItem ? ($payable->is_paid ? 'Paid' : 'Pending') : '',
        ];
    }
}