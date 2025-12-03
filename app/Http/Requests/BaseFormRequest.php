<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'email' => 'The :attribute must be a valid email address.',
            'unique' => 'The :attribute has already been taken.',
            'numeric' => 'The :attribute must be a number.',
            'integer' => 'The :attribute must be an integer.',
            'min' => 'The :attribute must be at least :min.',
            'max' => 'The :attribute may not be greater than :max.',
            'regex' => 'The :attribute format is invalid.',
            'exists' => 'The selected :attribute is invalid.',
            'in' => 'The selected :attribute is invalid.',
            'date' => 'The :attribute is not a valid date.',
            'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
            'mimes' => 'The :attribute must be a file of type: :values.',
            'max_file_size' => 'The :attribute may not be greater than :max kilobytes.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'name',
            'email' => 'email address',
            'password' => 'password',
            'phone' => 'phone number',
            'gst_number' => 'GST number',
            'price' => 'price',
            'stock' => 'stock quantity',
            'quantity' => 'quantity',
            'category' => 'category',
            'subcategory' => 'subcategory',
        ];
    }
}
