<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['purchase_entry_id', 'party_id', 'sale_id', 'customer_id', 'type', 'amount', 'payment_date', 'notes','tds_amount' ,'bank_name','type'];

    public function purchaseEntry()
    {
        return $this->belongsTo(PurchaseEntry::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
