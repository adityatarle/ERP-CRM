<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // --- Core Fields ---
        'type',             // 'payable' or 'receivable'
        'amount',
        'payment_date',
        'bank_name',
        'notes',

        // --- Receivable Payment Fields ---
        'invoice_id',       // NEW: Direct link to the Invoice
        'customer_id',
        'tds_amount',
        
        // --- Payable Payment Fields ---
        'purchase_entry_id',
        'party_id',

        // --- Legacy Field (can be made nullable or removed later) ---
        'sale_id', 
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'tds_amount' => 'decimal:2',
    ];

    // --- RELATIONSHIPS ---

    /**
     * Get the invoice that this payment was made for (for receivables).
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    
    /**
     * Get the purchase entry that this payment was for (for payables).
     */
    public function purchaseEntry()
    {
        return $this->belongsTo(PurchaseEntry::class);
    }
    
    /**
     * Get the customer who made the payment (for receivables).
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the party that received the payment (for payables).
     */
    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * Legacy relationship to Sale. Can be removed if you migrate all data
     * and no longer need to reference sales directly from payments.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}