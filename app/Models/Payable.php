<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payable extends Model
{
    protected $fillable = ['purchase_entry_id', 'party_id', 'amount', 'is_paid'];

    public function purchaseEntry()
    {
        return $this->belongsTo(PurchaseEntry::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }
}