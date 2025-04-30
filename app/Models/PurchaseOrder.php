<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['purchase_order_number', 'party_id', 'order_date', 'status'];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}