<?php

namespace App\Http\Requests;

class ImportFileRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240', // 10MB in KB
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'file.required' => 'Please select a file to import.',
            'file.mimes' => 'The file must be an Excel (.xlsx, .xls) or CSV file.',
            'file.max' => 'The file size cannot exceed 10MB.',
        ]);
    }
}
