<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Receivable;
use App\Models\DeliveryNote;
use App\Models\Product;
use App\Models\User; // Import User model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Notification; // For sending notifications
use App\Notifications\InvoiceUnlockRequested; // Import your Notification class
// use App\Notifications\InvoiceUnlockDecision;  // If you create this for notifying requester of decision
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF; // Assuming you're using a PDF package aliased as PDF

class InvoiceController extends Controller
{
    public function __construct()
    {
        // Apply middleware. 'auth' is likely applied globally or to a group in routes/web.php
        // $this->middleware('auth'); // Uncomment if not applied elsewhere

        // REMOVED: 'pendingInvoices' and 'approve' as they are no longer needed.
        $this->middleware('superadmin')->only(['listPendingUnlockRequests', 'manageUnlockRequestForm', 'decideUnlockRequest']);
    }

    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'customer_search' => 'nullable|string|max:255',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $customer_search = $request->input('customer_search');

        // Start building the query
        $query = Invoice::with(['customer', 'sales.saleItems.product', 'requester']);

        // Apply customer name filter if provided
        if ($customer_search) {
            $query->whereHas('customer', function ($q) use ($customer_search) {
                $q->where('name', 'like', '%' . $customer_search . '%');
            });
        }

        // Apply date range filter if both dates are provided
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // --- THE FIX IS HERE ---
        // Calculate the total amount from the *unpaginated* filtered query
        // We clone the query to avoid affecting the pagination query
        $filteredInvoicesForTotal = $query->clone()->get();
        $filteredTotal = $filteredInvoicesForTotal->sum('total');
        $filteredCount = $filteredInvoicesForTotal->count();
        
        // Calculate overall totals (without filters) for comparison
        $overallTotal = Invoice::sum('total');
        $overallCount = Invoice::count();
        // --- END OF FIX ---

        // Now, apply ordering and pagination to the original query for display
        $invoices = $query->latest()->paginate(15);

        return view('invoices.index', compact(
            'invoices', 
            'startDate', 
            'endDate', 
            'customer_search',
            'filteredTotal',
            'filteredCount',
            'overallTotal',
            'overallCount'
        ));
    }

    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();

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

        return view('invoices.create', compact('customers', 'products', 'categories', 'subcategories'));
    }


