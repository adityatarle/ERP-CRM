@include('layout.header')

<body class="act-delivery-notes-show">
    <div class="main-content-area">
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                    <h5>Delivery Note: {{ $deliveryNote->delivery_note_number }}</h5>
                    <a href="{{ route('delivery_notes.index') }}" class="btn btn-light">Back to Delivery Notes</a>
                </div>
                <div class="card-body">
                    <p><strong>Customer:</strong> {{ $deliveryNote->customer->name }}</p>
                    <p><strong>Delivery Date:</strong> {{ $deliveryNote->delivery_date }}</p>
                    <p><strong>Notes:</strong> {{ $deliveryNote->notes ?? 'N/A' }}</p>
                    <p><strong>Total Price:</strong> {{ number_format($deliveryNote->items->sum(fn($item) => $item->quantity * $item->price), 2) }}</p>

                    <h6>Items Delivered:</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deliveryNote->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->price, 2) }}</td>
                                    <td>{{ number_format($item->quantity * $item->price, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

@include('layout.footer')