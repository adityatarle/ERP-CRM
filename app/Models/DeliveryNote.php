<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

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

    protected $casts = [
        'delivery_date' => 'date',
        'purchase_date' => 'date',
        'is_invoiced' => 'boolean',
        'cgst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'igst' => 'decimal:2',
    ];

    /**
     * Check if delivery note can be converted to invoice
     */
    public function canBeConvertedToInvoice(): bool
    {
        return !$this->is_invoiced;
    }

    /**
     * Validate financial details for invoice conversion
     */
    public function validateFinancialDetails(): bool
    {
        if (empty($this->gst_type)) {
            return false;
        }

        if ($this->gst_type === 'CGST') {
            return !empty($this->cgst) && !empty($this->sgst);
        } elseif ($this->gst_type === 'IGST') {
            return !empty($this->igst);
        }

        return false;
    }

    /**
     * Get validation errors for invoice conversion
     */
    public function getInvoiceConversionErrors(): array
    {
        $errors = [];

        if ($this->is_invoiced) {
            $errors[] = 'This delivery note has already been converted to an invoice.';
        }

        if (empty($this->gst_type)) {
            $errors[] = 'GST type is required for invoice conversion.';
        } elseif ($this->gst_type === 'CGST') {
            if (empty($this->cgst)) {
                $errors[] = 'CGST value is required for CGST type.';
            }
            if (empty($this->sgst)) {
                $errors[] = 'SGST value is required for CGST type.';
            }
        } elseif ($this->gst_type === 'IGST') {
            if (empty($this->igst)) {
                $errors[] = 'IGST value is required for IGST type.';
            }
        }

        if ($this->items->isEmpty()) {
            $errors[] = 'Delivery note must have at least one item to convert to invoice.';
        }

        return $errors;
    }

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
