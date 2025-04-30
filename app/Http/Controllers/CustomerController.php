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
    public function index()
    {
        $customers = Customer::all();
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:customers,email|max:255',
        'phone' => 'nullable|string|max:20',
        'gst_number' => 'nullable|string|max:15', // Adjust max length as needed
        'address' => 'nullable|string|max:500',
    ]);

    Customer::create($validated);

    return redirect()->route('customers.index')->with('success', 'Customer added successfully.');
}

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
{
    $customer->load('sales.product'); // eager load sales and their products
    return view('customers.show', compact('customer'));
}

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id . '|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    /**
     * Export customers to Excel.
     */
    public function export()
    {
        return Excel::download(new CustomersExport, 'customers.xlsx');
    }

    /**
     * Import customers from Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new CustomersImport, $request->file('file'));
            return redirect()->route('customers.index')->with('success', 'Customers imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('customers.index')->with('error', 'Error importing customers: ' . $e->getMessage());
        }
    }
}