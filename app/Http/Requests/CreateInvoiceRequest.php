<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CreateInvoiceRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'ref_no' => 'nullable|string|max:255',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1|max:9999',
            'products.*.sale_price' => 'required|numeric|min:0.01|max:999999.99',
            'products.*.discount' => 'required|numeric|min:0|max:100',
            'products.*.itemcode' => 'nullable|string|max:255',
            'products.*.secondary_itemcode' => 'nullable|string|max:255',
            'gst_type' => 'required|in:CGST,IGST',
            'cgst' => 'nullable|required_if:gst_type,CGST|numeric|min:0|max:100',
            'sgst' => 'nullable|required_if:gst_type,CGST|numeric|min:0|max:100',
            'igst' => 'nullable|required_if:gst_type,IGST|numeric|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            'purchase_number' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'contact_person' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,completed,cancelled'
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer does not exist.',
            'products.required' => 'At least one product is required.',
            'products.min' => 'At least one product is required.',
            'products.*.product_id.required' => 'Product selection is required.',
            'products.*.product_id.exists' => 'The selected product does not exist.',
            'products.*.quantity.required' => 'Quantity is required.',
            'products.*.quantity.integer' => 'Quantity must be a whole number.',
            'products.*.quantity.min' => 'Quantity must be at least 1.',
            'products.*.quantity.max' => 'Quantity cannot exceed 9999.',
            'products.*.sale_price.required' => 'Sale price is required.',
            'products.*.sale_price.numeric' => 'Sale price must be a valid number.',
            'products.*.sale_price.min' => 'Sale price must be greater than 0.',
            'products.*.sale_price.max' => 'Sale price cannot exceed 999999.99.',
            'products.*.discount.required' => 'Discount is required.',
            'products.*.discount.numeric' => 'Discount must be a valid number.',
            'products.*.discount.min' => 'Discount cannot be negative.',
            'products.*.discount.max' => 'Discount cannot exceed 100%.',
            'gst_type.required' => 'GST type is required.',
            'gst_type.in' => 'GST type must be either CGST or IGST.',
            'cgst.required_if' => 'CGST rate is required when GST type is CGST.',
            'cgst.numeric' => 'CGST rate must be a valid number.',
            'cgst.min' => 'CGST rate cannot be negative.',
            'cgst.max' => 'CGST rate cannot exceed 100%.',
            'sgst.required_if' => 'SGST rate is required when GST type is CGST.',
            'sgst.numeric' => 'SGST rate must be a valid number.',
            'sgst.min' => 'SGST rate cannot be negative.',
            'sgst.max' => 'SGST rate cannot exceed 100%.',
            'igst.required_if' => 'IGST rate is required when GST type is IGST.',
            'igst.numeric' => 'IGST rate must be a valid number.',
            'igst.min' => 'IGST rate cannot be negative.',
            'igst.max' => 'IGST rate cannot exceed 100%.',
            'purchase_number.required' => 'Purchase number is required.',
            'purchase_date.required' => 'Purchase date is required.',
            'purchase_date.date' => 'Purchase date must be a valid date.',
            'status.in' => 'Status must be one of: pending, completed, cancelled.'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate GST rates based on type
            if ($this->gst_type === 'CGST') {
                if (empty($this->cgst) || empty($this->sgst)) {
                    $validator->errors()->add('gst_type', 'Both CGST and SGST rates are required for CGST type.');
                }
                
                if (!empty($this->igst)) {
                    $validator->errors()->add('igst', 'IGST rate should not be provided for CGST type.');
                }
            } else {
                if (empty($this->igst)) {
                    $validator->errors()->add('gst_type', 'IGST rate is required for IGST type.');
                }
                
                if (!empty($this->cgst) || !empty($this->sgst)) {
                    $validator->errors()->add('cgst', 'CGST and SGST rates should not be provided for IGST type.');
                }
            }

            // Validate sale prices
            if ($this->has('products')) {
                foreach ($this->products as $index => $productData) {
                    if (isset($productData['sale_price']) && $productData['sale_price'] <= 0) {
                        $validator->errors()->add(
                            "products.{$index}.sale_price",
                            "Sale price must be greater than 0."
                        );
                    }
                }
            }
        });
    }
}
