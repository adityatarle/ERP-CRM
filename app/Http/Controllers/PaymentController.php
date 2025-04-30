<?php

namespace App\Http\Controllers;

use App\Models\Payable;
use App\Models\Payment;
use App\Models\PurchaseEntry;
use App\Models\Party;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payables = Payable::with('purchaseEntry', 'party')->where('is_paid', false)->get();
        $unpaidPurchaseEntries = PurchaseEntry::whereIn('id', Payable::where('is_paid', false)->pluck('purchase_entry_id'))->get();
        $parties = Party::all();
        return view('payments.index', compact('payables', 'unpaidPurchaseEntries', 'parties'));
    }

    public function create()
    {
        $unpaidPurchaseEntries = PurchaseEntry::whereIn('id', Payable::where('is_paid', false)->pluck('purchase_entry_id'))->get();
        $parties = Party::all();
        return view('payments.create', compact('unpaidPurchaseEntries', 'parties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_entry_id' => 'required|exists:purchase_entries,id',
            'party_id' => 'required|exists:parties,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $payable = Payable::where('purchase_entry_id', $request->purchase_entry_id)->where('is_paid', false)->firstOrFail();
        $paidAmount = $request->amount;

        if ($paidAmount > $payable->amount) {
            return redirect()->back()->withErrors(['amount' => 'Payment amount cannot exceed payable amount.']);
        }

        \DB::transaction(function () use ($request, $payable, $paidAmount) {
            Payment::create([
                'purchase_entry_id' => $request->purchase_entry_id,
                'party_id' => $request->party_id,
                'amount' => $paidAmount,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
            ]);

            $payable->amount -= $paidAmount;
            if ($payable->amount <= 0) {
                $payable->is_paid = true;
            }
            $payable->save();
        });

        return redirect()->route('payments.index')->with('success', 'Payment recorded successfully.');
    }

    public function paymentsList()
    {
        $payments = Payment::with('purchaseEntry', 'party')->orderBy('payment_date', 'desc')->get();
        return view('payments.list', compact('payments'));
    }
}