<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PDF;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('party')->get();
        return view('purchase_orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $parties = Party::all();
        $products = Product::all();
        return view('purchase_orders.create', compact('parties', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_id' => 'required|exists:parties,id',
            'order_date' => 'required|date',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        $purchaseOrder = PurchaseOrder::create([
            'purchase_order_number' => 'PO-' . Str::random(8),
            'party_id' => $request->party_id,
            'order_date' => $request->order_date,
            'status' => 'pending',
        ]);

        foreach ($request->products as $product) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'unit_price' => $product['unit_price'],
                'total_price' => $product['quantity'] * $product['unit_price'],
            ]);
        }

        return redirect()->route('purchase_orders.index')->with('success', 'Purchase order created.');
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (auth()->user()->role !== 'superadmin') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $purchaseOrder->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Purchase order approved.');
    }

    public function downloadPDF($id)
    {
        $purchaseOrder = PurchaseOrder::with('party', 'items.product')->findOrFail($id);
        $pdf = PDF::loadView('purchase_orders.pdf', compact('purchaseOrder'));

        return $pdf->download('purchase_order_' . $purchaseOrder->purchase_order_number . '.pdf');
    }
}