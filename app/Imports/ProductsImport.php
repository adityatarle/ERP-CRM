<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToModel, WithHeadingRow
{
    protected $rowCount = 0;

    public function model(array $row)
    {
        // Log the raw row data for debugging
        Log::debug('Processing row: ', $row);

        // Normalize the row keys to lowercase for consistency
        $row = array_change_key_case($row, CASE_LOWER);

        $name = trim($row['particulars'] ?? '');
        // $category = trim($row['category'] ?? ''); // Already commented out

        // Skip rows that are blank, "Grand Total", or missing required fields
        if (
            empty($name) ||
            strtolower($name) === 'grand total'
        ) {
            Log::warning('Skipping row due to invalid or blank data: ', $row);
            return null;
        }

        // Handle price (purchase_rate): default to 0 if empty or invalid
        $price = trim($row['purchase_rate'] ?? '');
        $price = is_numeric($price) && $price >= 0 ? (float) $price : 0;

        // Handle tally_stock: default to 0 if empty or invalid
        $tallyStock = trim($row['tally_stock'] ?? '');
        $tallyStock = is_numeric($tallyStock) && $tallyStock >= 0 ? (int) $tallyStock : 0;

        // Handle physical_stock: default to 0 if empty or invalid
        $physicalStock = trim($row['physical_stock'] ?? '');
        $physicalStock = is_numeric($physicalStock) && $physicalStock >= 0 ? (int) $physicalStock : 0;

        // Handle challan_qty: default to 0 if empty or invalid
        $challanQty = trim($row['challan_qty'] ?? '');
        $challanQty = is_numeric($challanQty) && $challanQty >= 0 ? (int) $challanQty : 0;

        // Map GST and HSN/SAC (if these fields exist in the Product model)
        $gst = trim($row['gst'] ?? '');
        $hsn = trim($row['hsn_sac'] ?? '');

        // Log if any field was invalid and defaulted to 0
        if (!is_numeric($row['tally_stock'] ?? '') || ($row['tally_stock'] ?? '') < 0) {
            Log::info('Invalid tally_stock value, defaulting to 0: ', ['row' => $row]);
        }
        if (!is_numeric($row['physical_stock'] ?? '') || ($row['physical_stock'] ?? '') < 0) {
            Log::info('Invalid physical_stock value, defaulting to 0: ', ['row' => $row]);
        }
        if (!is_numeric($row['challan_qty'] ?? '') || ($row['challan_qty'] ?? '') < 0) {
            Log::info('Invalid challan_qty value, defaulting to 0: ', ['row' => $row]);
        }
        if (!is_numeric($row['purchase_rate'] ?? '') || ($row['purchase_rate'] ?? '') < 0) {
            Log::info('Invalid purchase_rate value, defaulting to 0: ', ['row' => $row]);
        }

        // Increment row count for valid rows
        $this->rowCount++;

        return new Product([
            'name'       => $name,
            // 'category'   => $category, // Already commented out
            'price'      => $price,
            'stock'      => $tallyStock,
            'pstock'     => $physicalStock,
            'qty'        => $challanQty,
            'gst'        => $gst,
            'hsn'        => $hsn,
        ]);
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}