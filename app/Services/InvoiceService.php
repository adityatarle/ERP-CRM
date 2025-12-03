<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Exception;

class InvoiceService extends BaseService
{
    /**
     * Create a new invoice with items
     */
    public function createInvoice(array $data): Invoice
    {
        return $this->executeInTransaction(function () use ($data) {
            $this->validateRequired($data, ['customer_id', 'products', 'gst_type']);
            
            // Process invoice data
            $invoiceData = $this->processInvoiceData($data);
            
            // Create invoice
            $invoice = Invoice::create([
                'customer_id' => $data['customer_id'],
                'ref_no' => $data['ref_no'] ?? null,
                'total_price' => $invoiceData['total_price'],
                'gst_type' => $data['gst_type'],
                'cgst' => $data['cgst'] ?? null,
                'sgst' => $data['sgst'] ?? null,
                'igst' => $data['igst'] ?? null,
                'description' => $data['description'] ?? null,
                'purchase_number' => $data['purchase_number'],
                'purchase_date' => $data['purchase_date'],
                'contact_person' => $data['contact_person'] ?? null,
                'status' => $data['status'] ?? 'pending'
            ]);

            // Create invoice items
            $this->createInvoiceItems($invoice, $invoiceData['items']);
            
            $this->logOperation('invoice_created', [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'total_price' => $invoice->total_price,
                'gst_type' => $invoice->gst_type
            ]);

            return $invoice->load(['customer', 'invoiceItems.product']);
        }, 'create_invoice');
    }

    /**
     * Process invoice data and calculate totals
     */
    private function processInvoiceData(array $data): array
    {
        $totalPrice = 0;
        $items = [];

        foreach ($data['products'] as $index => $productData) {
            $product = Product::findOrFail($productData['product_id']);
            
            // Validate sale price
            if (empty($productData['sale_price']) || $productData['sale_price'] <= 0) {
                throw new Exception("Product " . ($index + 1) . " must have a valid sale price greater than 0.");
            }

            // Calculate item total
            $itemTotal = $this->calculateTotalWithDiscount(
                $productData['sale_price'],
                $productData['quantity'],
                $productData['discount'] ?? 0
            );

            $items[] = [
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
                'unit_price' => $productData['sale_price'],
                'discount' => $productData['discount'] ?? 0,
                'total_price' => $itemTotal,
                'itemcode' => $productData['itemcode'] ?? null,
                'secondary_itemcode' => $productData['secondary_itemcode'] ?? null,
            ];

            $totalPrice += $itemTotal;
        }

        return [
            'total_price' => $totalPrice,
            'items' => $items
        ];
    }

