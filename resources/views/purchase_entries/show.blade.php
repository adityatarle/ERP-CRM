@include('layout.header')
<body class="act-purchaseentries">
    <div class="main-content-area">
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h1 class="mb-0 text-white">Purchase Entry Details</h1>
                    <a href="{{ route('purchase_entries.index') }}" class="btn btn-light">Back to List</a>
                </div>
                <div class="card-body p-3">
                    @if (session('success'))
                        <div class="alert alert-success mt-2">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
                    @endif
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Purchase Number:</strong> {{ $purchaseEntry->purchase_number }}</p>
                                <p><strong>Purchase Date:</strong> {{ $purchaseEntry->purchase_date }}</p>
                                <p><strong>Invoice Number:</strong> {{ $purchaseEntry->invoice_number }}</p>
                                <p><strong>Invoice Date:</strong> {{ $purchaseEntry->invoice_date }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Party:</strong> {{ $purchaseEntry->party->name }}</p>
                                <p><strong>Total with GST:</strong> ₹{{ number_format($purchaseEntry->items->sum('total_price'), 2) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <h5>Items</h5>
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Discount (%)</th>
                                    <th>CGST (%)</th>
                                    <th>SGST (%)</th>
                                    <th>IGST (%)</th>
                                    <th>Status</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseEntry->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $item->discount ?? '0' }}%</td>
                                        <td>{{ $item->cgst_rate ?? '0' }}%</td>
                                        <td>{{ $item->sgst_rate ?? '0' }}%</td>
                                        <td>{{ $item->igst_rate ?? '0' }}%</td>
                                        <td>{{ $item->status }}</td>
                                        <td>₹{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No items available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
@include('layout.footer')