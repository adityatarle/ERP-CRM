<?php

namespace App\Imports;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SalesDataImport implements FromCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    private $importResults = [];
    private $errors = [];
    private $currentRow = 1; // Start at 1 because of header
    
    public function collection(Collection $rows)
    {
        $this->importResults = [
            'total_processed' => 0,
            'sales_created' => 0,
            'invoices_created' => 0,
            'customers_created' => 0,
            'products_created' => 0,
            'errors' => []
        ];

        Log::info('Starting sales data import', ['total_rows' => $rows->count()]);

        foreach ($rows as $row) {
            $this->currentRow++;
            
            try {
                // Skip empty rows
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                // Process the row
                $this->processRow($row);
                $this->importResults['total_processed']++;

            } catch (\Exception $e) {
                $error = "Row {$this->currentRow}: " . $e->getMessage();
                $this->importResults['errors'][] = $error;
                Log::error('Sales import error', ['row' => $this->currentRow, 'error' => $e->getMessage(), 'data' => $row->toArray()]);
            }
        }

        Log::info('Sales data import completed', $this->importResults);
        return collect();
    }

    private function isEmptyRow($row)
    {
        // Check if the main identifying fields are empty
        return empty($row['date']) && empty($row['particulars']) && empty($row['buyer']);
    }

    private function processRow($row)
    {
        // Parse date
        $date = $this->parseDate($row['date'] ?? '');
        if (!$date) {
            throw new \Exception('Invalid or missing date');
        }

        // Get or create customer
        $customer = $this->getOrCreateCustomer($row);
        
        // Create the main sale record
        $sale = $this->createSale($row, $customer, $date);
        
        // Process sale items (products)
        $this->processSaleItems($row, $sale);
        
        // Create invoice if we have invoice-related data
        if ($this->shouldCreateInvoice($row)) {
            $this->createInvoice($row, $sale, $customer, $date);
        }
    }

    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Try different date formats
            $formats = ['d-M-y', 'd-m-Y', 'Y-m-d', 'd/m/Y', 'm/d/Y'];
            
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $dateString);
                if ($date) {
                    return Carbon::parse($date);
                }
            }

            // If none of the formats work, try Carbon's flexible parsing
            return Carbon::parse($dateString);
            
        } catch (\Exception $e) {
            Log::warning('Date parsing failed', ['date' => $dateString, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function getOrCreateCustomer($row)
    {
        $buyerName = trim($row['buyer'] ?? $row['particulars'] ?? '');
        $gstNumber = trim($row['gstin_uin'] ?? '');

        if (empty($buyerName)) {
            throw new \Exception('Buyer/Customer name is required');
        }

        // Try to find existing customer by name or GST number
        $customer = null;
        
        if (!empty($gstNumber)) {
            $customer = Customer::where('gst_number', $gstNumber)->first();
        }
        
        if (!$customer) {
            $customer = Customer::where('name', 'LIKE', '%' . $buyerName . '%')->first();
        }

        // Create new customer if not found
        if (!$customer) {
            $customer = Customer::create([
                'name' => $buyerName,
                'gst_number' => $gstNumber,
                'email' => null, // Will be filled later if needed
                'phone' => null, // Will be filled later if needed
                'address' => null, // Will be filled later if needed
                'city' => null,
                'pan_number' => null
            ]);
            
            $this->importResults['customers_created']++;
            Log::info('Customer created', ['name' => $buyerName, 'gst' => $gstNumber]);
        }

        return $customer;
    }

    private function createSale($row, $customer, $date)
    {
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'product_id' => null, // Will be handled via sale items
            'quantity' => $this->parseNumber($row['quantity'] ?? 0),
            'total_price' => $this->parseNumber($row['value'] ?? $row['gross_total'] ?? 0),
            'discount' => 0, // Can be calculated if needed
            'status' => 'completed',
            'ref_no' => $row['voucher_no'] ?? null,
            'created_at' => $date,
            'updated_at' => $date
        ]);

        $this->importResults['sales_created']++;
        return $sale;
    }

    private function processSaleItems($row, $sale)
    {
        // Check for product information in the row
        $productName = $this->extractProductName($row);
        
        if (!empty($productName)) {
            $product = $this->getOrCreateProduct($productName, $row);
            
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $this->parseNumber($row['quantity'] ?? 1),
                'unit_price' => $this->parseNumber($row['rate'] ?? 0),
                'total_price' => $this->parseNumber($row['value'] ?? 0),
                'itemcode' => $productName,
                'secondary_itemcode' => null,
                'discount' => 0
            ]);
        }
    }

    private function extractProductName($row)
    {
        // The product names seem to be in the 'particulars' field for detail rows
        $particulars = trim($row['particulars'] ?? '');
        
        // If particulars contains product codes (like D2A479-SC NTC DR), it's a product line
        if (preg_match('/^[A-Z0-9\-]+/', $particulars)) {
            return $particulars;
        }
        
        return null;
    }

    private function getOrCreateProduct($productName, $row)
    {
        // Try to find existing product
        $product = Product::where('name', 'LIKE', '%' . $productName . '%')
                         ->orWhere('item_code', $productName)
                         ->first();

        if (!$product) {
            $product = Product::create([
                'name' => $productName,
                'item_code' => $productName,
                'price' => $this->parseNumber($row['rate'] ?? 0),
                'stock' => 0, // Will need to be updated separately
                'category' => 'Imported',
                'subcategory' => null,
                'hsn' => null,
                'description' => 'Imported from Excel'
            ]);
            
            $this->importResults['products_created']++;
            Log::info('Product created', ['name' => $productName]);
        }

        return $product;
    }

    private function shouldCreateInvoice($row)
    {
        // Create invoice for main transaction rows (not detail product lines)
        return !empty($row['voucher_no']) && !empty($row['gross_total']);
    }

    private function createInvoice($row, $sale, $customer, $date)
    {
        $grossTotal = $this->parseNumber($row['gross_total'] ?? 0);
        $cgst = $this->parseNumber($row['cgst_9'] ?? $row['cgst_6'] ?? 0);
        $sgst = $this->parseNumber($row['sgst_9'] ?? $row['sgst_6'] ?? 0);
        $igst = 0; // Add if IGST column exists
        $salesGst = $this->parseNumber($row['sales_gst'] ?? 0);
        $roundOff = $this->parseNumber($row['round_off'] ?? 0);
        
        $subtotal = $grossTotal - $cgst - $sgst - $igst;

        Invoice::create([
            'invoice_number' => $row['voucher_no'] ?? 'IMP-' . time(),
            'customer_id' => $customer->id,
            'sale_id' => $sale->id,
            'issue_date' => $date,
            'due_date' => $this->calculateDueDate($date, $row['terms_of_payment'] ?? ''),
            'purchase_number' => $row['order_no_date'] ?? null,
            'purchase_date' => $date,
            'subtotal' => $subtotal,
            'tax' => $cgst + $sgst + $igst,
            'gst_type' => $this->determineGstType($cgst, $sgst, $igst),
            'gst' => $cgst + $sgst + $igst,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $grossTotal,
            'description' => $row['particulars'] ?? 'Imported Invoice',
            'status' => 'paid',
            'created_at' => $date,
            'updated_at' => $date
        ]);

        $this->importResults['invoices_created']++;
    }

    private function calculateDueDate($issueDate, $terms)
    {
        if (preg_match('/(\d+)\s*days?/i', $terms, $matches)) {
            $days = (int)$matches[1];
            return $issueDate->addDays($days);
        }
        
        return $issueDate->addDays(30); // Default 30 days
    }

    private function determineGstType($cgst, $sgst, $igst)
    {
        if ($igst > 0) {
            return 'IGST';
        } elseif ($cgst > 0 || $sgst > 0) {
            return 'CGST_SGST';
        }
        return 'None';
    }

    private function parseNumber($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Remove any non-numeric characters except decimal point and minus
        $cleaned = preg_replace('/[^\d.\-]/', '', $value);
        return floatval($cleaned);
    }

    public function rules(): array
    {
        return [
            'date' => 'required',
            'buyer' => 'required|string|max:255',
        ];
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getImportResults()
    {
        return $this->importResults;
    }
}