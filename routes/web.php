<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseEntryController;
use App\Http\Controllers\PaymentController;

// Home page (login form for guests, redirects to dashboard for authenticated users)
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('home');
})->name('home');

// Dashboard (requires authentication)
Route::get('/dashboard', function () {
    $totalSales = \App\Models\Sale::sum('total_price');
    $lowStockProducts = \App\Models\Product::where('stock', '<', 5)->get();
    $recentSales = \App\Models\Sale::with('customer', 'saleItems.product')->latest()->take(5)->get();
    return view('dashboard', compact('totalSales', 'lowStockProducts', 'recentSales'));
})->middleware('auth')->name('dashboard');

// Authentication routes (login, register, logout, etc.)
Auth::routes(['verify' => false]); // Email verification disabled


// Authenticated routes (require login)
Route::middleware('auth')->group(function () {
    
    Route::get('/payables', [PaymentController::class, 'index'])->name('payments.index');
Route::get('/payables/create', [PaymentController::class, 'create'])->name('payments.create');
Route::post('/payables', [PaymentController::class, 'store'])->name('payments.store');
Route::get('/payments', [PaymentController::class, 'paymentsList'])->name('payments.list');
    

    Route::get('/purchase-entries', [PurchaseEntryController::class, 'index'])->name('purchase_entries.index');
    Route::get('/purchase-entries/create', [PurchaseEntryController::class, 'create'])->name('purchase_entries.create');
    Route::post('/purchase-entries', [PurchaseEntryController::class, 'store'])->name('purchase_entries.store');
    Route::get('/purchase-entries/{purchaseEntry}/edit', [PurchaseEntryController::class, 'edit'])->name('purchase_entries.edit');
Route::put('/purchase-entries/{purchaseEntry}', [PurchaseEntryController::class, 'update'])->name('purchase_entries.update');
    

Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase_orders.index');
Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase_orders.create');
Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase_orders.store');
Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase_orders.approve');
Route::get('/purchase-orders/{id}/download-pdf', [PurchaseOrderController::class, 'downloadPDF'])
    ->name('purchase_orders.download_pdf');

    Route::get('/parties/search', [PartyController::class, 'search'])->name('parties.search');

    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');



Route::get('/parties', [PartyController::class, 'index'])->name('parties.index');
Route::post('/parties/import', [PartyController::class, 'import'])->name('parties.import');
Route::get('/parties/search', [PartyController::class, 'search'])->name('parties.search');
    

    
    
    
    // Product routes
    Route::resource('products', ProductController::class)->names([
        'index' => 'products.index',
        'create' => 'products.create',
        'store' => 'products.store',
        'show' => 'products.show',
        'edit' => 'products.edit',
        'update' => 'products.update',
        'destroy' => 'products.destroy',
    ]);
    Route::get('/test-subcategories', function () {
        $subcategories = App\Models\Product::whereNotNull('subcategory')->distinct()->pluck('subcategory')->sort();
        \Log::info('Subcategories: ' . $subcategories->toJson());
        return response()->json($subcategories);
    });
    Route::get('/products/export/excel', [ProductController::class, 'export'])->name('products.export');
    Route::post('/products/import/excel', [ProductController::class, 'import'])->name('products.import');

    // Customer routes
    Route::resource('customers', CustomerController::class)->names([
        'index' => 'customers.index',
        'create' => 'customers.create',
        'store' => 'customers.store',
        'show' => 'customers.show',
        'edit' => 'customers.edit',
        'update' => 'customers.update',
        'destroy' => 'customers.destroy',
    ]);
    Route::get('/customers/export/excel', [CustomerController::class, 'export'])->name('customers.export');
    Route::post('/customers/import/excel', [CustomerController::class, 'import'])->name('customers.import');

    // Sales routes
    Route::resource('sales', SaleController::class)->names([
        'index' => 'sales.index',
        'create' => 'sales.create',
        'store' => 'sales.store',
        'show' => 'sales.show',
        'edit' => 'sales.edit',
        'update' => 'sales.update',
        // 'destroy' => 'sales.destroy',
    ]);
    Route::put('sales/update-status/{id}', [SaleController::class, 'updateStatus'])->name('sales.update-status');

    // Invoice routes
    Route::get('/invoices/pending', [InvoiceController::class, 'pendingInvoices'])->name('invoices.pending')->middleware('superadmin');
    Route::post('/invoices/{invoice}/approve', [InvoiceController::class, 'approve'])->name('invoices.approve')->middleware('superadmin');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'generatePDF'])->name('invoices.pdf');

    Route::resource('invoices', InvoiceController::class)->names([
        'index' => 'invoices.index',
        'create' => 'invoices.create',
        'store' => 'invoices.store',
        'show' => 'invoices.show',
        'edit' => 'invoices.edit',
        'update' => 'invoices.update',
        'destroy' => 'invoices.destroy',
    ]);

    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index')->middleware('auth');
});