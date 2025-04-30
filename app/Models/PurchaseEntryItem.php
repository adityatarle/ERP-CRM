<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseEntryItem extends Model
{
    protected $fillable = ['purchase_entry_id', 'product_id', 'quantity', 'unit_price', 'total_price', 'gst_rate', 'gst_type'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseEntry()
    {
        return $this->belongsTo(PurchaseEntry::class);
    }
}