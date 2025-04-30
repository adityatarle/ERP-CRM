<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\Rule;

class CustomersImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Validate email uniqueness
        $existingCustomer = Customer::where('email', $row['email'])->first();
        if ($existingCustomer) {
            return null; // Skip if email already exists
        }

        return new Customer([
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
        ]);
    }
}