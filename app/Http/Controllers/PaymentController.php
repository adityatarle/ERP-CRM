<?php

namespace App\Http\Controllers;

use App\Models\Payable;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseEntry;
use App\Models\Party;
use App\Models\Sale;
use App\Models\Receivable;
use App\Models\Customer;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Exports\PayablesExport;
use App\Exports\ReceivablesExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'party_search' => 'nullable|string|max:255',
            'invoice_search' => 'nullable|string|max:255',
            'invoice_date_from' => 'nullable|date',
            'invoice_date_to' => 'nullable|date|after_or_equal:invoice_date_from',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $party_search = $request->input('party_search');
        $invoice_search = $request->input('invoice_search');
        $invoice_date_from = $request->input('invoice_date_from');
        $invoice_date_to = $request->input('invoice_date_to');

        // Start building the query
        $query = Payable::with(['purchaseEntry', 'party'])->where('is_paid', false);

        // Apply party name filter if provided
        if ($party_search) {
            $query->whereHas('party', function ($q) use ($party_search) {
                $q->where('name', 'like', '%' . $party_search . '%');
            });
        }

        // Apply invoice number filter if provided
        if ($invoice_search) {
            $query->where('invoice_number', 'like', '%' . $invoice_search . '%');
        }

        // Apply invoice date range filter if both dates are provided
        if ($invoice_date_from && $invoice_date_to) {
            $query->whereBetween('invoice_date', [$invoice_date_from, $invoice_date_to]);
        }

        // Apply date range filter if both dates are provided
        if ($startDate && $endDate) {
            $query->whereHas('purchaseEntry', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('purchase_date', [$startDate, $endDate]);
            });
        }

        $payables = $query->latest('created_at')->paginate(15);

        // This is needed for the "Record Payment" modal dropdown
        $parties = Party::orderBy('name')->get();

        return view('payments.payables.index', compact('payables', 'parties', 'startDate', 'endDate', 'party_search', 'invoice_search', 'invoice_date_from', 'invoice_date_to'));
    }

    public function exportPayables(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $party_search = $request->input('party_search');
        $invoice_search = $request->input('invoice_search');
        $invoice_date_from = $request->input('invoice_date_from');
        $invoice_date_to = $request->input('invoice_date_to');

        // Validate that at least one filter is provided
        if (!$startDate && !$endDate && !$party_search && !$invoice_search && !$invoice_date_from && !$invoice_date_to) {
            return redirect()->route('payables')->withErrors(['filter' => 'Please provide at least one filter to export.']);
        }

        // Validate date range if provided
        if ($startDate && $endDate && $endDate < $startDate) {
            return redirect()->route('payables')->withErrors(['end_date' => 'End date must be after start date.']);
        }

        // Validate invoice date range if provided
        if ($invoice_date_from && $invoice_date_to && $invoice_date_to < $invoice_date_from) {
            return redirect()->route('payables')->withErrors(['invoice_date_to' => 'Invoice end date must be after start date.']);
        }

        Log::info('Exporting payables with filters', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'party_search' => $party_search,
            'invoice_search' => $invoice_search,
            'invoice_date_from' => $invoice_date_from,
            'invoice_date_to' => $invoice_date_to,
        ]);

        return Excel::download(new PayablesExport($startDate, $endDate, $party_search, $invoice_search, $invoice_date_from, $invoice_date_to), 'payables_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function create()
    {
        $unpaidPurchaseEntries = PurchaseEntry::whereIn('id', Payable::where('is_paid', false)->pluck('purchase_entry_id'))->get();
        $parties = Party::orderBy('name')->get();
        return view('payments.payables.create', compact('unpaidPurchaseEntries', 'parties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'party_id' => 'required|exists:parties,id',
            'purchase_entry_ids' => 'required|json',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
        ]);

        $purchaseEntries = json_decode($request->purchase_entry_ids, true);
        $totalEnteredAmount = (float) $request->amount;

        // Validate JSON decoding
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($purchaseEntries)) {
            return redirect()->back()->withErrors(['purchase_entry_ids' => 'The purchase entry IDs field must be a valid JSON string.']);
        }

        // Validate purchase entries
        if (empty($purchaseEntries)) {
            return redirect()->back()->withErrors(['purchase_entry_ids' => 'No purchase entries selected for payment.']);
        }

        // Verify total allocated amount matches entered amount
        $totalAllocated = array_sum(array_column($purchaseEntries, 'amount'));
        if (abs($totalAllocated - $totalEnteredAmount) > 0.01) {
            return redirect()->back()->withErrors(['amount' => 'The total allocated amount does not match the entered amount.']);
        }

        // Validate purchase entry IDs exist and belong to the party
        $validPurchaseEntryIds = Payable::where('party_id', $request->party_id)
            ->where('is_paid', false)
            ->pluck('purchase_entry_id')
            ->toArray();

        foreach ($purchaseEntries as $entry) {
            if (!isset($entry['id']) || !isset($entry['amount']) || !in_array($entry['id'], $validPurchaseEntryIds)) {
                return redirect()->back()->withErrors(['purchase_entry_ids' => 'Invalid purchase entry selected.']);
            }
            if ((float) $entry['amount'] <= 0) {
                return redirect()->back()->withErrors(['purchase_entry_ids' => 'Payment amount for purchase entry ID ' . $entry['id'] . ' must be greater than 0.']);
            }
        }

        DB::transaction(function () use ($request, $purchaseEntries, $totalEnteredAmount) {
            foreach ($purchaseEntries as $entry) {
                $payable = Payable::where('purchase_entry_id', $entry['id'])
                    ->where('party_id', $request->party_id)
                    ->where('is_paid', false)
                    ->firstOrFail();

                $paymentAmount = (float) $entry['amount'];
                if ($paymentAmount > $payable->amount) {
                    throw new \Exception("Payment amount exceeds outstanding for purchase entry ID: {$entry['id']}");
                }

                // Record the payment
                Payment::create([
                    'purchase_entry_id' => $entry['id'],
                    'party_id' => $request->party_id,
                    'sale_id' => null,
                    'customer_id' => null,
                    'amount' => $paymentAmount,
                    'payment_date' => $request->payment_date,
                    'notes' => $request->notes,
                    'bank_name' => $request->bank_name,
                    'type' => 'payable',
                ]);

                // Update payable amount and invoice information
                $payable->amount -= $paymentAmount;
                if ($payable->amount <= 0.01) {
                    $payable->is_paid = true;
                }

                // Update invoice information if provided
                if ($request->invoice_number) {
                    $payable->invoice_number = $request->invoice_number;
                }
                if ($request->invoice_date) {
                    $payable->invoice_date = $request->invoice_date;
                }

                $payable->save();
            }
        });

        return redirect()->route('payables')->with('success', 'Payment recorded successfully.');
    }

    public function paymentsList()
    {
        // Get all payable payments with relationships
        $payments = Payment::with(['purchaseEntry', 'party', 'payable'])
            ->where('type', 'payable')
            ->orderBy('payment_date', 'desc')
            ->get();

        // Group payments by party for better organization
        $paymentsByParty = $payments->groupBy('party_id');
        
        // Calculate summary statistics
        $summary = [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'total_parties' => $paymentsByParty->count(),
            'date_range' => [
                'earliest' => $payments->min('payment_date'),
                'latest' => $payments->max('payment_date')
            ]
        ];

        return view('payments.payables.list', compact('payments', 'paymentsByParty', 'summary'));
    }



 public function receivables(Request $request)
{
    $validated = $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'customer_search' => 'nullable|string|max:255',
    ]);

    // THE FIX: Eager load the customer relationship
    $query = Invoice::with('customer')
        ->whereIn('payment_status', ['unpaid', 'partially_paid']);

    if (!empty($validated['customer_search'])) {
        $query->whereHas('customer', function ($q) use ($validated) {
            $q->where('name', 'like', '%' . $validated['customer_search'] . '%');
        });
    }

    if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
        // Filter by 'issue_date' or 'created_at' if issue_date is not always present
        $query->whereBetween('issue_date', [$validated['start_date'], $validated['end_date']]);
    }

    $invoices = $query->latest('issue_date')->paginate(20);
    $customers = Customer::orderBy('name')->get();
    
    // No changes needed here, the 'with('customer')' and the main query on Invoice
    // already make all invoice columns (like due_date) available.
    // The previous code was correct.

    return view('payments.receivables.index', [
        'invoices' => $invoices,
        'customers' => $customers,
        'customer_search' => $validated['customer_search'] ?? '',
        'startDate' => $validated['start_date'] ?? '',
        'endDate' => $validated['end_date'] ?? '',
    ]);
}

