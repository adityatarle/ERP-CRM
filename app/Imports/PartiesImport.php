<?php

namespace App\Imports;

use App\Models\Party;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PartiesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Party([
            'name' => $row['party_name'],
            'gst_in' => $row['gstin'] ?? null, // Map "GST/IN" to "gst_in"
            'email' => $row['emailemailcc'] ?? null, // Map "Email/EmailCC" to "email"
            'phone_number' => $row['phonemobile_number'] ?? null, // Map "Phone/Mobile Number" to "phone_number"
            'address' => $row['address'] ?? null,
        ]);
    }

    public function headingRow(): int
    {
        return 1; // Ensure the first row is treated as headers
    }
}