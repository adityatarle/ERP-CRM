<?php

namespace App\Http\Controllers;

use App\Models\Payable;
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
        $payments = Payment::with('purchaseEntry', 'party')->where('type', 'payable')->orderBy('payment_date', 'desc')->get();
        return view('payments.payables.list', compact('payments'));
    }

    

     public function receivables(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $customer_search = $request->input('customer_search');

        $query = Receivable::with('sale', 'customer')
            ->where('is_paid', false);

        // Apply customer name filter if provided
        if ($customer_search) {
            $query->whereHas('customer', function ($q) use ($customer_search) {
                $q->where('name', 'like', '%' . $customer_search . '%');
            });
        }

        // Apply date range filter if provided
        if ($startDate && $endDate) {
            if ($endDate < $startDate) {
                return redirect()->back()->withErrors(['end_date' => 'End date must be after start date.']);
            }

            Log::info('Filtering receivables with date range and customer search', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'customer_search' => $customer_search,
            ]);

            $query->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }

        $receivables = $query->get();

        Log::info('Receivables retrieved', [
            'count' => $receivables->count(),
            'receivables' => $receivables->map(function ($receivable) {
                return [
                    'id' => $receivable->id,
                    'sale_id' => $receivable->sale_id,
                    'created_at' => $receivable->created_at,
                    'credit_days' => $receivable->credit_days,
                ];
            })->toArray(),
        ]);

        $unpaidSales = Sale::whereIn('id', Receivable::where('is_paid', false)->pluck('sale_id'))->get();
        $customers = Customer::orderBy('name')->get();

        return view('payments.receivables.index', compact('receivables', 'unpaidSales', 'customers', 'startDate', 'endDate', 'customer_search'));
    }

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
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'customer_id' => 'required|exists:customers,id',
            'sale_ids' => 'required|json',
            'tds_amount' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
        ]);

        $saleEntries = json_decode($request->sale_ids, true);
        $totalEnteredAmount = (float) $request->amount;
        $tdsAmount = (float) ($request->tds_amount ?? 0);

        // Validate JSON decoding
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($saleEntries)) {
            return redirect()->back()->withErrors(['sale_ids' => 'The sale IDs field must be a valid JSON string.']);
        }

        // Validate sale entries
        if (empty($saleEntries)) {
            return redirect()->back()->withErrors(['sale_ids' => 'No sale entries selected for payment.']);
        }

        // Verify total allocated amount (plus TDS) matches entered amount
        $totalAllocated = array_sum(array_column($saleEntries, 'amount'));
        if (abs($totalAllocated + $tdsAmount - $totalEnteredAmount) > 0.01) {
            return redirect()->back()->withErrors(['amount' => 'The total allocated amount plus TDS does not match the entered amount.']);
        }

        // Validate sale IDs exist and belong to the customer
        $validSaleIds = Receivable::where('customer_id', $request->customer_id)
            ->where('is_paid', false)
            ->pluck('sale_id')
            ->toArray();

        foreach ($saleEntries as $entry) {
            if (!isset($entry['id']) || !isset($entry['amount']) || !in_array($entry['id'], $validSaleIds)) {
                return redirect()->back()->withErrors(['sale_ids' => 'Invalid sale entry selected.']);
            }
            if ((float) $entry['amount'] <= 0) {
                return redirect()->back()->withErrors(['sale_ids' => 'Payment amount for sale ID ' . $entry['id'] . ' must be greater than 0.']);
            }
        }

        DB::transaction(function () use ($request, $saleEntries, $tdsAmount) {
            foreach ($saleEntries as $entry) {
                $receivable = Receivable::where('sale_id', $entry['id'])
                    ->where('customer_id', $request->customer_id)
                    ->where('is_paid', false)
                    ->firstOrFail();

                $paymentAmount = (float) $entry['amount'];
                if ($paymentAmount > $receivable->amount) {
                    throw new \Exception("Payment amount exceeds outstanding for sale ID: {$entry['id']}");
                }

                // Record the payment
                Payment::create([
                    'purchase_entry_id' => null,
                    'party_id' => null,
                    'sale_id' => $entry['id'],
                    'customer_id' => $request->customer_id,
                    'amount' => $paymentAmount,
                    'tds_amount' => $tdsAmount > 0 ? $tdsAmount : 0,
                    'payment_date' => $request->payment_date,
                    'notes' => $request->notes,
                    'bank_name' => $request->bank_name,
                    'type' => 'receivable',
                ]);

                // Update receivable amount
                $receivable->amount -= ($paymentAmount + ($tdsAmount > 0 ? $tdsAmount : 0));
                if ($receivable->amount <= 0.01) {
                    $receivable->is_paid = true;
                    $sale = $receivable->sale;
                    $sale->status = 'confirmed';
                    $sale->save();
                }
                $receivable->save();
            }
        });

        return redirect()->route('receivables')->with('success', 'Payment recorded successfully.');
    }

    public function receivablesPaymentsList(Request $request)
    {
        $tdsFilter = $request->input('tds_filter', 'all');
        $sortBy = $request->input('sort_by', 'payment_date');
        $sortDir = $request->input('sort_dir', 'desc');

        // Validate sort_by to prevent SQL injection
        $allowedSortColumns = [
            'sale_ref_no' => 'sales.ref_no',
            'customer_name' => 'customers.name',
            'amount' => 'payments.amount',
            'tds_amount' => 'payments.tds_amount',
            'payment_date' => 'payments.payment_date',
            'bank_name' => 'payments.bank_name',
            'notes' => 'payments.notes',
        ];

        // Default to payment_date if sort_by is invalid
        $sortColumn = array_key_exists($sortBy, $allowedSortColumns) ? $allowedSortColumns[$sortBy] : 'payments.payment_date';
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? $sortDir : 'desc';

        $query = Payment::with(['sale', 'customer'])
            ->where('type', 'receivable')
            ->leftJoin('sales', 'payments.sale_id', '=', 'sales.id')
            ->leftJoin('customers', 'payments.customer_id', '=', 'customers.id')
            ->select('payments.*');

        // Apply TDS filter
        $query->when($tdsFilter === 'with_tds', function ($q) {
            $q->where('tds_amount', '>', 0);
        })->when($tdsFilter === 'without_tds', function ($q) {
            $q->whereNull('tds_amount')->orWhere('tds_amount', '=', 0);
        });

        // Apply sorting
        $query->orderByRaw("$sortColumn $sortDir");

        $payments = $query->get();

        return view('payments.receivables.list', compact('payments', 'tdsFilter', 'sortBy', 'sortDir'));
    }

    public function getSalesByCustomer(Request $request)
    {
        $customerId = $request->query('customer_id');

        if (!$customerId) {
            return response()->json([]);
        }

        // Fetch unpaid sales for the given customer
        $unpaidSales = Sale::whereIn('id', Receivable::where('is_paid', false)
            ->where('customer_id', $customerId)
            ->pluck('sale_id'))
            ->get();

        // Map the sales to include the amount to be paid
        $salesWithAmount = $unpaidSales->map(function ($sale) {
            $receivable = Receivable::where('sale_id', $sale->id)
                ->where('is_paid', false)
                ->first();
            return [
                'id' => $sale->id,
                'ref_no' => $sale->ref_no,
                'amount' => $receivable ? $receivable->amount : 0,
            ];
        })->toArray();

        return response()->json($salesWithAmount);
    }

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
}