// ...



    public function exportReceivables(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $customer_search = $request->input('customer_search');

        // Validation for date range if provided
        if ($startDate && $endDate && $endDate < $startDate) {
            return redirect()->route('receivables')->withErrors(['end_date' => 'End date must be after start date.']);
        }

        Log::info('Exporting receivables with date range and customer search', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'customer_search' => $customer_search,
        ]);

        return Excel::download(new ReceivablesExport($startDate, $endDate, $customer_search), 'receivables_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function createReceivable()
    {
        $unpaidSales = Sale::whereIn('id', Receivable::where('is_paid', false)->pluck('sale_id'))->get();
        $customers = Customer::orderBy('name')->get();
        return view('payments.receivables.create', compact('unpaidSales', 'customers'));
    }

   public function storeReceivable(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'customer_id' => 'required|exists:customers,id',
            'invoice_ids' => 'required|json', // Changed from receivable_ids
            'tds_amount' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
        ]);

        $invoiceEntries = json_decode($validated['invoice_ids'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['invoice_ids' => 'Invalid invoice data format.']);
        }

        $totalEntered = (float)$validated['amount'];
        $tdsAmount = (float)($validated['tds_amount'] ?? 0);
        $totalAllocated = array_sum(array_column($invoiceEntries, 'amount'));

        // Verify total allocated amount (plus TDS) matches entered amount
        if (abs($totalAllocated + $tdsAmount - $totalEntered) > 0.01) {
            return back()->withErrors(['amount' => 'The total allocated amount plus TDS (₹' . ($totalAllocated + $tdsAmount) . ') does not match the entered amount (₹' . $totalEntered . ').']);
        }
        
        DB::transaction(function () use ($validated, $invoiceEntries) {
            $tdsToApply = (float)($validated['tds_amount'] ?? 0);
            
            foreach ($invoiceEntries as $entry) {
                $invoice = Invoice::where('id', $entry['id'])
                    ->where('customer_id', $validated['customer_id'])
                    ->whereIn('payment_status', ['unpaid', 'partially_paid'])
                    ->firstOrFail();

                $paymentAmount = (float)$entry['amount'];

                // Create the payment record, linking it directly to the invoice
                Payment::create([
                    'invoice_id'    => $invoice->id,
                    'customer_id'   => $validated['customer_id'],
                    'amount'        => $paymentAmount,
                    'tds_amount'    => $tdsToApply,
                    'payment_date'  => $validated['payment_date'],
                    'notes'         => $validated['notes'] ?? null,
                    'bank_name'     => $validated['bank_name'] ?? null,
                    'type'          => 'receivable',
                ]);

                // Update the invoice itself
                $invoice->amount_paid += $paymentAmount + $tdsToApply;
                
                // Update invoice payment status
                if ($invoice->amount_due <= 0.01) { // Use amount_due accessor
                    $invoice->payment_status = 'paid';
                } else {
                    $invoice->payment_status = 'partially_paid';
                }
                
                $invoice->save();
                
                // Important: Ensure TDS is only applied once for the entire payment
                $tdsToApply = 0; 
            }
        });

        return redirect()->route('receivables')->with('success', 'Payment recorded successfully.');
    }

   public function receivablesPaymentsList(Request $request)
{
    // --- Step 1: Get user input with defaults ---
    $tdsFilter = $request->input('tds_filter', 'all');
    $sortBy = $request->input('sort_by', 'payment_date');
    $sortDir = $request->input('sort_dir', 'desc');

    // --- Step 2: Define allowed columns for sorting ---
    $allowedSortColumns = [
        'invoice_number' => 'invoices.invoice_number',
        'customer_name'  => 'customers.name',
        'amount'         => 'payments.amount',
        'tds_amount'     => 'payments.tds_amount',
        'payment_date'   => 'payments.payment_date',
        'due_date'       => 'invoices.due_date', // <-- ADD THIS
        'bank_name'      => 'payments.bank_name',
        'notes'          => 'payments.notes',
    ];

    $sortColumn = $allowedSortColumns[$sortBy] ?? 'payments.payment_date';
    $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? $sortDir : 'desc';

    // --- Step 3: Build the Eloquent query ---
    $query = Payment::query()
        ->where('type', 'receivable')
        ->with(['invoice', 'customer']); // Eager load relationships

    // --- Step 4: Apply Joins ONLY if sorting by a related table's column ---
    if (in_array($sortBy, ['invoice_number', 'customer_name', 'due_date'])) { // <-- ADD due_date
        $query->leftJoin('invoices', 'payments.invoice_id', '=', 'invoices.id')
              ->leftJoin('customers', 'payments.customer_id', '=', 'customers.id')
              ->select('payments.*', 'invoices.due_date', 'invoices.issue_date'); // <-- ADD issue_date and due_date to select
    }

    // --- Step 5: Apply TDS filter ---
    $query->when($tdsFilter === 'with_tds', function ($q) {
        $q->where('payments.tds_amount', '>', 0);
    })->when($tdsFilter === 'without_tds', function ($q) {
        $q->where(function ($subQuery) {
            $subQuery->whereNull('payments.tds_amount')->orWhere('payments.tds_amount', '=', 0);
        });
    });

    // --- Step 6: Apply sorting ---
    $query->orderBy($sortColumn, $sortDir);

    $payments = $query->get();

    // --- ENHANCED: Group payments by customer for better organization ---
    $paymentsByCustomer = $payments->groupBy('customer_id');
    
    // --- ENHANCED: Calculate summary statistics ---
    $summary = [
        'total_payments' => $payments->count(),
        'total_amount' => $payments->sum('amount'),
        'total_tds' => $payments->sum('tds_amount'),
        'total_customers' => $paymentsByCustomer->count(),
        'date_range' => [
            'earliest' => $payments->min('payment_date'),
            'latest' => $payments->max('payment_date')
        ]
    ];

    // --- Step 7: Pass enhanced data to the view ---
    return view('payments.receivables.list', compact('payments', 'paymentsByCustomer', 'summary', 'tdsFilter', 'sortBy', 'sortDir'));
}

