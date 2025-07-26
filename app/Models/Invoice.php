<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory; // It's good practice to include the HasFactory trait.

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'sale_id', // Note: You might remove this if using the many-to-many 'sales' relationship exclusively
        'issue_date',
        'due_date',
        'purchase_number',
        'purchase_date',
        'contact_person',
        'subtotal',
        'tax',
        'gst_type',
        'gst',
        'cgst',
        'sgst',
        'igst',
        'total',
        'description',
        'status', // This might refer to invoice status like 'approved', 'on_hold'
        
        // --- NEW PAYMENT FIELDS ---
        'amount_paid',
        'payment_status', // This will be 'unpaid', 'partially_paid', 'paid'
        
        // --- EDIT UNLOCK REQUEST FIELDS ---
        'edit_request_status',
        'requested_by_id',
        'unlock_reason',
        'unlock_decision_by_id',
        'unlock_decision_at',
        'unlock_decision_reason',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'purchase_date' => 'date',
        'unlock_decision_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2', // Important for calculations
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'amount_due',
    ];

    /**
     * Calculate the remaining amount due for the invoice.
     *
     * @return float
     */
    public function getAmountDueAttribute(): float
    {
        return (float) $this->total - (float) $this->amount_paid;
    }

    // --- RELATIONSHIPS ---

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all payments made against this invoice.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * This defines the many-to-many relationship with Sales via the pivot table.
     */
    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'invoice_sale', 'invoice_id', 'sale_id');
    }

    /**
     * If you have a direct `sale_id` column on the invoices table for a one-to-one or one-to-many primary sale.
     * If you only use the many-to-many `sales()` relationship, you can remove this.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function unlockDecisionMaker()
    {
        return $this->belongsTo(User::class, 'unlock_decision_by_id');
    }
}