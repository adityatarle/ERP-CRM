<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseEntryItem extends Model
{
    protected $fillable = ['purchase_entry_id', 'product_id', 'quantity', 'unit_price', 'total_price', 'gst_rate','cgst_rate',
        'sgst_rate',
        'igst_rate', 'gst_type','discount','status','item_code',
        'hsn'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseEntry()
    {
        return $this->belongsTo(PurchaseEntry::class);
    }
}