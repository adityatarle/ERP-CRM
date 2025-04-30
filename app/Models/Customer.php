<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address','gst_number'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function invoices()
{
    return $this->hasMany(Invoice::class);
}
}