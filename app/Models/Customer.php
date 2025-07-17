<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory; // Keep this if you use factories

    protected $fillable = ['name', 'email', 'phone', 'address', 'gst_number', 'city', 'pan_number'];

    /**
     * Get all of the sales associated with the customer.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get all of the invoices associated with the customer.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all payments received from the customer.
     */
    public function payments()
    {
        // We only want payments of type 'receivable' for customers
        return $this->hasMany(Payment::class)->where('type', 'receivable');
    }

    /**
     * Get all outstanding receivables for the customer.
     */
    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }
}