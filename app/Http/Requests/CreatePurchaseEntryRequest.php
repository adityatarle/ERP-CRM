<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CreatePurchaseEntryRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'party_id' => 'required|exists:parties,id',
            'invoice_number' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1|max:9999',
            'products.*.purchase_price' => 'required|numeric|min:0.01|max:999999.99',
            'products.*.discount' => 'nullable|numeric|min:0|max:100',
            'cash_discount' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:pending,completed,cancelled'
        ];
    }

    public function messages(): array
    {
        return [
            'party_id.required' => 'Please select a party.',
            'party_id.exists' => 'The selected party does not exist.',
            'invoice_number.required' => 'Invoice number is required.',
            'invoice_number.string' => 'Invoice number must be a valid string.',
            'invoice_number.max' => 'Invoice number cannot exceed 255 characters.',
            'purchase_date.required' => 'Purchase date is required.',
            'purchase_date.date' => 'Purchase date must be a valid date.',
            'products.required' => 'At least one product is required.',
            'products.min' => 'At least one product is required.',
            'products.*.product_id.required' => 'Product selection is required.',
            'products.*.product_id.exists' => 'The selected product does not exist.',
            'products.*.quantity.required' => 'Quantity is required.',
            'products.*.quantity.integer' => 'Quantity must be a whole number.',
            'products.*.quantity.min' => 'Quantity must be at least 1.',
            'products.*.quantity.max' => 'Quantity cannot exceed 9999.',
            'products.*.purchase_price.required' => 'Purchase price is required.',
            'products.*.purchase_price.numeric' => 'Purchase price must be a valid number.',
            'products.*.purchase_price.min' => 'Purchase price must be greater than 0.',
            'products.*.purchase_price.max' => 'Purchase price cannot exceed 999999.99.',
            'products.*.discount.numeric' => 'Discount must be a valid number.',
            'products.*.discount.min' => 'Discount cannot be negative.',
            'products.*.discount.max' => 'Discount cannot exceed 100%.',
            'cash_discount.numeric' => 'Cash discount must be a valid number.',
            'cash_discount.min' => 'Cash discount cannot be negative.',
            'cash_discount.max' => 'Cash discount cannot exceed 100%.',
            'status.in' => 'Status must be one of: pending, completed, cancelled.'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate purchase prices
            if ($this->has('products')) {
                foreach ($this->products as $index => $productData) {
                    if (isset($productData['purchase_price']) && $productData['purchase_price'] <= 0) {
                        $validator->errors()->add(
                            "products.{$index}.purchase_price",
                            "Purchase price must be greater than 0."
                        );
                    }
                }
            }

            // Validate date is not in the future
            if ($this->purchase_date && strtotime($this->purchase_date) > time()) {
                $validator->errors()->add('purchase_date', 'Purchase date cannot be in the future.');
            }
        });
    }
}
