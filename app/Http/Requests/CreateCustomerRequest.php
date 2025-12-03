<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CreateCustomerRequest extends BaseFormRequest
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
                Rule::unique('customers', 'name')->ignore($this->route('customer'))
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($this->route('customer'))
            ],
            'phone' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'gst_number' => [
                'nullable',
                'string',
                'size:15',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                Rule::unique('customers', 'gst_number')->ignore($this->route('customer'))
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.unique' => 'A customer with this name already exists.',
            'email.unique' => 'A customer with this email already exists.',
            'gst_number.unique' => 'A customer with this GST number already exists.',
            'gst_number.regex' => 'GST number format is invalid.',
            'gst_number.size' => 'GST number must be exactly 15 characters.',
            'phone.regex' => 'Phone number format is invalid.',
        ]);
    }
}
