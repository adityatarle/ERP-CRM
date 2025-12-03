<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CreateSaleRequest extends BaseFormRequest
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
            'products.*.discount' => 'nullable|numeric|min:0|max:100',
            'products.*.barcode' => 'nullable|string|max:255',
            'products.*.itemcode' => 'nullable|string|max:255',
            'products.*.secondary_itemcode' => 'nullable|string|max:255',
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
            'products.*.discount.numeric' => 'Discount must be a valid number.',
            'products.*.discount.min' => 'Discount cannot be negative.',
            'products.*.discount.max' => 'Discount cannot exceed 100%.',
            'status.in' => 'Status must be one of: pending, completed, cancelled.'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check stock availability for each product
            if ($this->has('products')) {
                foreach ($this->products as $index => $productData) {
                    if (isset($productData['product_id']) && isset($productData['quantity'])) {
                        $product = \App\Models\Product::find($productData['product_id']);
                        
                        if ($product && $product->stock < $productData['quantity']) {
                            $validator->errors()->add(
                                "products.{$index}.quantity",
                                "Insufficient stock for {$product->name}. Available: {$product->stock}"
                            );
                        }
                    }
                }
            }
        });
    }
}