// ...




    // ... other controller methods ...

  

    public function getPurchaseEntriesByParty(Request $request)
    {
        $partyId = $request->query('party_id');
        if (!$partyId) {
            return response()->json([], 400);
        }

        $payables = Payable::where('party_id', $partyId)
            ->where('is_paid', false)
            ->with(['purchaseEntry'])
            ->get();

        $entries = $payables->map(function ($payable) {
            return [
                'id' => $payable->purchase_entry_id,
                'purchase_number' => $payable->purchaseEntry->purchase_number ?? null,
                'purchase_date' => $payable->purchaseEntry->purchase_date ?? null,
                'amount' => (float) $payable->amount, // Ensure amount is a float
            ];
        })->sortBy('purchase_date')->values()->toArray();

        return response()->json($entries);
    }

     public function getInvoicesByCustomer(Request $request)
    {
        $validated = $request->validate(['customer_id' => 'required|exists:customers,id']);
        
        $unpaidInvoices = Invoice::where('customer_id', $validated['customer_id'])
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->oldest('issue_date')
            ->get(['id', 'invoice_number', 'total', 'amount_paid']); // Select only needed columns
            
        $data = $unpaidInvoices->map(function ($invoice) {
            return [
                'id'       => $invoice->id,
                'ref_no'   => $invoice->invoice_number,
                'amount'   => $invoice->amount_due, // Use the calculated amount_due attribute
                'type'     => 'Invoice'
            ];
        });
        
        return response()->json($data);
    }

}
