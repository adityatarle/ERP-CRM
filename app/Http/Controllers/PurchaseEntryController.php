<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\PurchaseEntry;
use App\Models\PurchaseEntryItem;
use App\Models\Product;
use App\Models\Payable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PurchaseEntryController extends Controller
{
    public function index()
    {
        $purchaseEntries = PurchaseEntry::with('party')->get();
        return view('purchase_entries.index', compact('purchaseEntries'));
    }

    public function create()
    {
        $parties = Party::all();
        $products = Product::all();
        return view('purchase_entries.create', compact('parties', 'products'));
    }

    public function store(Request $request)
    {
        Log::info('Starting store method', $request->all());

        $request->validate([
            'purchase_date' => 'required|date',
            'invoice_number' => 'required|string',
            'invoice_date' => 'required|date',
            'party_id' => 'required|exists:parties,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.gst_rate' => 'nullable|numeric|min:0|max:100',
            'products.*.gst_type' => 'nullable|in:CGST,SGST,IGST',
        ]);

        try {
            \DB::transaction(function () use ($request) {
                Log::info('Starting transaction');

                $purchaseEntry = PurchaseEntry::create([
                    'purchase_number' => 'PE-' . Str::random(8),
                    'purchase_date' => $request->purchase_date,
                    'invoice_number' => $request->invoice_number,
                    'invoice_date' => $request->invoice_date,
                    'party_id' => $request->party_id,
                    'note' => $request->note,
                ]);
                Log::info('Purchase entry created', ['id' => $purchaseEntry->id]);

                $totalAmount = 0;
                foreach ($request->products as $product) {
                    $baseTotal = $product['quantity'] * $product['unit_price'];
                    $gstRate = $product['gst_rate'] ?? 0;
                    $gstType = $product['gst_type'] ?? null;

                    $totalGstRate = 0;
                    if ($gstType === 'IGST') {
                        $totalGstRate = $gstRate;
                    } elseif ($gstType === 'CGST' || $gstType === 'SGST') {
                        $totalGstRate = $gstRate * 2;
                    }

                    $gstAmount = $baseTotal * ($totalGstRate / 100);
                    $totalWithGST = $baseTotal + $gstAmount;

                    PurchaseEntryItem::create([
                        'purchase_entry_id' => $purchaseEntry->id,
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity'],
                        'unit_price' => $product['unit_price'],
                        'total_price' => $totalWithGST,
                        'gst_rate' => $gstRate,
                        'gst_type' => $gstType,
                    ]);
                    Log::info('Purchase entry item created', ['purchase_entry_id' => $purchaseEntry->id, 'product_id' => $product['product_id']]);

                    $totalAmount += $totalWithGST;

                    if ($request->note && stripos($request->note, 'received') !== false) {
                        $productModel = Product::find($product['product_id']);
                        if (!$productModel) {
                            Log::error("Product not found for ID: {$product['product_id']}");
                            throw new \Exception("Product not found for ID: {$product['product_id']}");
                        }
                        Log::info("Calling updateStock for product ID: {$product['product_id']}, Quantity: {$product['quantity']}");
                        $productModel->updateStock($product['quantity']);
                    }
                }

                // Create payable entry
                Payable::create([
                    'purchase_entry_id' => $purchaseEntry->id,
                    'party_id' => $request->party_id,
                    'amount' => $totalAmount,
                    'is_paid' => false,
                ]);
                Log::info('Payable created', ['purchase_entry_id' => $purchaseEntry->id, 'amount' => $totalAmount]);
            });

            return redirect()->route('purchase_entries.index')->with('success', 'Purchase entry created. Add note to update stock if received.');
        } catch (\Exception $e) {
            Log::error('Transaction failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while creating the purchase entry. Check logs for details.']);
        }
    }

    public function edit($id)
    {
        $purchaseEntry = PurchaseEntry::with('items')->findOrFail($id);
        $parties = Party::all();
        $products = Product::all();
        return view('purchase_entries.edit', compact('purchaseEntry', 'parties', 'products'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'invoice_number' => 'required|string',
            'invoice_date' => 'required|date',
            'party_id' => 'required|exists:parties,id',
            'note' => 'nullable|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.gst_rate' => 'nullable|numeric|min:0|max:100',
            'products.*.gst_type' => 'nullable|in:CGST,SGST,IGST',
        ]);

        try {
            \DB::transaction(function () use ($request, $id) {
                $purchaseEntry = PurchaseEntry::findOrFail($id);
                $purchaseEntry->update([
                    'purchase_date' => $request->purchase_date,
                    'invoice_number' => $request->invoice_number,
                    'invoice_date' => $request->invoice_date,
                    'party_id' => $request->party_id,
                    'note' => $request->note,
                ]);

                PurchaseEntryItem::where('purchase_entry_id', $purchaseEntry->id)->delete();
                $totalAmount = 0;
                foreach ($request->products as $product) {
                    $baseTotal = $product['quantity'] * $product['unit_price'];
                    $gstRate = $product['gst_rate'] ?? 0;
                    $gstType = $product['gst_type'] ?? null;

                    $totalGstRate = 0;
                    if ($gstType === 'IGST') {
                        $totalGstRate = $gstRate;
                    } elseif ($gstType === 'CGST' || $gstType === 'SGST') {
                        $totalGstRate = $gstRate * 2;
                    }

                    $gstAmount = $baseTotal * ($totalGstRate / 100);
                    $totalWithGST = $baseTotal + $gstAmount;

                    PurchaseEntryItem::create([
                        'purchase_entry_id' => $purchaseEntry->id,
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity'],
                        'unit_price' => $product['unit_price'],
                        'total_price' => $totalWithGST,
                        'gst_rate' => $gstRate,
                        'gst_type' => $gstType,
                    ]);

                    $totalAmount += $totalWithGST;

                    if ($request->note && stripos($request->note, 'received') !== false) {
                        $productModel = Product::find($product['product_id']);
                        if (!$productModel) {
                            Log::error("Product not found for ID: {$product['product_id']}");
                            throw new \Exception("Product not found for ID: {$product['product_id']}");
                        }
                        Log::info("Calling updateStock for product ID: {$product['product_id']}, Quantity: {$product['quantity']}");
                        $productModel->updateStock($product['quantity']);
                    }
                }

                Payable::updateOrCreate(
                    ['purchase_entry_id' => $purchaseEntry->id],
                    ['party_id' => $request->party_id, 'amount' => $totalAmount, 'is_paid' => false]
                );
            });

            return redirect()->route('purchase_entries.index')->with('success', 'Purchase entry updated and stock updated if noted as received.');
        } catch (\Exception $e) {
            Log::error('Transaction failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the purchase entry. Check logs for details.']);
        }
    }
}