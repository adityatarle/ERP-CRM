<?php

namespace App\Http\Controllers;

use App\Models\Party;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PartiesImport;

class PartyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index(Request $request)
    {
        $searchTerm = $request->input('search');

        // Start with the base query
        $query = Party::query();

        // If a search term is provided, apply the filter
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('gst_in', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Paginate the results and ensure the search query is appended to pagination links
        $parties = $query->latest()->paginate(15)->withQueryString();

        return view('parties.index', compact('parties', 'searchTerm'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('parties.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'gst_in' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255|unique:parties,email',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Party::create($validatedData);

        return redirect()->route('parties.index')->with('success', 'Party created successfully.');
    }

    /**
     * Display the specified resource.
     * Uses Route-Model Binding to automatically find the party.
     */
    public function show(Party $party)
    {
        // Eager load all necessary relationships for the ledger view
        $party->load([
            'purchaseEntries' => function ($query) {
                $query->with('payable')->latest('purchase_date');
            },
            'payments' => function ($query) {
                $query->latest('payment_date');
            }
        ]);

        // Calculate statement summary
        $totalBilled = $party->purchaseEntries->sum(function ($entry) {
            // Sum of total_price from all items in each entry
            return $entry->items->sum('total_price');
        });

        $totalPaid = $party->payments->sum('amount');
        $balanceDue = $totalBilled - $totalPaid;

        return view('parties.show', compact('party', 'totalBilled', 'totalPaid', 'balanceDue'));
    }


    /**
     * Show the form for editing the specified resource.
     * Uses Route-Model Binding to automatically find the party.
     */
    public function edit(Party $party)
    {
        return view('parties.edit', compact('party'));
    }

    /**
     * Update the specified resource in storage.
     * Uses Route-Model Binding to automatically find the party.
     */
    public function update(Request $request, Party $party)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'gst_in' => 'nullable|string|max:15',
            // Ensure email is unique, but ignore the current party's email during validation
            'email' => 'nullable|email|max:255|unique:parties,email,' . $party->id,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $party->update($validatedData);

        // Redirect to the show page to see the updated details
        return redirect()->route('parties.show', $party)->with('success', 'Party details updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * Uses Route-Model Binding to automatically find the party.
     */
    public function destroy(Party $party)
    {
        $party->delete();
        return redirect()->route('parties.index')->with('success', 'Party deleted successfully.');
    }

    /**
     * Handle file import.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        Excel::import(new PartiesImport, $request->file('file'));

        return redirect()->back()->with('success', 'Parties imported successfully.');
    }

    /**
     * Handle AJAX search requests.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $parties = Party::where('name', 'like', "%$query%")->get();
        return response()->json($parties);
    }
}
