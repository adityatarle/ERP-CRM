<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\InvoiceController; // Important for conversion
use Illuminate\Support\Facades\Log;
use PDF;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $deliveryNotes = DeliveryNote::with(['customer', 'items.product'])
            ->where('is_invoiced', false)
            ->latest('delivery_date')
            ->get();
        return view('delivery_notes.index', compact('deliveryNotes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::select('id', 'name')->get()->map(function ($c) {
            return ['id' => $c->id, 'name' => htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8')];
        });
        $products = Product::select('id', 'name', 'stock', 'price', 'item_code')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'),
                'stock' => $p->stock,
                'price' => $p->price,
                'itemcode' => $p->item_code,
            ];
        });
        $categories = Product::distinct()->pluck('category')->filter()->values();
        $subcategories = Product::distinct()->pluck('subcategory')->filter()->values();
        return view('delivery_notes.create', compact('customers', 'products', 'categories', 'subcategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'ref_no' => 'nullable|string|max:255',
            'purchase_number' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'delivery_date' => 'required|date',
            // GST fields can be nullable for a DN, as it's non-financial
            'gst_type' => 'nullable|in:CGST,IGST',
            'cgst' => 'nullable|numeric|min:0|max:100',
            'sgst' => 'nullable|numeric|min:0|max:100',
            'igst' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'required|numeric|min:0|max:100',
            'items.*.itemcode' => 'nullable|string|max:255',
            'items.*.secondary_itemcode' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Check stock for all items before making any database changes
            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                if ($product->stock < $itemData['quantity']) {
                    // It's better to throw a ValidationException here
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => "Insufficient stock for product: {$product->name}. Available: {$product->stock}, Requested: {$itemData['quantity']}",
                    ]);
                }
            }

            $deliveryNote = DeliveryNote::create([
                'delivery_note_number' => 'DN-' . strtoupper(uniqid()),
                'customer_id' => $validated['customer_id'],
                'ref_no' => $validated['ref_no'],
                'purchase_number' => $validated['purchase_number'],
                'purchase_date' => $validated['purchase_date'],
                'delivery_date' => $validated['delivery_date'],
                'gst_type' => $validated['gst_type'] ?? null,
                'cgst' => $validated['gst_type'] === 'CGST' ? $validated['cgst'] : null,
                'sgst' => $validated['gst_type'] === 'CGST' ? $validated['sgst'] : null,
                'igst' => $validated['gst_type'] === 'IGST' ? $validated['igst'] : null,
                'description' => $validated['description'],
                'notes' => $validated['notes'],
                'is_invoiced' => false,
            ]);

            foreach ($validated['items'] as $item) {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'],
                    'itemcode' => $item['itemcode'],
                    'secondary_itemcode' => $item['secondary_itemcode'],
                ]);

                // --- THE FIX IS HERE ---
                // Decrement the stock for the product
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->decrement('stock', $item['quantity']);
                }
                // --- END OF FIX ---
            }

            DB::commit();
            
            // Return a success response for AJAX requests
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Delivery note created successfully.']);
            }
            // Or redirect for standard form submissions
            return redirect()->route('delivery_notes.index')->with('success', 'Delivery note created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery note creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while creating the delivery note.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['items.product', 'customer']);
        return view('delivery_notes.show', compact('deliveryNote'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeliveryNote $deliveryNote)
    {
        $customers = Customer::all();
        $products = Product::all();
        $categories = Product::distinct()->pluck('category')->sort();
        $subcategories = Product::distinct()->pluck('subcategory')->sort();
        $deliveryNote->load('items.product');
        return view('delivery_notes.edit', compact('deliveryNote', 'customers', 'products', 'categories', 'subcategories'));
    }

    /**
     * NEW METHOD: Just updates the Delivery Note without creating an invoice.
     */
    public function updateOnly(Request $request, DeliveryNote $deliveryNote)
    {
        // Use a simpler validation for non-financial updates
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'ref_no' => 'nullable|string|max:255',
            'purchase_number' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'delivery_date' => 'required|date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'required|numeric|min:0|max:100',
            'items.*.itemcode' => 'nullable|string|max:255',
            'items.*.secondary_itemcode' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $deliveryNote->update($request->only([
                'customer_id',
                'ref_no',
                'purchase_number',
                'purchase_date',
                'delivery_date',
                'description',
                'notes',
                'gst_type',
                'cgst',
                'sgst',
                'igst'
            ]));

            $deliveryNote->items()->delete();
            foreach ($validated['items'] as $item) {
                $deliveryNote->items()->create($item);
            }
            DB::commit();

            return redirect()->route('delivery_notes.index')->with('success', 'Delivery note updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery note update-only failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating the delivery note.');
        }
    }

    /**
     * RENAMED METHOD: This now explicitly handles the conversion to an invoice.
     */
    public function updateAndConvertToInvoice(Request $request, DeliveryNote $deliveryNote)
    {
        Log::info('Attempting to convert Delivery Note to Invoice.', ['dn_id' => $deliveryNote->id]);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'ref_no' => 'nullable|string|max:255',
            'purchase_number' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'delivery_date' => 'required|date',
            'gst_type' => 'required|in:CGST,IGST',
            'cgst' => 'required_if:gst_type,CGST|nullable|numeric|min:0|max:100',
            'sgst' => 'required_if:gst_type,CGST|nullable|numeric|min:0|max:100',
            'igst' => 'required_if:gst_type,IGST|nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:9999',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'required|numeric|min:0|max:100',
            'items.*.itemcode' => 'nullable|string|max:255',
            'items.*.secondary_itemcode' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // THE FIX: Prepare data safely for the InvoiceController
            $invoiceRequestData = [
                'customer_id' => $validated['customer_id'],
                'ref_no' => $validated['ref_no'],
                'purchase_number' => $validated['purchase_number'],
                'purchase_date' => $validated['purchase_date'],
                'gst_type' => $validated['gst_type'],
                // Use data_get() to safely access keys, providing null as a default
                'cgst' => data_get($validated, 'cgst'),
                'sgst' => data_get($validated, 'sgst'),
                'igst' => data_get($validated, 'igst'),
                'description' => $validated['description'],
                'products' => array_map(function ($item) {
                    return [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'sale_price' => $item['price'],
                        'discount' => $item['discount'],
                        'itemcode' => $item['itemcode'],
                        'secondary_itemcode' => $item['secondary_itemcode'],
                    ];
                }, $validated['items']),
            ];

            $invoiceController = new InvoiceController();
            // Pass the safely prepared data as a new Request object
            $invoiceResponse = $invoiceController->store(new Request($invoiceRequestData));

            $invoiceResponseData = json_decode($invoiceResponse->getContent(), true);

            if (!$invoiceResponseData['success']) {
                throw new \Exception('Invoice creation failed: ' . ($invoiceResponseData['message'] ?? 'Unknown error.'));
            }

            // If invoice creation succeeds, delete the original delivery note
            $deliveryNote->items()->delete();
            $deliveryNote->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $invoiceResponseData['message'] ?? 'Delivery note converted to invoice successfully!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery note conversion failed', ['dn_id' => $deliveryNote->id, 'error' => $e->getMessage()]);
            // Return a more informative error message
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Download the Delivery Note as a PDF.
     */
    public function downloadPdf(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['customer', 'items.product']);
        $company = [
            'name' => 'MAULI SOLUTIONS',
            'address' => 'Gat No-627, Pune-Nashik Highway...',
            'contact' => 'Mob-9284716150/9158506948',
            'gstin' => '27ABIFM9220D1ZC',
            'state' => 'Maharashtra, Code: 27',
            'email' => 'maulisolutions18@gmail.com',
            'pan' => 'ABIFM9220D'
        ];

        $subtotal = 0;
        $totalCgst = 0;
        $totalSgst = 0;
        $totalIgst = 0;
        $totalQuantity = 0;
        foreach ($deliveryNote->items as $item) {
            $totalQuantity += $item->quantity;
            $priceAfterDiscount = ($item->quantity * $item->price) * (1 - (($item->discount ?? 0) / 100));
            $subtotal += $priceAfterDiscount;
            $totalCgst += $priceAfterDiscount * (($deliveryNote->cgst ?? 0) / 100);
            $totalSgst += $priceAfterDiscount * (($deliveryNote->sgst ?? 0) / 100);
            $totalIgst += $priceAfterDiscount * (($deliveryNote->igst ?? 0) / 100);
        }
        $grandTotal = $subtotal + $totalCgst + $totalSgst + $totalIgst;
        $amountInWords = function_exists('numberToWords') ? numberToWords(round($grandTotal)) : '';
        $data = compact('deliveryNote', 'company', 'totalQuantity', 'subtotal', 'totalCgst', 'totalSgst', 'totalIgst', 'grandTotal', 'amountInWords');
        $pdf = PDF::loadView('delivery_notes.pdf', $data);
        return $pdf->download('Delivery_Note_' . $deliveryNote->delivery_note_number . '.pdf');
    }
}
