@include('layout.header')

<style>
    /* Your existing styles are fine */
    body { background-color: #f4f7f9; }
    .main-content-area { min-height: 100vh; }
    .card-header h1 { font-size: 1.25rem; font-weight: 600; }
    .details-card { background-color: #fff; border: 1px solid #dee2e6; border-radius: .375rem; padding: 1.5rem; }
    .details-card h6 { font-size: 1rem; font-weight: 600; color: #495057; margin-bottom: 1rem; }
    .details-card p { margin-bottom: 0.5rem; font-size: 0.95rem; }
    .details-card .label { color: #6c757d; width: 120px; display: inline-block; }
    .table thead th { background-color: #e9ecef; font-weight: 600; color: #495057; white-space: nowrap; }
    .table td { vertical-align: middle; }
    .status-badge { padding: 0.4em 0.7em; font-size: 0.75rem; font-weight: 600; border-radius: 50rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-Completed { background-color: rgba(25, 135, 84, 0.1); color: #0f5132; }
    .status-Partial { background-color: rgba(255, 193, 7, 0.15); color: #664d03; }
    .status-Pending { background-color: rgba(108, 117, 125, 0.1); color: #41464b; }
</style>

<body class="act-po-show">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white p-3">
                    <h1 class="mb-0 h5"><i class="fa fa-file-alt me-2"></i>Purchase Order Details</h1>
                    <a href="{{ route('purchase_orders.index') }}" class="btn btn-light btn-sm"><i class="fa fa-arrow-left me-1"></i> Back to List</a>
                </div>
                <div class="card-body p-4">
                    {{-- PO Main Details Section --}}
                    <div class="row g-4 mb-4">
                        <div class="col-md-8">
                            <div class="details-card h-100">
                                <h6>Order Information</h6>
                                <p><span class="label">PO Number:</span> <strong>{{ $purchaseOrder->purchase_order_number }}</strong></p>
                                <p><span class="label">Party / Vendor:</span> {{ $purchaseOrder->party->name }}</p>
                                <p><span class="label">Order Date:</span> {{ $purchaseOrder->order_date->format('d M, Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="details-card h-100 text-center">
                                <h6>Overall Status</h6>
                                @php
                                    $status = $purchaseOrder->receipt_status; 
                                    $statusClass = 'status-' . ucfirst(strtolower($status));
                                @endphp
                                <p class="h4 mt-3">
                                    <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
                                </p>
                                <p class="small text-muted mt-3">Last updated: {{ $purchaseOrder->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Products Table Section --}}
                    <h5 class="mt-4 mb-3">Item Receipt Status</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th class="text-center">Ordered</th>
                                    <th class="text-center">Received</th>
                                    <th class="text-center">Remaining</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $item)
                                    @php
                                        // THE FIX IS HERE: Use the correct variable name from the controller
                                        $received = $totalReceivedQuantities->get($item->product_id, 0);
                                        $remaining = $item->quantity - $received;
                                    @endphp
                                    <tr>
                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-center">{{ $received }}</td>
                                        <td class="text-center font-weight-bold {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                            <strong>{{ $remaining }}</strong>
                                        </td>
                                        <td class="text-center">
                                            @if($remaining <= 0)
                                                <span class="status-badge status-Completed">Completed</span>
                                            @elseif($received > 0)
                                                <span class="status-badge status-Partial">Partial</span>
                                            @else
                                                <span class="status-badge status-Pending">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
@include('layout.footer')