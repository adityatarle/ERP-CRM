@include('layout.header')

<body class="act-product">
    <div class="main-content-area">
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h1 class="mb-0 text-white">Product Details</h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-5"><strong>ID:</strong> {{ $product->id }}</p>
                            <p class="mb-5"><strong>Name:</strong> {{ $product->name }}</p>
                            <p class="mb-5"><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-5"><strong>Stock:</strong> {{ $product->stock }}</p>
                            <p class="mb-5"><strong>Description:</strong> {{ $product->description ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Purchase History Section -->
                    <h3 class="mt-5 mb-3">Purchase History</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Party</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($product->purchaseEntryItems as $item)
                                    <tr>
                                        <td>{{ $item->purchaseEntry->purchase_date ?? 'N/A' }}</td>
                                        <td>{{ $item->purchaseEntry->party->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="text-success">
                                                +{{ $item->quantity }}
                                            </span>
                                        </td>
                                        <td>${{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ number_format($item->discount, 2) }}%</td>
                                        <td>${{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No purchase history available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Sales History Section -->
                   <h3 class="mt-5 mb-3">Sales History</h3>
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($product->saleItems as $item)
                @if ($item->sale)
                    <tr>
                        <td>{{ $item->sale->created_at->toDateString() }}</td>
                        <td>{{ $item->sale->customer->name ?? 'N/A' }}</td>
                        <td>
                            <span class="text-danger">
                                -{{ $item->quantity }}
                            </span>
                        </td>
                        <td>₹{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->discount, 2) }}%</td>
                        <td>₹{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @else
                    <!-- Log or skip orphaned SaleItem records -->
                    @php
                        \Log::warning("Skipping sale item due to missing sale", [
                            'sale_item_id' => $item->id,
                            'product_id' => $product->id,
                            'sale_id' => $item->sale_id,
                        ]);
                    @endphp
                    @continue
                @endif
            @empty
                <tr>
                    <td colspan="6" class="text-center">No sales history available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

                    <div class="mt-4">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back to Products</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<style>
    .text-success {
        color: #28a745;
    }

    .text-danger {
        color: #dc3545;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }
</style>

@include('layout.footer')