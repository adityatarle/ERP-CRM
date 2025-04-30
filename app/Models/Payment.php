<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['purchase_entry_id', 'party_id', 'amount', 'payment_date', 'notes'];

    public function purchaseEntry()
    {
        return $this->belongsTo(PurchaseEntry::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }
}
