<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'sale_id',
        'issue_date',
        'due_date',
        'purchase_number', 
        'purchase_date',
        'contact_person',
        'subtotal',
        'tax',
        'gst_type',
        'gst',
        'cgst', // Add this
        'sgst', // Add this
        'igst',
        'total',
        'description',
        'status',
        'edit_request_status',
        'requested_by_id',
        'unlock_reason',
        'unlock_decision_by_id',
        'unlock_decision_at',
        'unlock_decision_reason',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'invoice_sale', 'invoice_id', 'sale_id');
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
