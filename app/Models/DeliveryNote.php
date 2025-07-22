<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    use HasFactory;

    protected $fillable = [ 'delivery_note_number',
        'customer_id',
        'ref_no',
        'purchase_number',
        'purchase_date',
        'delivery_date',
        'gst_type',
        'cgst',
        'contact_person',
        'sgst',
        'igst',
        'description',
        'notes',
        'is_invoiced',
        'sale_id'];

   public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
