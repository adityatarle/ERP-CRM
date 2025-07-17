<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptNoteItem extends Model
{
    protected $fillable = [
        'receipt_note_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'cgst_rate',
        'sgst_rate',
        'igst_rate',
        'item_code',
        'hsn',
        'status',
        'gst_rate',
        'gst_type',
        'created_at',
        'updated_at',
    ];

    public function receiptNote()
    {
        return $this->belongsTo(ReceiptNote::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
