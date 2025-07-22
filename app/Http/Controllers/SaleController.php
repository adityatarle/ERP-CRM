<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Imports\SalesDataImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['customer', 'saleItems.product'])->get();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        
        // Fetch distinct categories and subcategories from the products table
        $categories = Product::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->filter()
            ->values();
            
        $subcategories = Product::select('subcategory')
            ->distinct()
            ->whereNotNull('subcategory')
            ->pluck('subcategory')
            ->filter()
            ->values();
        
        return view('sales.create', compact('customers', 'products', 'categories', 'subcategories'));
    }

public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'products' => 'required|array|min:1',
            'ref_no' => 'nullable|string|max:255',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1|max:9999',
            'products.*.sale_price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0|max:100',
            'products.*.barcode' => 'nullable|string|max:255',
        ]);

        $totalSalePrice = 0;
        $saleItemsData = [];

        foreach ($validated['products'] as $index => $productData) {
            $product = Product::findOrFail($productData['product_id']);
            if ($product->stock < $productData['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for product {$product->name}.",
                    'errors' => [
                        "products.{$index}.quantity" => "Insufficient stock for product {$product->name}."
                    ]
                ], 422);
            }

            $unitPrice = $productData['sale_price'];
            $discount = $productData['discount'] ?? 0;
            $baseTotalPrice = $unitPrice * $productData['quantity'];
            $itemTotalPrice = $baseTotalPrice * (1 - $discount / 100);

            $saleItemsData[] = [
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'total_price' => $itemTotalPrice,
                'barcode' => $productData['barcode'],
            ];

            $totalSalePrice += $itemTotalPrice;
            $product->decrement('stock', $productData['quantity']);
        }

        $sale = Sale::create([
            'customer_id' => $validated['customer_id'],
            'ref_no' => $validated['ref_no'],
            'total_price' => $totalSalePrice,
        ]);

        foreach ($saleItemsData as $itemData) {
            $sale->saleItems()->create([
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'discount' => $itemData['discount'],
                'total_price' => $itemData['total_price'],
                'barcode' => $itemData['barcode'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sale recorded successfully.'
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Sale creation failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while recording the sale.'
        ], 500);
    }
}

    public function show(Sale $sale)
    {
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $customers = Customer::all();
        $products = Product::all();
        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

   public function update(Request $request, Sale $sale)
{
    DB::beginTransaction();
    try {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'products' => 'required|array|min:1',
            'ref_no' => 'nullable|string|max:255',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.discount' => 'nullable|numeric|min:0|max:100',
            'products.*.quantity' => 'required|integer|min:1|max:9999',
        ]);

        $totalSalePrice = 0;
        $newSaleItemsData = [];
        

        foreach ($validated['products'] as $index => $productData) {
            $product = Product::findOrFail($productData['product_id']);
            $existingItem = $sale->saleItems()->where('product_id', $productData['product_id'])->first();
            $currentQuantity = $existingItem ? $existingItem->quantity : 0;

            $stockNeeded = $productData['quantity'] - $currentQuantity;
            if ($stockNeeded > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for product {$product->name}.",
                    'errors' => [
                        "products.{$index}.quantity" => "Insufficient stock for product {$product->name}."
                    ]
                ], 422);
            }

            $unitPrice = $product->price;
            $itemTotalPrice = $unitPrice * $productData['quantity'];
             $discount = $productData['discount'];

            $newSaleItemsData[] = [
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
                'discount' => $discount,
                'unit_price' => $unitPrice,
                'total_price' => $itemTotalPrice,
            ];

            $totalSalePrice += $itemTotalPrice;
        }

        // Update sale record
        $sale->update([
            'customer_id' => $validated['customer_id'],
            'ref_no' => $validated['ref_no'],
            'total_price' => $totalSalePrice,
        ]);

        // Restore stock for existing sale items
        foreach ($sale->saleItems as $item) {
            $product = Product::findOrFail($item->product_id);
            $product->increment('stock', $item->quantity);
        }

        // Delete existing sale items
        $sale->saleItems()->delete();

        // Create new sale items and deduct stock
        foreach ($newSaleItemsData as $itemData) {
            $sale->saleItems()->create([
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'discount' => $itemData['discount'],
                'unit_price' => $itemData['unit_price'],
                'total_price' => $itemData['total_price'],
            ]);

            $product = Product::findOrFail($itemData['product_id']);
            $product->decrement('stock', $itemData['quantity']);
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => 'Sale updated successfully.'
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Sale update failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while updating the sale.'
        ], 500);
    }
}
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,canceled',
        ]);

        $sale = Sale::findOrFail($id);
        $sale->status = $request->status;
        $sale->save();

        return redirect()->back()->with('success', 'Sale status updated successfully.');
    }

    /**
     * Show the import form
     */
    public function showImportForm()
    {
        return view('sales.import');
    }

    /**
     * Import sales data from Excel
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new SalesDataImport();
            
            Excel::import($import, $request->file('excel_file'));
            
            $results = $import->getImportResults();
            
            $message = "Import completed successfully! ";
            $message .= "Processed: {$results['total_processed']} rows, ";
            $message .= "Sales: {$results['sales_created']}, ";
            $message .= "Invoices: {$results['invoices_created']}, ";
            $message .= "Customers: {$results['customers_created']}, ";
            $message .= "Products: {$results['products_created']}";
            
            if (!empty($results['errors'])) {
                $errorMessage = "Some errors occurred: " . implode('; ', array_slice($results['errors'], 0, 3));
                if (count($results['errors']) > 3) {
                    $errorMessage .= " and " . (count($results['errors']) - 3) . " more errors.";
                }
                
                return redirect()->route('sales.import.form')
                    ->with('warning', $message)
                    ->with('errors', $errorMessage);
            }
            
            return redirect()->route('sales.index')->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Sales import failed', [
                'error' => $e->getMessage(),
                'file' => $request->file('excel_file')->getClientOriginalName()
            ]);
            
            return redirect()->route('sales.import.form')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download sample Excel template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Date',
            'Particulars', 
            'Buyer',
            'Voucher Type',
            'Voucher No.',
            'Voucher Ref. No.',
            'GSTIN/UIN',
            'Sales Tax No.',
            'Order No. & Date',
            'Terms of Payment',
            'Other References',
            'Terms of Delivery',
            'Quantity',
            'Rate',
            'Value',
            'Gross Total',
            'Sales-GST',
            'CGST@9%',
            'SGST@9%',
            'Round Off',
            'CGST@6%',
            'SGST@6%'
        ];

        $sampleData = [
            [
                '02-Jun-25',
                'SAMPLE COMPANY PVT LTD',
                'SAMPLE COMPANY PVT LTD',
                'Sales',
                '2025/26/001',
                '',
                '27AABCP7335H1ZC',
                '',
                '101136 dt.28-Apr-25',
                '45 Days',
                '',
                '',
                '1 NOS',
                '',
                '5000.00',
                '5900.00',
                '5000.00',
                '450.00',
                '450.00',
                '',
                '',
                ''
            ],
            [
                '',
                'SAMPLE-PRODUCT-001 Sample Product Description',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '1 NOS',
                '5000.00/NOS',
                '5000.00',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]
        ];

        $filename = 'sales_import_template_' . date('Y_m_d') . '.csv';
        
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);
        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}