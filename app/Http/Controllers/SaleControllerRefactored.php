<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Services\SaleService;
use App\Http\Requests\CreateSaleRequest;
use App\Imports\SalesDataImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SaleControllerRefactored extends BaseController
{
    protected SaleService $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    /**
     * Display a listing of sales
     */
    public function index(Request $request)
    {
        return $this->handleAjaxRequest($request, function () use ($request) {
            $filters = $this->getFilterParams($request, [
                'customer_id', 'status', 'search', 'start_date', 'end_date', 
                'sort_by', 'sort_direction', 'per_page'
            ]);

            // Validate date range if provided
            if ($request->has('start_date') || $request->has('end_date')) {
                $dateRange = $this->validateDateRange($request);
                $filters['date_range'] = $dateRange;
            }

            $result = $this->saleService->getSales($filters);
            
            if ($request->ajax() || $request->wantsJson()) {
                return $result;
            }

            return view('sales.index', $result);
        });
    }

    /**
     * Show the form for creating a new sale
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['category', 'subcategory'])->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get();
        
        return view('sales.create', compact('customers', 'products', 'categories', 'subcategories'));
    }

    /**
     * Store a newly created sale
     */
    public function store(CreateSaleRequest $request)
    {
        return $this->handleAjaxRequest($request, function () use ($request) {
            $sale = $this->saleService->createSale($request->validated());
            
            $this->logOperation('sale_created', [
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'total_price' => $sale->total_price
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return $this->successResponse($sale, 'Sale created successfully', 201);
            }

            return redirect()->route('sales.index')
                ->with('success', 'Sale created successfully.');
        });
    }

    /**
     * Display the specified sale
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'saleItems.product']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified sale
     */
    public function edit(Sale $sale)
    {
        $sale->load(['customer', 'saleItems.product']);
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['category', 'subcategory'])->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get();
        
        return view('sales.edit', compact('sale', 'customers', 'products', 'categories', 'subcategories'));
    }

    /**
     * Update the specified sale
     */
    public function update(CreateSaleRequest $request, Sale $sale)
    {
        return $this->handleAjaxRequest($request, function () use ($request, $sale) {
            // For now, we'll implement a simple status update
            // Full update would require more complex logic to handle stock changes
            if ($request->has('status')) {
                $sale = $this->saleService->updateSaleStatus($sale, $request->status);
            }
            
            $this->logOperation('sale_updated', [
                'sale_id' => $sale->id,
                'status' => $sale->status
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return $this->successResponse($sale, 'Sale updated successfully');
            }

            return redirect()->route('sales.index')
                ->with('success', 'Sale updated successfully.');
        });
    }

    /**
     * Remove the specified sale
     */
    public function destroy(Request $request, Sale $sale)
    {
        return $this->handleAjaxRequest($request, function () use ($sale) {
            $sale = $this->saleService->cancelSale($sale);
            
            $this->logOperation('sale_cancelled', [
                'sale_id' => $sale->id
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return $this->successResponse(null, 'Sale cancelled successfully');
            }

            return redirect()->route('sales.index')
                ->with('success', 'Sale cancelled successfully.');
        });
    }

    /**
     * Get sales statistics
     */
    public function statistics(Request $request)
    {
        return $this->handleAjaxRequest($request, function () use ($request) {
            $filters = [];
            
            if ($request->has('start_date') || $request->has('end_date')) {
                $filters['date_range'] = $this->validateDateRange($request);
            }

            $statistics = $this->saleService->getSaleStatistics($filters);
            
            return $this->successResponse($statistics, 'Sales statistics retrieved successfully');
        });
    }

    /**
     * Import sales data from Excel
     */
    public function import(Request $request)
    {
        return $this->handleAjaxRequest($request, function () use ($request) {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:10240'
            ]);

            $file = $request->file('file');
            
            $this->logOperation('sales_import_started', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);

            Excel::import(new SalesDataImport, $file);

            $this->logOperation('sales_import_completed', [
                'filename' => $file->getClientOriginalName()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return $this->successResponse(null, 'Sales data imported successfully');
            }

            return redirect()->route('sales.index')
                ->with('success', 'Sales data imported successfully.');
        });
    }

    /**
     * Export sales data to Excel
     */
    public function export(Request $request)
    {
        return $this->handleAjaxRequest($request, function () use ($request) {
            $filters = $this->getFilterParams($request, [
                'customer_id', 'status', 'start_date', 'end_date'
            ]);

            if ($request->has('start_date') || $request->has('end_date')) {
                $filters['date_range'] = $this->validateDateRange($request);
            }

            $this->logOperation('sales_export_requested', $filters);

            // This would typically use an export class
            // For now, we'll return a success response
            return $this->successResponse(null, 'Export functionality will be implemented');
        });
    }

    /**
     * Get sales for a specific customer
     */
    public function getCustomerSales(Request $request, Customer $customer)
    {
        return $this->handleAjaxRequest($request, function () use ($customer) {
            $sales = $this->saleService->getSales(['customer_id' => $customer->id]);
            
            return $this->successResponse($sales, 'Customer sales retrieved successfully');
        });
    }

    /**
     * Update sale status
     */
    public function updateStatus(Request $request, Sale $sale)
    {
        return $this->handleAjaxRequest($request, function () use ($request, $sale) {
            $request->validate([
                'status' => 'required|in:pending,completed,cancelled'
            ]);

            $sale = $this->saleService->updateSaleStatus($sale, $request->status);
            
            $this->logOperation('sale_status_updated', [
                'sale_id' => $sale->id,
                'new_status' => $sale->status
            ]);

            return $this->successResponse($sale, 'Sale status updated successfully');
        });
    }
}
