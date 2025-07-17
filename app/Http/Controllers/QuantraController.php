<?php

namespace App\Http\Controllers;

use App\Models\Quantra;
use Illuminate\Http\Request;

class QuantraController extends Controller
{
    public function index()
    {
        $quantras = Quantra::latest()->get();
        return view('quantra.index', compact('quantras'));
    }

    public function create()
    {
        return view('quantra.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        Quantra::create($validated);

        return redirect()->route('quantra.index')
            ->with('success', 'Quantra entry created successfully.');
    }
}