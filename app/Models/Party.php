<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $fillable = ['name', 'gst_in', 'email', 'phone_number', 'address'];

    public function purchaseEntries()
    {
        return $this->hasMany(PurchaseEntry::class);
    }

    public function payables()
    {
        return $this->hasMany(Payable::class);
    }

     public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

      public function payments()
    {
        // We only want payments of type 'payable' associated with this party
        return $this->hasMany(Payment::class)->where('type', 'payable');
    }

    

}