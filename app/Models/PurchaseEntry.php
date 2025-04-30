<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseEntry extends Model
{
    protected $fillable = ['purchase_number', 'purchase_date', 'invoice_number', 'invoice_date', 'party_id', 'note'];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseEntryItem::class);
    }
}