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

    protected $appends = [
        'total_paid',
        'remaining_amount',
        'payment_count',
        'payment_status'
    ];

    public function purchaseEntry()
    {
        return $this->belongsTo(PurchaseEntry::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * Get all payments made against this payable
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'purchase_entry_id', 'purchase_entry_id')
                    ->where('type', 'payable');
    }

    /**
     * Calculate total amount paid
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Calculate remaining amount
     */
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->amount - $this->total_paid);
    }

    /**
     * Count total payments
     */
    public function getPaymentCountAttribute()
    {
        return $this->payments()->count();
    }

    /**
     * Get payment status
     */
    public function getPaymentStatusAttribute()
    {
        if ($this->remaining_amount <= 0.01) {
            return 'paid';
        } elseif ($this->total_paid > 0) {
            return 'partially_paid';
        } else {
            return 'unpaid';
        }
    }
}