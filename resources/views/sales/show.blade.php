@include('layout.header')

<body class="act-sales">
    <div class="main-content-area">
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h1 class="mb-0 text-white">Sale Details</h1>
                </div>
                <div class="card-body">
                    <!-- Customer Info -->
                    <div class="mb-4">
                        <p><strong>Name:</strong> {{ $sale->customer->name }}</p>
                        <p><strong>Email:</strong> {{ $sale->customer->email }}</p>
                        <p><strong>Phone No:</strong> {{ $sale->customer->phone }}</p>
                        <p><strong>Address:</strong> {{ $sale->customer->address }}</p>
                    </div>

                    <!-- Product Info Table -->
                    <div>
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th>Discount</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($sale->saleItems->isNotEmpty())
                                    @foreach ($sale->saleItems as $saleItem)
                                        <tr>
                                            <td>{{ $saleItem->product->name }}</td>
                                            <td>{{ $saleItem->quantity }}</td>
                                            <td>{{ number_format($saleItem->discount, 2) }}%</td>
                                            <td>${{ number_format($saleItem->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    <!-- Add a row for the total sale price -->
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total Sale Price:</strong></td>
                                        <td><strong>${{ number_format($sale->total_price, 2) }}</strong></td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center">No products found for this sale.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Back to Sales</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
@include('layout.footer')