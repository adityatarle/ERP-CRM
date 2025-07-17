<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['customer_id', 'product_id', 'quantity', 'total_price','discount', 'status','ref_no'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
    public function getTotalPriceAttribute()
    {
        return $this->saleItems->sum('total_price');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_sale', 'sale_id', 'invoice_id')
                    ->withTimestamps();
    }
    public function receivable()
    {
        return $this->hasOne(Receivable::class);
    }

    public function deliveryNote()
    {
        return $this->hasOne(DeliveryNote::class);
    }

}