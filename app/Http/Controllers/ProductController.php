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
        // Get the search query from the request
        $search = $request->query('search');

        // Build the query
        $query = Product::query();

        // If a search term is provided, filter products by name
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Get the filtered products
        $products = $query->get();

        // Pass the products and search term to the view
        return view('products.index', compact('products', 'search'));
    }

    public function create()
    {
        $categories = Product::distinct()->pluck('category')->sort();
        $subcategories = Product::distinct()->pluck('subcategory', 'category')->filter()->sort();
        return view('products.create', compact('categories', 'subcategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'stock' => 'nullable|integer|min:0',
            'pstock' => 'required|integer|min:0',
        'qty' => 'required|integer|min:0',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
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