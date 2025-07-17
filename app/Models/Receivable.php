<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Receivable extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'customer_id',
        'invoice_id', // Add this
        'amount',
        'is_paid',
        'credit_days',
        'due_date',     // Add this
        // any other fields like notes, payment_method used for this receivable
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'due_date' => 'date',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Accessor for due days
    public function getDueDaysAttribute()
    {
        if ($this->is_paid) {
            return 0; // Or null, or some indicator it's paid
        }

        // Try due_date first, then calculate from created_at and credit_days
        $dueDate = $this->due_date;
        if (!$dueDate && $this->credit_days !== null && $this->created_at) {
             $dueDate = Carbon::parse($this->created_at)->addDays($this->credit_days);
        }

        if (!$dueDate) return null; // Not enough info to calculate

        if ($dueDate->isPast()) {
            return Carbon::now()->diffInDays($dueDate); // Will be positive
        }
        return Carbon::now()->diffInDays($dueDate, false); // Negative if past, positive if future
    }

    // Accessor for overdue status
    public function getIsOverdueAttribute()
    {
        if ($this->is_paid) {
            return false;
        }
        $dueDate = $this->due_date;
        if (!$dueDate && $this->credit_days !== null && $this->created_at) {
             $dueDate = Carbon::parse($this->created_at)->addDays($this->credit_days);
        }
        
        if (!$dueDate) return false;

        return Carbon::now()->greaterThan($dueDate);
    }
}