    /**
     * Create invoice items
     */
    private function createInvoiceItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $itemData) {
            $invoiceItem = $invoice->invoiceItems()->create($itemData);
            
            $this->logOperation('invoice_item_created', [
                'invoice_item_id' => $invoiceItem->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity']
            ]);
        }
    }

    /**
     * Update invoice status
     */
    public function updateInvoiceStatus(Invoice $invoice, string $status): Invoice
    {
        return $this->executeInTransaction(function () use ($invoice, $status) {
            $oldStatus = $invoice->status;
            $invoice->update(['status' => $status]);
            
            $this->logOperation('invoice_status_updated', [
                'invoice_id' => $invoice->id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);

            return $invoice;
        }, 'update_invoice_status');
    }

    /**
     * Get invoices with filters
     */
    public function getInvoices(array $filters = []): array
    {
        $query = Invoice::with(['customer', 'invoiceItems.product']);

        // Apply filters
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['gst_type'])) {
            $query->where('gst_type', $filters['gst_type']);
        }

        if (isset($filters['date_range'])) {
            $this->applyDateFilters($query, $filters['date_range'], 'purchase_date');
        }

        if (isset($filters['search'])) {
            $this->buildSearchQuery($query, $filters['search'], ['ref_no', 'purchase_number']);
        }

        // Apply sorting
        $this->applySorting($query, $filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc');

        // Get results
        $invoices = $this->paginateResults($query, $filters['per_page'] ?? 15);
        
        // Get summary stats
        $summaryQuery = Invoice::query();
        if (isset($filters['date_range'])) {
            $this->applyDateFilters($summaryQuery, $filters['date_range'], 'purchase_date');
        }
        $summary = $this->getSummaryStats($summaryQuery, 'total_price');

        return [
            'invoices' => $invoices,
            'summary' => $summary
        ];
    }

    /**
     * Get invoice statistics
     */
    public function getInvoiceStatistics(array $filters = []): array
    {
        $query = Invoice::query();

        if (isset($filters['date_range'])) {
            $this->applyDateFilters($query, $filters['date_range'], 'purchase_date');
        }

        $totalInvoices = $query->count();
        $totalValue = $query->sum('total_price');
        $averageInvoice = $totalInvoices > 0 ? $totalValue / $totalInvoices : 0;

        // Invoices by GST type
        $invoicesByGstType = $query->groupBy('gst_type')
            ->selectRaw('gst_type, count(*) as count, sum(total_price) as total')
            ->get()
            ->keyBy('gst_type');

        // Invoices by status
        $invoicesByStatus = $query->groupBy('status')
            ->selectRaw('status, count(*) as count, sum(total_price) as total')
            ->get()
            ->keyBy('status');

        // Top customers by invoice value
        $topCustomers = Invoice::with('customer')
            ->selectRaw('customer_id, count(*) as invoice_count, sum(total_price) as total_value')
            ->groupBy('customer_id')
            ->orderBy('total_value', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_invoices' => $totalInvoices,
            'total_value' => $totalValue,
            'average_invoice' => $averageInvoice,
            'invoices_by_gst_type' => $invoicesByGstType,
            'invoices_by_status' => $invoicesByStatus,
            'top_customers' => $topCustomers
        ];
    }

    /**
     * Calculate GST breakdown for invoice
     */
    public function calculateGSTBreakdown(Invoice $invoice): array
    {
        $subtotal = $invoice->total_price;
        $gstBreakdown = [];

        if ($invoice->gst_type === 'CGST') {
            $cgstAmount = ($subtotal * $invoice->cgst) / 100;
            $sgstAmount = ($subtotal * $invoice->sgst) / 100;
            
            $gstBreakdown = [
                'subtotal' => $subtotal,
                'cgst_rate' => $invoice->cgst,
                'cgst_amount' => $cgstAmount,
                'sgst_rate' => $invoice->sgst,
                'sgst_amount' => $sgstAmount,
                'total_gst' => $cgstAmount + $sgstAmount,
                'grand_total' => $subtotal + $cgstAmount + $sgstAmount
            ];
        } else {
            $igstAmount = ($subtotal * $invoice->igst) / 100;
            
            $gstBreakdown = [
                'subtotal' => $subtotal,
                'igst_rate' => $invoice->igst,
                'igst_amount' => $igstAmount,
                'total_gst' => $igstAmount,
                'grand_total' => $subtotal + $igstAmount
            ];
        }

        return $gstBreakdown;
    }

    /**
     * Generate invoice PDF data
     */
    public function generateInvoicePDFData(Invoice $invoice): array
    {
        $invoice->load(['customer', 'invoiceItems.product']);
        $gstBreakdown = $this->calculateGSTBreakdown($invoice);
        
        return [
            'invoice' => $invoice,
            'gst_breakdown' => $gstBreakdown,
            'generated_at' => now(),
            'company_info' => $this->getCompanyInfo()
        ];
    }

    /**
     * Get company information for invoices
     */
    private function getCompanyInfo(): array
    {
        return [
            'name' => config('app.company_name', 'Your Company Name'),
            'address' => config('app.company_address', 'Your Company Address'),
            'gst_number' => config('app.company_gst', 'Your GST Number'),
            'phone' => config('app.company_phone', 'Your Phone Number'),
            'email' => config('app.company_email', 'Your Email Address')
        ];
    }
}
