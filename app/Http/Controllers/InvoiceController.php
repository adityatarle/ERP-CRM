<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Http\Request;
use PDF;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['customer', 'sale'])->get();
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::all();
        // $sales = Sale::all();
        $sales = Sale::where('status', 'confirmed')->get();
        return view('invoices.create', compact('customers', 'sales'));
    }

    public function store(Request $request)
{
    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'sale_ids' => 'required|array|min:1',
        'sale_ids.*' => 'exists:sales,id',
        'issue_date' => 'required|date',
        'due_date' => 'required|date|after_or_equal:issue_date',
        'gst' => 'required|numeric|min:0',
    ]);

    // Fetch all selected sales with their saleItems
    $sales = Sale::with('saleItems')->whereIn('id', $request->sale_ids)->get();

    // Calculate subtotal by summing total_price from all sale_items
    $subtotal = $sales->flatMap(function ($sale) {
        return $sale->saleItems->pluck('total_price');
    })->sum();

    $gst = $subtotal * ($request->gst / 100);
    $total = $subtotal + $gst;

    // Create the invoice
    $invoice = Invoice::create([
        'invoice_number' => 'INV-' . uniqid(),
        'customer_id' => $request->customer_id,
        'issue_date' => $request->issue_date,
        'due_date' => $request->due_date,
        'subtotal' => $subtotal,
        'tax' => $gst,
        'gst' => $request->gst,
        'total' => $total,
        'status' => 'pending',
    ]);

    // Associate multiple sales with the invoice
    $invoice->sales()->sync($request->sale_ids);

    return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
}

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'sale.saleItems.product']);
        return view('invoices.show', compact('invoice'));
    }

    public function generatePDF(Invoice $invoice)
    {
        if ($invoice->status !== 'approved') {
            return redirect()->route('invoices.index')->with('error', 'Invoice must be approved before downloading.');
        }
    
        $invoice->load(['customer', 'sales.saleItems.product']);
        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function pendingInvoices()
    {
        if (Auth::user()->role !== 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        $invoices = Invoice::with(['customer', 'sale'])->where('status', 'pending')->get();
        return view('invoices.pending', compact('invoices'));
    }

    public function approve(Invoice $invoice)
    {
        if (Auth::user()->role !== 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        $invoice->update(['status' => 'approved']);
        return redirect()->route('invoices.pending')->with('success', 'Invoice approved successfully.');
    }

    public function edit($id)
    {
        $invoice = Invoice::findOrFail($id);
        $customers = Customer::all();
        $sales = Sale::all();
        return view('invoices.edit', compact('invoice','sales', 'customers'));
    }

    public function update(Request $request, $id)
{
    // ✅ Validate input
    $validatedData = $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'sale_id'     => 'required|exists:sales,id',
        'issue_date'  => 'required|date',
        'due_date'    => 'required|date|after_or_equal:issue_date',
    ]);

    // ✅ Find the invoice
    $invoice = Invoice::findOrFail($id);

    // ✅ Update the invoice
    $invoice->customer_id = $validatedData['customer_id'];
    $invoice->sale_id     = $validatedData['sale_id'];
    $invoice->issue_date  = $validatedData['issue_date'];
    $invoice->due_date    = $validatedData['due_date'];
    $invoice->save();

    // ✅ Redirect with a success message
    return redirect()->route('invoices.pending')->with('success', 'Invoice updated successfully!');
}
}