public function store(Request $request)
    {
        Log::debug('Invoice store called', ['request_data' => $request->all()]);

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'ref_no' => 'nullable|string|max:255',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1|max:9999',
                'products.*.sale_price' => 'required|numeric|min:0.01|max:999999.99',
                'products.*.discount' => 'required|numeric|min:0|max:100',
                'products.*.itemcode' => 'nullable|string|max:255',
                'products.*.secondary_itemcode' => 'nullable|string|max:255',
                'gst_type' => 'required|in:CGST,IGST',
                'cgst' => 'nullable|required_if:gst_type,CGST|numeric|min:0|max:100',
                'sgst' => 'nullable|required_if:gst_type,CGST|numeric|min:0|max:100',
                'igst' => 'nullable|required_if:gst_type,IGST|numeric|min:0|max:100',
                'description' => 'nullable|string',
                'purchase_number' => 'required|string|max:255',
                'purchase_date' => 'required|date',
                'contact_person' => 'nullable|string',
            ]);
            
            // ENHANCED VALIDATION: Check that all products have valid sale prices
            foreach ($validated['products'] as $index => $productData) {
                if (empty($productData['sale_price']) || $productData['sale_price'] <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false, 
                        'message' => "Product " . ($index + 1) . " must have a valid sale price greater than 0."
                    ], 422);
                }
                
                // Check if sale_price is a valid number
                if (!is_numeric($productData['sale_price'])) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false, 
                        'message' => "Product " . ($index + 1) . " sale price must be a valid number."
                    ], 422);
                }
            }
            
            $totalSalePrice = 0;
            $saleItemsData = [];
            
            $gstType = $validated['gst_type'];
            $gstRate = ($gstType === 'CGST') ? (($validated['cgst'] ?? 9) + ($validated['sgst'] ?? 9)) : ($validated['igst'] ?? 18);

            // Check if this is a conversion from delivery note (has purchase_number and purchase_date)
            $isFromDeliveryNote = !empty($validated['purchase_number']) && !empty($validated['purchase_date']);

            foreach ($validated['products'] as $index => $productData) {
                $product = Product::findOrFail($productData['product_id']);
                
                // Only check stock if NOT converting from delivery note (since stock was already updated)
                if (!$isFromDeliveryNote && $product->stock < $productData['quantity']) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Insufficient stock for product {$product->name}."], 422);
                }

                $unitPrice = $productData['sale_price'];
                $quantity = $productData['quantity'];
                $discountPercentage = $productData['discount'];
                $baseTotalPrice = $unitPrice * $quantity;
                $discountAmount = ($baseTotalPrice * $discountPercentage) / 100;
                $itemTotalPrice = $baseTotalPrice - $discountAmount;

                // --- THE FIX IS HERE ---
                // Add unit_price and discount to the array being prepared for the database.
                $saleItemsData[] = [
                    'product_id'         => $product->id,
                    'quantity'           => $quantity,
                    'unit_price'         => $unitPrice,
                    'discount'           => $discountPercentage,
                    'total_price'        => $itemTotalPrice,
                    'itemcode'           => $productData['itemcode'] ?? null,
                    'secondary_itemcode' => $productData['secondary_itemcode'] ?? null,
                ];
                // --- END OF FIX ---

                $totalSalePrice += $itemTotalPrice;
            }

            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'ref_no' => $validated['ref_no'] ?? null, // Ensure ref_no is handled if not present
                'total_price' => $totalSalePrice,
                'status' => 'confirmed',
            ]);
            
            foreach ($saleItemsData as $itemData) {
                $sale->saleItems()->create($itemData);
                // Only decrement stock if NOT converting from delivery note
                if (!$isFromDeliveryNote) {
                    Product::find($itemData['product_id'])->decrement('stock', $itemData['quantity']);
                }
            }

            $subtotal = $totalSalePrice;
            $gstAmount = $subtotal * ($gstRate / 100);
            $total = $subtotal + $gstAmount;
            
            $customer = Customer::find($validated['customer_id']);
            $overdueInvoices = Invoice::where('customer_id', $validated['customer_id'])
                ->whereIn('payment_status', ['unpaid', 'partially_paid'])
                ->whereNotNull('due_date')
                ->where('due_date', '<', Carbon::today())
                ->exists();

            $invoiceStatus = $overdueInvoices ? 'on_hold' : 'approved';

            if ($overdueInvoices) {
                Log::info('Customer has overdue invoices, setting new invoice status to on_hold', ['customer_id' => $validated['customer_id']]);
            }

            $creditDays = $customer->default_credit_days ?? 30;

            $invoiceData = [
                'invoice_number' => 'INV-' . strtoupper(uniqid()),
                'customer_id' => $validated['customer_id'],
                'subtotal' => $subtotal,
                'tax' => $gstAmount,
                'total' => $total,
                'status' => $invoiceStatus,
                'gst' => $gstRate,
                'gst_type' => $gstType,
                'purchase_number' => $validated['purchase_number'],
                'purchase_date' => $validated['purchase_date'],
                'contact_person' => $request->input('contact_person'),
                'description' => $request->input('description'),
                'cgst' => $gstType === 'CGST' ? ($validated['cgst'] ?? 9) : null,
                'sgst' => $gstType === 'CGST' ? ($validated['sgst'] ?? 9) : null,
                'igst' => $gstType === 'IGST' ? ($validated['igst'] ?? 18) : null,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays($creditDays)->toDateString(),
                'amount_paid' => 0.00,
                'payment_status' => 'unpaid',
            ];

            $invoice = Invoice::create($invoiceData);
            $invoice->sales()->sync([$sale->id]);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $invoiceStatus === 'on_hold' ? 'Invoice created but placed on hold due to overdue payments.' : 'Invoice created successfully.',
                'messageType' => $invoiceStatus === 'on_hold' ? 'warning' : 'success'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Invoice creation validation failed', ['errors' => $e->errors(), 'request_data' => $request->all()]);
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'An error occurred while creating the invoice: ' . $e->getMessage()], 500);
        }
    }





    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'sales.saleItems.product', 'sales.receivable', 'requester', 'unlockDecisionMaker']);
        return view('invoices.show', compact('invoice'));
    }

