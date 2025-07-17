<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Imports\CustomersImport;
use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index(Request $request) // Added search functionality here too
    {
        $searchTerm = $request->input('search');

        $query = Customer::query();

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('gst_number', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            });
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('customers.index', compact('customers', 'searchTerm'));
    }

    // ... create() and store() methods are fine ...

    /**
     * Display the specified customer's ledger.
     */
    public function show(Customer $customer)
    {
        // Eager load all data needed for the ledger view
        $customer->load(['invoices', 'payments' => function ($query) {
            $query->latest('payment_date');
        }]);

        // Calculate the summary statistics
        $totalInvoiced = $customer->invoices->sum('total');
        $totalReceived = $customer->payments->sum('amount');
        $balanceDue = $totalInvoiced - $totalReceived;

        return view('customers.show', compact('customer', 'totalInvoiced', 'totalReceived', 'balanceDue'));
    }

    // ... the rest of your controller methods (edit, update, etc.) remain the same ...
    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email|max:255',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:250',
            'gst_number' => 'nullable|string|max:15|unique:customers,gst_number',
            'pan_number' => 'nullable|string|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'address' => 'nullable|string|max:500',
        ]);
        Customer::create($validated);
        return redirect()->route('customers.index')->with('success', 'Customer added successfully.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id . '|max:255',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:250',
            'gst_number' => 'nullable|string|max:15',
            'pan_number' => 'nullable|string|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'address' => 'nullable|string|max:500',
        ]);
        $customer->update($validated);
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function export()
    {
        return Excel::download(new CustomersExport, 'customers.xlsx');
    }
    
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:2048']);
        try {
            Excel::import(new CustomersImport, $request->file('file'));
            return redirect()->route('customers.index')->with('success', 'Customers imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('customers.index')->with('error', 'Error importing customers: ' . $e->getMessage());
        }
    }
}