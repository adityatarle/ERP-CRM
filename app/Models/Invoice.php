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
        'subtotal',
        'tax',
        'gst',
        'total',
        'status',
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
}