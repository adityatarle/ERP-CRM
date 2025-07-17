@include('layout.header')

<body class="act-delivery-notes">
    <div class="main-content-area">
        @if (session('success'))
            <div class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-2">{{ session('error') }}</div>
        @endif
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                    <h5 class="mb-2 mb-md-0 text-white">Delivery Notes</h5>
                    <a href="{{ route('delivery_notes.create') }}" class="btn btn-light">Create Delivery Note</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Delivery Note Number</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Delivery Date</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($deliveryNotes as $deliveryNote)
                                <tr>
                                    <td>{{ $deliveryNote->delivery_note_number }}</td>
                                    <td>{{ $deliveryNote->customer->name }}</td>
                                    <td>{{ $deliveryNote->items->first()->product->name ?? 'No product' }}</td>
                                    <td>{{ $deliveryNote->items->sum('quantity') }}</td>
                                    <td>{{ number_format($deliveryNote->items->sum(fn($item) => $item->quantity * $item->price), 2) }}</td>
                                    <td>{{ $deliveryNote->delivery_date }}</td>
                                    <td>{{ $deliveryNote->notes ?? 'N/A' }}</td>
                                    <td class="d-flex gap-1">
                                        <a href="{{ route('delivery_notes.show', $deliveryNote) }}" class="btn btn-sm btn-info">View</a>
                                        <a href="{{ route('delivery_notes.edit', $deliveryNote) }}" class="btn btn-sm btn-warning">Edit</a>
                                       
                                        <a href="{{ route('delivery_notes.downloadPdf', $deliveryNote) }}" class="btn btn-sm btn-success">Download PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No delivery notes found.</td>
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