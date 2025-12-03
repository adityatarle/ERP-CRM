<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\ImportFileRequest;
use App\Services\FileUploadService;
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

    public function store(CreateProductRequest $request)
    {
        $validated = $request->validated();
        
        // Handle the checkbox for 'is_taxable'
        $validated['is_taxable'] = $request->has('is_taxable') ? 1 : 0;

        // Create the product with the validated data
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
                    'price' => $product->price,
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

    // In app/Http/Controllers/ProductController.php

    public function update(Request $request, Product $product)
    {
        // THE FIX: Changed 'pstock' and 'qty' from 'required' to 'nullable'.
        // Also ensuring the 'name' validation allows the current product's name.
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'category' => 'required|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'hsn' => 'nullable|string|max:8',
            'item_code' => 'nullable|string|max:255', // Corrected max length
            'stock' => 'nullable|integer|min:0',
            'pstock' => 'nullable|integer|min:0',    // <-- CHANGED FROM 'required'
            'qty' => 'nullable|integer|min:0',       // <-- CHANGED FROM 'required'
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

    public function import(ImportFileRequest $request, FileUploadService $fileUploadService)
    {
        try {
            $file = $request->file('file');
            
            // Upload file securely
            $uploadResult = $fileUploadService->upload($file, 'import', 'imports');
            
            Log::info('Importing file: ' . $uploadResult['original_name'], [
                'filename' => $uploadResult['filename'],
                'size' => $uploadResult['size']
            ]);

            $import = new ProductsImport;
            Excel::import($import, $file);

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
