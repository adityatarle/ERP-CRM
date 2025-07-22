<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\PurchaseEntry;
use App\Models\PurchaseEntryItem;
use App\Models\Product;
use App\Models\Payable;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PurchaseEntryController extends Controller
{
    public function index(Request $request)
    {
        // 1. Get all search inputs from the request
        $invoiceNumber = $request->input('invoice_number');
        $partyName = $request->input('party_name');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // 2. Start with the base query and eager-load relationships for efficiency
        $query = PurchaseEntry::with(['party', 'items']);

        // 3. Apply filters if they exist
        if ($invoiceNumber) {
            $query->where('invoice_number', 'like', '%' . $invoiceNumber . '%');
        }
        if ($partyName) {
            $query->whereHas('party', function ($q) use ($partyName) {
                $q->where('name', 'like', '%' . $partyName . '%');
            });
        }
        if ($startDate) {
            $query->whereDate('purchase_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('purchase_date', '<=', $endDate);
        }

        // --- THE FIX IS HERE ---
        // 4. Calculate the total amount from the *unpaginated* filtered query
        // We clone the query to avoid affecting the pagination query
        $filteredEntriesForTotal = $query->clone()->get();
        $filteredTotal = $filteredEntriesForTotal->flatMap->items->sum('total_price');
        // --- END OF FIX ---

        // 5. Now, apply ordering and pagination to the original query for display
        $purchaseEntries = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // 6. Pass all data, including the new total, to the view
        return view('purchase_entries.index', compact(
            'purchaseEntries',
            'filteredTotal', // Pass the new total
            'invoiceNumber',
            'partyName',
            'startDate',
            'endDate'
        ));
    }

    public function create()
    {
        // Fetch parties that have at least one purchase order
        $parties = Party::whereHas('purchaseOrders')->orderBy('name')->get();

        // Fetch only purchase orders with remaining items
        $purchaseOrders = $this->getPurchaseOrdersWithRemainingItemsForView();

        // Pass all products for the dynamic "Add Product" functionality
        $products = Product::orderBy('name')->get();

        return view('purchase_entries.create', compact('parties', 'products', 'purchaseOrders'));
    }

    /**
     * Get purchase orders with remaining items for view rendering
     */
    private function getPurchaseOrdersWithRemainingItemsForView()
    {
        $purchaseOrders = PurchaseOrder::with(['party', 'items.product', 'receiptNoteItems', 'purchaseEntryItems'])
            ->orderBy('purchase_order_number', 'desc')
            ->get();

        // Filter purchase orders that have remaining items
        return $purchaseOrders->filter(function($po) {
            // Skip if no items
            if ($po->items->isEmpty()) {
                return false;
            }

            // Calculate received quantities from both sources
            $receivedViaNote = $po->receiptNoteItems
                ->where('status', 'received')
                ->groupBy('product_id')
                ->map(fn($items) => $items->sum('quantity'));
            
            $receivedViaEntry = $po->purchaseEntryItems
                ->where('status', 'received')
                ->groupBy('product_id')
                ->map(fn($items) => $items->sum('quantity'));

            // Check if any item has remaining quantity
            foreach ($po->items as $item) {
                $fromNote = $receivedViaNote->get($item->product_id, 0);
                $fromEntry = $receivedViaEntry->get($item->product_id, 0);
                $totalReceived = $fromNote + $fromEntry;
                $remaining = $item->quantity - $totalReceived;

                if ($remaining > 0) {
                    return true; // This PO has at least one item with remaining quantity
                }
            }

            return false; // All items are fully received
        });
    }



    public function store(Request $request)
    {
        Log::info('Starting purchase entry store method', $request->all());

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'party_id' => 'required|exists:parties,id',
            'invoice_number' => 'required|string|max:255|unique:purchase_entries,invoice_number',
            'invoice_date' => 'required|date',
            'purchase_date' => 'required|date',
            'note' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:0',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0|max:100',
            'products.*.cgst_rate' => 'nullable|numeric|min:0|max:100',
            'products.*.sgst_rate' => 'nullable|numeric|min:0|max:100',
            'products.*.igst_rate' => 'nullable|numeric|min:0|max:100',
            'products.*.status' => 'required|in:pending,received',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $receivedProducts = array_filter($validated['products'], fn($product) => $product['quantity'] > 0);
                if (empty($receivedProducts)) {
                    return redirect()->back()->with('error', 'No products with valid quantities.');
                }

                $totalAmount = 0;
                $totalDiscount = 0;
                $totalGstAmount = 0;

                foreach ($receivedProducts as $productData) {
                    $quantity = (float)$productData['quantity'];
                    $unitPrice = (float)$productData['unit_price'];
                    $discount = (float)($productData['discount'] ?? 0);
                    $cgstRate = (float)($productData['cgst_rate'] ?? 0);
                    $sgstRate = (float)($productData['sgst_rate'] ?? 0);
                    $igstRate = (float)($productData['igst_rate'] ?? 0);

                    $baseTotal = $quantity * $unitPrice;
                    $discountAmount = $baseTotal * ($discount / 100);
                    $priceAfterDiscount = $baseTotal - $discountAmount;
                    $gstAmount = $priceAfterDiscount * (($cgstRate + $sgstRate + $igstRate) / 100);
                    $totalWithGST = $priceAfterDiscount + $gstAmount;

                    $totalAmount += $totalWithGST;
                    $totalDiscount += $discountAmount;
                    $totalGstAmount += $gstAmount;
                }

                $purchaseEntry = PurchaseEntry::create([
                    'purchase_number' => 'PE-' . strtoupper(Str::random(8)),
                    'purchase_order_id' => $validated['purchase_order_id'],
                    'purchase_date' => $validated['purchase_date'],
                    'invoice_number' => $validated['invoice_number'],
                    'invoice_date' => $validated['invoice_date'],
                    'party_id' => $validated['party_id'],
                    'note' => $validated['note'],
                    'gst_amount' => $totalGstAmount,
                    'discount' => $totalDiscount,
                    'from_receipt_note' => false, // Default for direct entries
                ]);

                foreach ($receivedProducts as $itemData) {
                    $quantity = (float)$itemData['quantity'];
                    $unitPrice = (float)$itemData['unit_price'];
                    $discount = (float)($itemData['discount'] ?? 0);
                    $cgstRate = (float)($itemData['cgst_rate'] ?? 0);
                    $sgstRate = (float)($itemData['sgst_rate'] ?? 0);
                    $igstRate = (float)($itemData['igst_rate'] ?? 0);

                    $baseTotal = $quantity * $unitPrice;
                    $discountAmount = $baseTotal * ($discount / 100);
                    $priceAfterDiscount = $baseTotal - $discountAmount;
                    $gstAmount = $priceAfterDiscount * (($cgstRate + $sgstRate + $igstRate) / 100);

                    PurchaseEntryItem::create([
                        'purchase_entry_id' => $purchaseEntry->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $quantity,
                        'unit_price' => $itemData['unit_price'],
                        'discount' => $discount,
                        'cgst_rate' => $cgstRate,
                        'sgst_rate' => $sgstRate,
                        'igst_rate' => $igstRate,
                        'total_price' => $priceAfterDiscount + $gstAmount,
                        'status' => $itemData['status'],
                    ]);

                    // Only update stock for direct entries, not converted ones
                    if ($itemData['status'] === 'received' && !$purchaseEntry->from_receipt_note) {
                        $productModel = Product::find($itemData['product_id']);
                        if ($productModel) {
                            $productModel->increment('stock', $quantity);
                        }
                    }
                }

                Payable::create([
                    'purchase_entry_id' => $purchaseEntry->id,
                    'party_id' => $validated['party_id'],
                    'amount' => $totalAmount,
                    'is_paid' => false,
                ]);

                // Check if all items in the purchase order are received
                $purchaseOrderItems = PurchaseOrderItem::where('purchase_order_id', $validated['purchase_order_id'])->get();
                $allItemsReceived = true;

                foreach ($purchaseOrderItems as $orderItem) {
                    $orderedQty = $orderItem->quantity;
                    $receivedQty = PurchaseEntryItem::whereHas('purchaseEntry', function ($q) use ($validated) {
                        $q->where('purchase_order_id', $validated['purchase_order_id']);
                    })->where('product_id', $orderItem->product_id)
                        ->where('status', 'received')
                        ->sum('quantity');

                    if ($receivedQty < $orderedQty) {
                        $allItemsReceived = false;
                        break;
                    }
                }

                if ($allItemsReceived) {
                    PurchaseOrder::where('id', $validated['purchase_order_id'])->update(['status' => 'received']);
                }

                return redirect()->route('purchase_entries.index')->with('success', 'Purchase entry created successfully.');
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Purchase Entry Store Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'An error occurred. Please check the logs.');
        }
    }

    public function store_bk(Request $request)
    {
        Log::info('Starting purchase entry store method', $request->all());

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'party_id'          => 'required|exists:parties,id',
            'invoice_number'    => 'required|string|max:255|unique:purchase_entries,invoice_number',
            'invoice_date'      => 'required|date',
            'purchase_date'     => 'required|date',
            'note'              => 'nullable|string',
            'products'          => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity'   => 'required|numeric|min:0',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.discount'   => 'nullable|numeric|min:0|max:100',
            'products.*.cgst_rate'  => 'nullable|numeric|min:0|max:100',
            'products.*.sgst_rate'  => 'nullable|numeric|min:0|max:100',
            'products.*.igst_rate'  => 'nullable|numeric|min:0|max:100',
            'products.*.status'     => 'required|in:pending,received',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $receivedProducts = array_filter($validated['products'], function ($product) {
                    return $product['quantity'] > 0;
                });

                if (empty($receivedProducts)) {
                    return;
                }

                $totalAmount = 0;
                $totalDiscount = 0;
                $totalGstAmount = 0;

                foreach ($receivedProducts as $productData) {
                    $quantity = (float)$productData['quantity'];
                    $unitPrice = (float)$productData['unit_price'];
                    $discount = (float)($productData['discount'] ?? 0);
                    $cgstRate = (float)($productData['cgst_rate'] ?? 0);
                    $sgstRate = (float)($productData['sgst_rate'] ?? 0);
                    $igstRate = (float)($productData['igst_rate'] ?? 0);

                    $baseTotal = $quantity * $unitPrice;
                    $discountAmount = $baseTotal * ($discount / 100);
                    $priceAfterDiscount = $baseTotal - $discountAmount;
                    $gstAmount = $priceAfterDiscount * (($cgstRate + $sgstRate + $igstRate) / 100);
                    $totalWithGST = $priceAfterDiscount + $gstAmount;

                    $totalAmount += $totalWithGST;
                    $totalDiscount += $discountAmount;
                    $totalGstAmount += $gstAmount;
                }

                $purchaseEntry = PurchaseEntry::create([
                    'purchase_number'    => 'PE-' . strtoupper(Str::random(8)),
                    'purchase_order_id'  => $validated['purchase_order_id'],
                    'purchase_date'      => $validated['purchase_date'],
                    'invoice_number'     => $validated['invoice_number'],
                    'invoice_date'       => $validated['invoice_date'],
                    'party_id'           => $validated['party_id'],
                    'note'               => $validated['note'],
                    'gst_amount'         => $totalGstAmount,
                    'discount'           => $totalDiscount,
                ]);

                foreach ($receivedProducts as $itemData) {
                    $quantity = (float)$itemData['quantity'];
                    $unitPrice = (float)$itemData['unit_price'];
                    $discount = (float)($itemData['discount'] ?? 0);
                    $cgstRate = (float)($itemData['cgst_rate'] ?? 0);
                    $sgstRate = (float)($itemData['sgst_rate'] ?? 0);
                    $igstRate = (float)($itemData['igst_rate'] ?? 0);

                    $baseTotal = $quantity * $unitPrice;
                    $discountAmount = $baseTotal * ($discount / 100);
                    $priceAfterDiscount = $baseTotal - $discountAmount;
                    $gstAmount = $priceAfterDiscount * (($cgstRate + $sgstRate + $igstRate) / 100);

                    PurchaseEntryItem::create([
                        'purchase_entry_id' => $purchaseEntry->id,
                        'product_id'        => $itemData['product_id'],
                        'quantity'          => $quantity,
                        'unit_price'        => $unitPrice,
                        'discount'          => $discount,
                        'cgst_rate'         => $cgstRate,
                        'sgst_rate'         => $sgstRate,
                        'igst_rate'         => $igstRate,
                        'total_price'       => $priceAfterDiscount + $gstAmount,
                        'status'            => $itemData['status'],
                    ]);

                    if ($itemData['status'] === 'received') {
                        $productModel = Product::find($itemData['product_id']);
                        if ($productModel) {
                            $productModel->increment('stock', $quantity);
                        }
                    }
                }

                Payable::create([
                    'purchase_entry_id' => $purchaseEntry->id,
                    'party_id'          => $validated['party_id'],
                    'amount'            => $totalAmount,
                    'is_paid'           => false,
                ]);

                // ✅ Check if all items in the purchase order are received
                $purchaseOrderItems = \App\Models\PurchaseOrderItem::where('purchase_order_id', $validated['purchase_order_id'])->get();
                $allItemsReceived = true;

                foreach ($purchaseOrderItems as $orderItem) {
                    $orderedQty = $orderItem->quantity;

                    $receivedQty = \App\Models\PurchaseEntryItem::whereHas('purchaseEntry', function ($q) use ($validated) {
                        $q->where('purchase_order_id', $validated['purchase_order_id']);
                    })->where('product_id', $orderItem->product_id)
                        ->where('status', 'received')
                        ->sum('quantity');

                    if ($receivedQty < $orderedQty) {
                        $allItemsReceived = false;
                        break;
                    }
                }

                if ($allItemsReceived) {
                    \App\Models\PurchaseOrder::where('id', $validated['purchase_order_id'])->update([
                        'status' => 'received',
                    ]);
                }
            });

            return redirect()->route('purchase_entries.index')->with('success', 'Purchase entry created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Purchase Entry Store Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'An error occurred. Please check the logs.');
        }
    }


    public function edit($id)
    {
        $purchaseEntry = PurchaseEntry::with('items.product')->findOrFail($id);
        $parties = Party::all();
        $products = Product::all();
        $purchaseOrders = PurchaseOrder::all();
        return view('purchase_entries.edit', compact('purchaseEntry', 'parties', 'products', 'purchaseOrders'));
    }

    public function update(Request $request, $id)
    {
        Log::info('Starting update method', $request->all());

        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'purchase_date' => 'required|date',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'party_id' => 'required|exists:parties,id',
            'note' => 'nullable|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0|max:100',
            'products.*.cgst_rate' => 'nullable|numeric|min:0|max:100',
            'products.*.sgst_rate' => 'nullable|numeric|min:0|max:100',
            'products.*.igst_rate' => 'nullable|numeric|min:0|max:100',
            'products.*.status' => 'required|in:pending,received',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $purchaseEntry = PurchaseEntry::findOrFail($id);

                $totalAmount = 0;
                $totalDiscount = 0;
                $totalGstAmount = 0;

                $itemsData = [];
                foreach ($request->products as $item) {
                    $baseTotal = $item['quantity'] * $item['unit_price'];
                    $discount = $item['discount'] ?? 0;
                    $discountedBaseTotal = $baseTotal * (1 - ($discount / 100));
                    $totalDiscount += $baseTotal * ($discount / 100);

                    $cgstRate = $item['cgst_rate'] ?? 0;
                    $sgstRate = $item['sgst_rate'] ?? 0;
                    $igstRate = $item['igst_rate'] ?? 0;

                    $cgstAmount = $discountedBaseTotal * ($cgstRate / 100);
                    $sgstAmount = $discountedBaseTotal * ($sgstRate / 100);
                    $igstAmount = $discountedBaseTotal * ($igstRate / 100);
                    $gstAmount = $cgstAmount + $sgstAmount + $igstAmount;

                    $totalWithGST = $discountedBaseTotal + $gstAmount;
                    $totalAmount += $totalWithGST;
                    $totalGstAmount += $gstAmount;

                    $itemsData[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $discount,
                        'total_price' => $totalWithGST,
                        'cgst_rate' => $cgstRate,
                        'sgst_rate' => $sgstRate,
                        'igst_rate' => $igstRate,
                        'status' => $item['status'],
                    ];
                }

                $purchaseEntry->update([
                    'purchase_order_id' => $request->purchase_order_id,
                    'purchase_date' => $request->purchase_date,
                    'invoice_number' => $request->invoice_number,
                    'invoice_date' => $request->invoice_date,
                    'party_id' => $request->party_id,
                    'note' => $request->note,
                    'gst_amount' => $totalGstAmount,
                    'discount' => $totalDiscount,
                ]);

                PurchaseEntryItem::where('purchase_entry_id', $purchaseEntry->id)->delete();
                foreach ($itemsData as $itemData) {
                    $item = PurchaseEntryItem::create([
                        'purchase_entry_id' => $purchaseEntry->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'discount' => $itemData['discount'],
                        'total_price' => $itemData['total_price'],
                        'cgst_rate' => $itemData['cgst_rate'],
                        'sgst_rate' => $itemData['sgst_rate'],
                        'igst_rate' => $itemData['igst_rate'],
                        'status' => $itemData['status'],
                    ]);

                    if ($itemData['status'] === 'received') {
                        $productModel = Product::find($itemData['product_id']);
                        if (!$productModel) {
                            Log::error("Product not found for ID: {$itemData['product_id']}");
                            throw new \Exception("Product not found for ID: {$itemData['product_id']}");
                        }
                        Log::info("Calling updateStock for product ID: {$itemData['product_id']}, Quantity: {$itemData['quantity']}");
                        $productModel->updateStock($itemData['quantity']);
                    }
                }

                Payable::updateOrCreate(
                    ['purchase_entry_id' => $purchaseEntry->id],
                    ['party_id' => $request->party_id, 'amount' => $totalAmount, 'is_paid' => false]
                );
            });

            return redirect()->route('purchase_entries.index')->with('success', 'Purchase entry updated.');
        } catch (\Exception $e) {
            Log::error('Transaction failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the purchase entry. Check logs for details.']);
        }
    }

    public function show($id)
    {
        try {
            $purchaseEntry = PurchaseEntry::with(['party', 'items.product'])->findOrFail($id);
            return view('purchase_entries.show', compact('purchaseEntry'));
        } catch (\Exception $e) {
            Log::error('Error fetching purchase entry', ['id' => $id, 'message' => $e->getMessage()]);
            return redirect()->route('purchase_entries.index')->with('error', 'Failed to fetch purchase entry details.');
        }
    }
}