// In app/Http/Controllers/InvoiceController.php
// In your InvoiceController.php
// In your InvoiceController.php

public function generatePDF(Invoice $invoice)
{
    // Eager load all necessary relationships
    $invoice->load(['customer', 'sales.saleItems.product']);

    // --- Data Preparation ---
    $company = [
        'name' => 'MAULI SOLUTIONS',
        'address' => 'Gat No-627, Pune-Nashik Highway, IN Front Off Gabriel, Vitthal-Muktai Complex, Kuruli Chakan, Pune-410501',
        'contact' => 'Mob-9284716150/9158506948',
        'udyam' => 'UDYAM-MH-26-0484571 (Small)',
        'gstin' => '27ABIFM9220D1ZC',
        'state' => 'Maharashtra, Code: 27',
        'email' => 'maulisolutions18@gmail.com',
        'pan' => 'ABIFM9220D',
        'bank_name' => 'ICICI BANK',
        'account_holder' => 'MAULI SOLUTIONS',
        'account_no' => '410305000280',
        'ifsc_code' => 'ICIC0004103',
    ];

    // Get all items in a single collection. NO MORE CHUNKING.
    $allItems = $invoice->sales->flatMap->saleItems;

    // Calculate the HSN summary for the entire invoice
    $hsnSummary = [];
    foreach ($allItems as $item) {
        $hsn = $item->product->hsn;
        if (!isset($hsnSummary[$hsn])) {
            $hsnSummary[$hsn] = [
                'taxable_value' => 0,
                'cgst_rate' => $invoice->cgst,
                'sgst_rate' => $invoice->sgst,
                'igst_rate' => $invoice->igst,
            ];
        }
        $hsnSummary[$hsn]['taxable_value'] += $item->total_price;
    }

    $totalQuantity = $allItems->sum('quantity');
    $amount_in_words = function_exists('numberToWords') ? numberToWords(round($invoice->total)) : '';
    $tax_amount_in_words = function_exists('numberToWords') ? numberToWords(round($invoice->tax)) : '';

    $label = match ($invoice->download_count) {
        0 => 'ORIGINAL FOR BUYER',
        1 => 'DUPLICATE FOR TRANSPORTER',
        2 => 'TRIPLICATE FOR SUPPLIER',
        default => 'EXTRA COPY',
    };
    $invoice->increment('download_count');

    // --- PDF Generation ---
    // Pass the full $allItems collection to the view
    $data = compact(
        'invoice', 
        'company', 
        'label',
        'allItems',
        'hsnSummary',
        'totalQuantity',
        'amount_in_words',
        'tax_amount_in_words'
    );

    // Load the new, continuous-flow view
    $pdf = PDF::loadView('invoices.pdf', $data); 

    return $pdf->download('invoice-' . $invoice->invoice_number . '-' . $label . '.pdf');
}
    // REMOVED: The following two methods, pendingInvoices() and approve(), are no longer needed
    // because the approval workflow has been removed. I have commented them out. You can also delete them.
    /*
    public function pendingInvoices()
    {
        // This method is now obsolete.
        $invoices = Invoice::with(['customer', 'sales'])
            ->whereIn('status', ['pending', 'on_hold'])
            ->latest()
            ->paginate(15);
        return view('invoices.pending', compact('invoices'));
    }

    public function approve(Invoice $invoice)
    {
        // This method is now obsolete.
        $customerId = $invoice->customer_id;
        $overdueReceivables = Receivable::where('customer_id', $customerId)
            ->where('is_paid', false)
            ->get()
            ->filter(function ($receivable) {
                if (is_null($receivable->credit_days)) return false;
                $dueDate = Carbon::parse($receivable->created_at)->addDays($receivable->credit_days);
                return Carbon::now()->greaterThan($dueDate);
            });
        if ($overdueReceivables->isNotEmpty() && $invoice->status === 'on_hold') {
            return redirect()->route('invoices.pending')->with('error', 'Cannot approve. Customer still has overdue receivables that caused this invoice to be on hold.');
        }
        $invoice->update(['status' => 'approved']);
        return redirect()->route('invoices.pending')->with('success', 'Invoice approved successfully.');
    }
    */

    public function edit($id)
    {
        $invoice = Invoice::with(['sales', 'requester'])->findOrFail($id);

        if (Auth::user()->role !== 'superadmin') {
            // CHANGED: The condition for a non-superadmin to edit an invoice.
            // 'pending' is removed. They can edit 'on_hold' invoices freely
            // (e.g., to fix them), but 'approved' or 'paid' invoices still need an unlock request.
            $canEditNonSuperAdmin = $invoice->status === 'on_hold' ||
                (in_array($invoice->status, ['approved', 'paid']) && $invoice->edit_request_status === 'unlock_approved');

            if (!$canEditNonSuperAdmin) {
                return redirect()->route('invoices.index')->with('error', 'This invoice is locked for editing. Please request an unlock if applicable.');
            }
        }

        $customers = Customer::orderBy('name')->get();
        $currentSaleIds = $invoice->sales->pluck('id')->toArray();
        $availableSales = Sale::where('status', 'confirmed')
            ->where(function ($query) use ($currentSaleIds, $invoice) {
                $query->whereDoesntHave('invoices', function ($q) use ($invoice) {
                    $q->where('invoice_id', '!=', $invoice->id);
                })
                    ->orWhereIn('id', $currentSaleIds);
            })
            ->with(['customer', 'saleItems.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('invoices.edit', compact('invoice', 'availableSales', 'customers', 'currentSaleIds'));
    }


    public function update(Request $request, $id)
    {
        // ... (validation logic is the same)
        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'ref_no' => 'nullable|string|max:255',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1|max:9999',
                'products.*.sale_price' => 'required|numeric|min:0',
                'products.*.discount' => 'required|numeric|min:0|max:100',
                'products.*.itemcode' => 'nullable|string|max:255',
                'products.*.secondary_itemcode' => 'nullable|string|max:255',
                'gst_type' => 'required|in:CGST,SGST,IGST',
                'cgst' => 'required_if:gst_type,CGST,SGST|numeric|min:0|max:100',
                'sgst' => 'required_if:gst_type,CGST,SGST|numeric|min:0|max:100',
                'igst' => 'required_if:gst_type,IGST|numeric|min:0|max:100',
                'description' => 'nullable|string',
                'purchase_number' => 'required|string|max:255',
                'purchase_date' => 'required|date',
            ]);

            DB::beginTransaction();

            // ... (stock reversal and sale update logic is the same)

            $invoice = Invoice::findOrFail($id);
            $sale = $invoice->sales()->first();
            if ($sale) {
                foreach ($sale->saleItems as $saleItem) {
                    $product = Product::findOrFail($saleItem->product_id);
                    $product->increment('stock', $saleItem->quantity);
                }
                $sale->saleItems()->delete();
            }

            $totalSalePrice = 0;
            $saleItemsData = [];
            $gstType = $validated['gst_type'];
            $gstRate = 0;
            if ($gstType === 'CGST' || $gstType === 'SGST') {
                $gstRate = $validated['cgst'] + $validated['sgst'];
            } else {
                $gstRate = $validated['igst'];
            }

            foreach ($validated['products'] as $index => $productData) {
                $product = Product::findOrFail($productData['product_id']);
                if ($product->stock < $productData['quantity']) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Insufficient stock for product {$product->name}.", 'errors' => ["products.{$index}.quantity" => "Insufficient stock for product {$product->name}."]], 422);
                }
                $unitPrice = $productData['sale_price'];
                $discount = $productData['discount'];
                $baseTotalPrice = $unitPrice * $productData['quantity'];
                $discountAmount = ($baseTotalPrice * $discount) / 100;
                $itemTotalPrice = $baseTotalPrice - $discountAmount;
                $saleItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'total_price' => $itemTotalPrice,
                    'itemcode' => $productData['itemcode'] ?? null,
                    'secondary_itemcode' => $productData['secondary_itemcode'] ?? null,
                ];
                $totalSalePrice += $itemTotalPrice;
            }

            if (!$sale) {
                $sale = Sale::create(['customer_id' => $validated['customer_id'], 'ref_no' => $validated['ref_no'], 'total_price' => $totalSalePrice, 'status' => 'confirmed']);
            } else {
                $sale->update(['customer_id' => $validated['customer_id'], 'ref_no' => $validated['ref_no'], 'total_price' => $totalSalePrice, 'status' => 'confirmed']);
            }

            foreach ($saleItemsData as $itemData) {
                $sale->saleItems()->create($itemData);
                $product = Product::findOrFail($itemData['product_id']);
                $product->decrement('stock', $itemData['quantity']);
            }

            $subtotal = $totalSalePrice;
            $gstAmount = $subtotal * ($gstRate / 100);
            $total = $subtotal + $gstAmount;
            $customer = Customer::find($validated['customer_id']);
            $creditDays = $customer->default_credit_days ?? 30;

            $overdueReceivables = Receivable::where('customer_id', $validated['customer_id'])
                ->where('is_paid', false)
                ->where('invoice_id', '!=', $invoice->id)
                ->get()
                ->filter(function ($receivable) {
                    if (is_null($receivable->credit_days)) return false;
                    $dueDate = Carbon::parse($receivable->created_at)->addDays($receivable->credit_days);
                    return Carbon::now()->greaterThan($dueDate);
                });

            // CHANGED: When updating, the status will also revert to 'approved' instead of 'pending'.
            $invoiceStatus = $overdueReceivables->isEmpty() ? 'approved' : 'on_hold';

            // ... (rest of update logic remains the same)

            $invoiceData = [
                'customer_id' => $validated['customer_id'],
                'subtotal' => $subtotal,
                'tax' => $gstAmount,
                'gst' => $gstRate,
                'gst_type' => $gstType,
                'description' => $validated['description'],
                'purchase_number' => $validated['purchase_number'],
                'purchase_date' => $validated['purchase_date'],
                'total' => $total,
                'status' => $invoiceStatus,
            ];
            if ($gstType === 'CGST' || $gstType === 'SGST') {
                $invoiceData['cgst'] = $validated['cgst'];
                $invoiceData['sgst'] = $validated['sgst'];
                $invoiceData['igst'] = null;
            } else {
                $invoiceData['igst'] = $validated['igst'];
                $invoiceData['cgst'] = null;
                $invoiceData['sgst'] = null;
            }

            $invoice->update($invoiceData);
            $invoice->sales()->sync([$sale->id]);

            $saleTotalInvoiced = $subtotal + $gstAmount;
            Receivable::updateOrCreate(['sale_id' => $sale->id], ['customer_id' => $sale->customer_id, 'invoice_id' => $invoice->id, 'amount' => round($saleTotalInvoiced, 2), 'is_paid' => false, 'credit_days' => $creditDays]);

            DeliveryNote::where('sale_id', $sale->id)->update(['is_invoiced' => true]);

            DB::commit();

            $message = $invoiceStatus === 'on_hold' ? 'Invoice updated but placed on hold due to overdue receivables.' : 'Invoice updated successfully.';
            $messageType = $invoiceStatus === 'on_hold' ? 'warning' : 'success';

            return response()->json(['success' => true, 'message' => $message, 'messageType' => $messageType], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Invoice update failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while updating the invoice.'], 500);
        }
    }


    public function createFromDeliveryNote(DeliveryNote $deliveryNote)
    {
        if ($deliveryNote->is_invoiced) {
            $invoice = Invoice::whereHas('sales', function ($query) use ($deliveryNote) {
                $query->where('sales.id', $deliveryNote->sale_id);
            })->first();
            return $invoice
                ? redirect()->route('invoices.show', $invoice)->with('info', 'This delivery note is already part of Invoice ' . $invoice->invoice_number)
                : redirect()->back()->with('error', 'This delivery note has already been invoiced (invoice not found).');
        }
        $customers = Customer::where('id', $deliveryNote->sale->customer_id)->get();
        $sales = Sale::where('id', $deliveryNote->sale_id)->with(['customer', 'saleItems.product'])->get();
        return view('invoices.create', compact('customers', 'sales', 'deliveryNote'));
    }

    public function markAsPaid(Invoice $invoice)
    {
        if (!in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.show', $invoice)->with('info', 'Invoice is already marked as paid.');
        }
        if ($invoice->status !== 'approved') {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Only approved invoices can be marked as paid.');
        }
        \DB::transaction(function () use ($invoice) {
            $invoice->update(['status' => 'paid']);
            Receivable::where('invoice_id', $invoice->id)->update(['is_paid' => true]);
        });
        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice marked as paid successfully.');
    }

    // --- Edit Unlock Request Methods ---
    public function requestUnlock(Request $request, Invoice $invoice)
    {
        if (Auth::user()->role === 'superadmin') {
            return redirect()->back()->with('info', 'Superadmins can edit directly without requesting unlock.');
        }
        if (!in_array($invoice->status, ['approved', 'paid'])) {
            return redirect()->back()->with('error', 'Unlock requests are only applicable for approved or paid invoices.');
        }
        if ($invoice->edit_request_status === 'pending_unlock') {
            return redirect()->back()->with('info', 'An unlock request is already pending for this invoice.');
        }
        if ($invoice->edit_request_status === 'unlock_approved') {
            return redirect()->route('invoices.edit', $invoice->id)->with('info', 'Invoice is already unlocked for editing.');
        }
        $request->validate(['unlock_reason' => 'required|string|max:1000']);
        $invoice->update([
            'edit_request_status' => 'pending_unlock',
            'requested_by_id' => Auth::id(),
            'unlock_reason' => $request->unlock_reason,
            'unlock_decision_by_id' => null,
            'unlock_decision_at' => null,
            'unlock_decision_reason' => null,
        ]);
        $superadmins = User::where('role', 'superadmin')->get();
        if ($superadmins->isNotEmpty()) {
            Notification::send($superadmins, new InvoiceUnlockRequested($invoice, Auth::user()));
        }
        return redirect()->route('invoices.index')->with('success', 'Edit unlock request submitted for Invoice #' . $invoice->invoice_number . '. A superadmin will review it.');
    }

    public function listPendingUnlockRequests()
    {
        // Middleware 'superadmin' applied
        $pendingRequests = Invoice::with(['customer', 'requester'])
            ->where('edit_request_status', 'pending_unlock')
            ->latest('updated_at')
            ->paginate(15); // Added pagination
        return view('invoices.pending_unlock_requests', compact('pendingRequests'));
    }

    public function manageUnlockRequestForm(Request $request, Invoice $invoice)
    {
        // Middleware 'superadmin' applied
        if ($invoice->edit_request_status !== 'pending_unlock') {
            return redirect()->route('invoices.pending_unlock_requests')->with('info', 'This request is no longer pending a decision.');
        }
        if ($request->has('notification_id')) {
            $notification = Auth::user()->notifications()->where('id', $request->notification_id)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }
        $invoice->load(['customer', 'requester']);
        return view('invoices.manage_unlock_form', compact('invoice'));
    }

    public function decideUnlockRequest(Request $request, Invoice $invoice)
    {
        // Middleware 'superadmin' applied
        if ($invoice->edit_request_status !== 'pending_unlock') {
            return redirect()->route('invoices.pending_unlock_requests')->with('info', 'This request is no longer pending a decision.');
        }
        $request->validate([
            'decision' => 'required|in:approve,reject',
            'decision_reason' => 'nullable|string|max:1000',
        ]);
        if ($request->decision === 'reject' && empty($request->decision_reason)) {
            return back()->withInput()->withErrors(['decision_reason' => 'A reason is required when rejecting an unlock request.']);
        }
        $newUnlockStatus = $request->decision === 'approve' ? 'unlock_approved' : 'unlock_rejected';
        $invoice->update([
            'edit_request_status' => $newUnlockStatus,
            'unlock_decision_by_id' => Auth::id(),
            'unlock_decision_at' => now(),
            'unlock_decision_reason' => $request->decision_reason,
        ]);
        // Optionally: Notify the original requester about the decision
        // if ($invoice->requester) {
        //     Notification::send($invoice->requester, new InvoiceUnlockDecision($invoice, $newUnlockStatus, $request->decision_reason));
        // }
        $message = $newUnlockStatus === 'unlock_approved' ? 'Unlock request approved for Invoice #' . $invoice->invoice_number . '.' : 'Unlock request rejected for Invoice #' . $invoice->invoice_number . '.';
        return redirect()->route('invoices.pending_unlock_requests')->with('success', $message);
    }
}
