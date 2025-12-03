<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CreateProductRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($this->route('product'))
            ],
            'category' => 'required|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock' => 'required|integer|min:0|max:999999',
            'is_taxable' => 'nullable|boolean',
            'hsn' => [
                'nullable',
                'string',
                'size:8',
                'regex:/^[0-9]+$/'
            ],
            'gst' => 'nullable|numeric|min:0|max:100',
            'item_code' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.unique' => 'A product with this name already exists.',
            'hsn.regex' => 'HSN code must contain only numbers.',
            'hsn.size' => 'HSN code must be exactly 8 digits.',
            'price.max' => 'Price cannot exceed 999,999.99.',
            'stock.max' => 'Stock quantity cannot exceed 999,999.',
        ]);
    }
}
