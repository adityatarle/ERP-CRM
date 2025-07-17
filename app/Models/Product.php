<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'category', 'subcategory', 'price', 'stock', 'discount', 'qty','gst', 'pstock', 'hsn','item_code'];

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseEntryItems()
    {
        return $this->hasMany(PurchaseEntryItem::class);
    }

    public function updateStock($quantity)
    {
        try {
            $quantity = (int) $quantity;
            $oldStock = $this->stock ?? 0;
            $this->stock = $oldStock + $quantity;
            \Log::info("Updating stock for product ID: {$this->id}, Old Stock: {$oldStock}, New Stock: {$this->stock}");
            $this->save();
            \Log::info("Stock updated successfully for product ID: {$this->id}");
        } catch (\Exception $e) {
            \Log::error("Failed to update stock for product ID: {$this->id}, Error: {$e->getMessage()}");
            throw $e;
        }
    }
}
