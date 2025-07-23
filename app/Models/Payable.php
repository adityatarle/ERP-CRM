<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payable extends Model
{
    protected $fillable = ['purchase_entry_id', 'party_id', 'amount', 'is_paid', 'invoice_number', 'invoice_date'];

    protected $casts = [
        'invoice_date' => 'date',
        'is_paid' => 'boolean',
    ];

    public function purchaseEntry()
    {
        return $this->belongsTo(PurchaseEntry::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }
}