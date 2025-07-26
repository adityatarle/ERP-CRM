<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
   public function index(Request $request)
{
    // Get the search query and category filter from the request
    $search = $request->query('search');
    $category = $request->query('category');

    // Build the query
    $query = Product::query();

    // If a search term is provided, filter products by name
    if ($search) {
        $query->where('name', 'like', '%' . $search . '%');
    }

    // If a category is selected, filter products by category
    if ($category) {
        $query->where('category', $category);
    }

    // Get the filtered products
    $products = $query->get();

    // Get all unique categories for the dropdown
    $categories = Product::distinct()->pluck('category')->sort();

    // Pass the products, search term, categories, and selected category to the view
    return view('products.index', compact('products', 'search', 'categories', 'category'));
}

    public function create()
    {
        $categories = Product::distinct()->pluck('category')->sort();
        $subcategories = Product::distinct()->pluck('subcategory', 'category')->filter()->sort();
        return view('products.create', compact('categories', 'subcategories'));
    }

    public function store(Request $request)
    {
        // 1. Validation rules updated for the new fields
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'category' => 'required|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'hsn' => 'nullable|string|max:8',
            'gst' => 'nullable|numeric|min:0|max:100',
            // 'is_taxable' is handled separately below
        ]);

        // 2. Handle the checkbox for 'is_taxable'
        // An unchecked checkbox does not send a value, so we check for its presence.
        $validated['is_taxable'] = $request->has('is_taxable');

        // 3. Create the product with the validated data
        $product = Product::create($validated);

        // This part handles AJAX responses (e.g., from a modal)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'hsn' => $product->hsn,
                    'gst' => $product->gst,
                ],
            ], 200);
        }
        
        // This handles standard form submissions
        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }


    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load([
            'saleItems.sale.customer',
            'purchaseEntryItems.purchaseEntry.party'
        ]);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'hsn' => 'nullable|string|max:8',
            'item_code' => 'nullable|string|max:8',
            'stock' => 'nullable|integer|min:0',
            'pstock' => 'required|integer|min:0',
            'qty' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $fileName = $request->file('file')->getClientOriginalName();
            Log::info('Importing file: ' . $fileName);

            $import = new ProductsImport;
            Excel::import($import, $request->file('file'));

            $rowCount = $import->getRowCount();
            if ($rowCount === 0) {
                return redirect()->route('products.index')->with('error', 'No valid rows found in the file.');
            }

            return redirect()->route('products.index')->with('success', "Successfully imported $rowCount products.");
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            Log::error('Import validation error: ' . implode('; ', $errorMessages));
            return redirect()->route('products.index')->with('error', 'Import failed: ' . implode('; ', $errorMessages));
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return redirect()->route('products.index')->with('error', 'Failed to import products: ' . $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $products = Product::where('name', 'like', "%{$query}%")->get(['id', 'name']);
        return response()->json($products);
    }
}