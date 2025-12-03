<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Services\InvoiceService;
use App\Http\Requests\CreateInvoiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceControllerRefactored extends BaseController
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        return $this->handleAjaxRequest($request, function () use ($request) {
            $filters = $this->getFilterParams($request, [
                'customer_id', 'status', 'gst_type', 'search', 'start_date', 'end_date',
                'sort_by', 'sort_direction', 'per_page'
            ]);

            // Validate date range if provided
            if ($request->has('start_date') || $request->has('end_date')) {
                $dateRange = $this->validateDateRange($request);
                $filters['date_range'] = $dateRange;
            }

            $result = $this->invoiceService->getInvoices($filters);
            
            if ($request->ajax() || $request->wantsJson()) {
                return $result;
            }

            return view('invoices.index', $result);
        });
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['category', 'subcategory'])->orderBy('name')->get();
        
        return view('invoices.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(CreateInvoiceRequest $request)
    {
        return $this->handleAjaxRequest($request, function () use ($request) {
            $invoice = $this->invoiceService->createInvoice($request->validated());
            
            $this->logOperation('invoice_created', [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'total_price' => $invoice->total_price,
                'gst_type' => $invoice->gst_type
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return $this->successResponse($invoice, 'Invoice created successfully', 201);
            }

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice created successfully.');
        });
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'invoiceItems.product']);
        $gstBreakdown = $this->invoiceService->calculateGSTBreakdown($invoice);
        
        return view('invoices.show', compact('invoice', 'gstBreakdown'));
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load(['customer', 'invoiceItems.product']);
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['category', 'subcategory'])->orderBy('name')->get();
        
        return view('invoices.edit', compact('invoice', 'customers', 'products'));
    }

    /**
     * Update the specified invoice
     */
    public function update(CreateInvoiceRequest $request, Invoice $invoice)
    {
        return $this->handleAjaxRequest($request, function () use ($request, $invoice) {
            // For now, we'll implement a simple status update
            // Full update would require more complex logic
            if ($request->has('status')) {
                $invoice = $this->invoiceService->updateInvoiceStatus($invoice, $request->status);
            }
            
            $this->logOperation('invoice_updated', [
                'invoice_id' => $invoice->id,
                'status' => $invoice->status
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return $this->successResponse($invoice, 'Invoice updated successfully');
            }

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice updated successfully.');
        });
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Request $request, Invoice $invoice)
    {
        return $this->handleAjaxRequest($request, function () use ($invoice) {
            // For invoices, we typically don't delete but mark as cancelled
            $invoice = $this->invoiceService->updateInvoiceStatus($invoice, 'cancelled');
            
            $this->logOperation('invoice_cancelled', [
                'invoice_id' => $invoice->id
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return $this->successResponse(null, 'Invoice cancelled successfully');
            }

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice cancelled successfully.');
        });
    }

    /**
     * Generate PDF for invoice
     */
    public function pdf(Invoice $invoice)
    {
        try {
            $pdfData = $this->invoiceService->generateInvoicePDFData($invoice);
            
            $pdf = Pdf::loadView('invoices.pdf', $pdfData);
            
            $this->logOperation('invoice_pdf_generated', [
                'invoice_id' => $invoice->id
            ]);

            return $pdf->download("invoice-{$invoice->id}.pdf");
        } catch (\Exception $e) {
            $this->logOperation('invoice_pdf_generation_failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ], 'error');

            return redirect()->back()
                ->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Get invoice statistics
     */
    public function statistics(Request $request)
    {
        return $this->handleAjaxRequest($request, function () use ($request) {
            $filters = [];
            
            if ($request->has('start_date') || $request->has('end_date')) {
                $filters['date_range'] = $this->validateDateRange($request);
            }

            $statistics = $this->invoiceService->getInvoiceStatistics($filters);
            
            return $this->successResponse($statistics, 'Invoice statistics retrieved successfully');
        });
    }

    /**
     * Get invoices for a specific customer
     */
    public function getCustomerInvoices(Request $request, Customer $customer)
    {
        return $this->handleAjaxRequest($request, function () use ($customer) {
            $invoices = $this->invoiceService->getInvoices(['customer_id' => $customer->id]);
            
            return $this->successResponse($invoices, 'Customer invoices retrieved successfully');
        });
    }

    /**
     * Update invoice status
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        return $this->handleAjaxRequest($request, function () use ($request, $invoice) {
            $request->validate([
                'status' => 'required|in:pending,completed,cancelled'
            ]);

            $invoice = $this->invoiceService->updateInvoiceStatus($invoice, $request->status);
            
            $this->logOperation('invoice_status_updated', [
                'invoice_id' => $invoice->id,
                'new_status' => $invoice->status
            ]);

            return $this->successResponse($invoice, 'Invoice status updated successfully');
        });
    }

    /**
     * Get GST breakdown for invoice
     */
    public function getGSTBreakdown(Invoice $invoice)
    {
        return $this->handleAjaxRequest(request(), function () use ($invoice) {
            $gstBreakdown = $this->invoiceService->calculateGSTBreakdown($invoice);
            
            return $this->successResponse($gstBreakdown, 'GST breakdown calculated successfully');
        });
    }

    /**
     * Print invoice
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'invoiceItems.product']);
        $gstBreakdown = $this->invoiceService->calculateGSTBreakdown($invoice);
        
        $this->logOperation('invoice_printed', [
            'invoice_id' => $invoice->id
        ]);

        return view('invoices.print', compact('invoice', 'gstBreakdown'));
    }

    /**
     * Duplicate invoice
     */
    public function duplicate(Invoice $invoice)
    {
        $invoice->load(['customer', 'invoiceItems.product']);
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['category', 'subcategory'])->orderBy('name')->get();
        
        // Clear the ID and other fields that shouldn't be duplicated
        $invoice->id = null;
        $invoice->created_at = null;
        $invoice->updated_at = null;
        $invoice->status = 'pending';
        
        $this->logOperation('invoice_duplicated', [
            'original_invoice_id' => $invoice->id
        ]);

        return view('invoices.create', compact('invoice', 'customers', 'products'));
    }
}
