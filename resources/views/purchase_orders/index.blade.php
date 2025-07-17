@include('layout.header')

<style>
    /* Page & Card Styling */
    body {
        background-color: #f4f7f9;
    }

    .main-content-area {
        min-height: 100vh;
    }

    .card-header h1 {
        font-size: 1.25rem;
        font-weight: 600;
    }

    .table thead th {
        background-color: #e9ecef;
        font-weight: 600;
        color: #495057;
        white-space: nowrap;
        padding: 0.75rem 1rem;
    }

    .table td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
    }

    .action-buttons form {
        display: inline-block;
    }

    .status-badge {
        padding: 0.4em 0.7em;
        font-size: 0.8rem;
        font-weight: 500;
        border-radius: 50rem;
        /* pill shape */
    }

    .status-completed {
        background-color: rgba(25, 135, 84, 0.1);
        color: #0f5132;
    }

    .status-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: #664d03;
    }

    .status-partial {
        background-color: rgba(13, 202, 240, 0.1);
        color: #0a58ca;
    }
</style>

<body class="act-po">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white p-3">
                    <h1 class="mb-0 h5 text-white">
                        <i class="fa fa-shopping-cart me-2"></i>
                        Purchase Orders
                    </h1>
                    <a href="{{ route('purchase_orders.create') }}" class="btn btn-light btn-sm">
                        <i class="fa fa-plus me-1"></i> Create PO
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">PO Number</th>
                                    <th>Party / Vendor</th>
                                    <th>Order Date</th>
                                    <th class="text-end">Total Value</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders as $po)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-primary">{{ $po->purchase_order_number }}</div>
                                    </td>
                                    <td>{{ $po->party->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($po->order_date)->format('d M, Y') }}</td>

                                    {{-- Calculate total value on the fly --}}
                                    <td class="text-end">
                                        ₹{{ number_format($po->items->sum('total_price'), 2) }}
                                    </td>

                                    <!-- In resources/views/purchase_orders/index.blade.php -->
                                    <td class="text-center">
                                        @php
                                        // Use the new dynamic status from the model accessor
                                        $status = $po->receipt_status;
                                        $statusClass = 'status-' . strtolower($status);
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
                                    </td>
                                    <td class="text-end pe-3 action-buttons">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('purchase_orders.download_pdf', $po->id) }}" class="btn btn-danger btn-sm" title="Download PDF">
                                                <i class="fa fa-file-pdf"></i>
                                            </a>
                                            <a href="{{ route('purchase_orders.show', $po->id) }}" class="btn btn-warning btn-sm" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            {{-- Add Edit/Delete buttons if needed --}}
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center p-4">
                                        No purchase orders found. Click "Create PO" to get started.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($purchaseOrders->hasPages())
                <div class="card-footer bg-light border-top">
                    {{ $purchaseOrders->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
@include('layout.footer')