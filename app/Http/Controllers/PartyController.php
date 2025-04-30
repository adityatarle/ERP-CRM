<?php

namespace App\Http\Controllers;

use App\Models\Party;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PartiesImport;

class PartyController extends Controller
{
    public function index()
    {
        $parties = Party::all();
        return view('parties.index', compact('parties'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        Excel::import(new PartiesImport, $request->file('file'));

        return redirect()->back()->with('success', 'Parties imported successfully.');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $parties = Party::where('name', 'like', "%$query%")->get();
        return response()->json($parties);
    }
}