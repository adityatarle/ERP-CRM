<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

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
        return view('sales.create', compact('customers', 'products'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'products' => 'required|array|min:1',
        'products.*.product_id' => 'required|exists:products,id',
        'products.*.quantity' => 'required|integer|min:1|max:9999',
    ]);

    $totalSalePrice = 0;
    $saleItemsData = [];

    foreach ($validated['products'] as $productData) {
        $product = Product::findOrFail($productData['product_id']);
        if ($product->stock < $productData['quantity']) {
            return back()->withErrors(['quantity' => "Insufficient stock for product {$product->name}."]);
        }

        $unitPrice = $product->price;
        $itemTotalPrice = $unitPrice * $productData['quantity'];

        $saleItemsData[] = [
            'product_id' => $product->id,
            'quantity' => $productData['quantity'],
            'unit_price' => $unitPrice,
            'total_price' => $itemTotalPrice,
        ];

        $totalSalePrice += $itemTotalPrice;
        $product->decrement('stock', $productData['quantity']);
    }

    $sale = Sale::create([
        'customer_id' => $validated['customer_id'],
        'total_price' => $totalSalePrice,
    ]);

    foreach ($saleItemsData as $itemData) {
        $sale->saleItems()->create([
            'product_id' => $itemData['product_id'],
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'],
            'total_price' => $itemData['total_price'],
        ]);
    }

    return redirect()->route('sales.index')->with('success', 'Sale recorded successfully.');
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
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);
        $stock_difference = $request->quantity - $sale->quantity;

        if ($product->stock < $stock_difference) {
            return back()->withErrors(['quantity' => 'Insufficient stock.']);
        }

        $total_price = $product->price * $request->quantity;

        $sale->update([
            'customer_id' => $request->customer_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'total_price' => $total_price,
        ]);

        $product->stock -= $stock_difference;
        $product->save();

        return redirect()->route('sales.index')->with('success', 'Sale updated successfully.');
    }

    // public function destroy(Sale $sale)
    // {
    //     $product = $sale->product;
    //     $product->stock += $sale->quantity;
    //     $product->save();

    //     $sale->delete();
    //     return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
    // }


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
}