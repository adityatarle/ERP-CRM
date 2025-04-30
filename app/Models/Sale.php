<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['customer_id', 'product_id', 'quantity', 'total_price','status'];

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
    
}