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
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#remainingItemsModal">
                            <i class="fa fa-list me-1"></i> Remaining Items
                        </button>
                        <a href="{{ route('purchase_orders.create') }}" class="btn btn-light btn-sm">
                            <i class="fa fa-plus me-1"></i> Create PO
                        </a>
                    </div>
                </div>
                
                <!-- Filter Form -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('purchase_orders.index') }}" class="row g-3" id="filterForm">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="party_id" class="form-label">Party/Vendor</label>
                            <select name="party_id" id="party_id" class="form-select">
                                <option value="">All Parties</option>
                                @foreach($parties as $party)
                                    <option value="{{ $party->id }}" {{ request('party_id') == $party->id ? 'selected' : '' }}>
                                        {{ $party->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('purchase_orders.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
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
                                    <th class="text-center">Remaining Items</th>
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

                                    {{-- Calculate remaining items count --}}
                                    <td class="text-center">
                                        @php
                                        $receivedViaNote = $po->receiptNoteItems
                                            ->where('status', 'received')
                                            ->groupBy('product_id')
                                            ->map(fn($items) => $items->sum('quantity'));
                                        
                                        $receivedViaEntry = $po->purchaseEntryItems
                                            ->where('status', 'received')
                                            ->groupBy('product_id')
                                            ->map(fn($items) => $items->sum('quantity'));

                                        $remainingCount = 0;
                                        foreach ($po->items as $item) {
                                            $fromNote = $receivedViaNote->get($item->product_id, 0);
                                            $fromEntry = $receivedViaEntry->get($item->product_id, 0);
                                            $totalReceived = $fromNote + $fromEntry;
                                            $remaining = $item->quantity - $totalReceived;
                                            if ($remaining > 0) {
                                                $remainingCount++;
                                            }
                                        }
                                        @endphp
                                        @if($remainingCount > 0)
                                            <span class="badge bg-warning text-dark">{{ $remainingCount }} items</span>
                                        @else
                                            <span class="badge bg-success">All received</span>
                                        @endif
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
                                    <td colspan="7" class="text-center p-4">
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

    <!-- Remaining Items Modal -->
    <div class="modal fade" id="remainingItemsModal" tabindex="-1" aria-labelledby="remainingItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="remainingItemsModalLabel">
                        <i class="fa fa-list me-2"></i>Remaining Items to Receive
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Filter Form for Remaining Items -->
                    <form id="remainingItemsFilterForm" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="remaining_status" class="form-label">Status</label>
                            <select name="status" id="remaining_status" class="form-select">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="partial">Partial</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="remaining_party_id" class="form-label">Party/Vendor</label>
                            <select name="party_id" id="remaining_party_id" class="form-select">
                                <option value="">All Parties</option>
                                @foreach($parties as $party)
                                    <option value="{{ $party->id }}">{{ $party->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="remaining_start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="remaining_start_date" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="remaining_end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="remaining_end_date" class="form-control">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-primary me-2" onclick="loadRemainingItems()">
                                <i class="fa fa-search"></i> Search
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportRemainingItems()">
                                <i class="fa fa-download"></i> Excel
                            </button>
                        </div>
                    </form>

                    <!-- Summary Card -->
                    <div class="row mb-3" id="summarySection" style="display: none;">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Items</h5>
                                    <h3 id="totalItems">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Remaining Value</h5>
                                    <h3 id="totalValue">₹0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Unique Purchase Orders</h5>
                                    <h3 id="uniquePOs">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading remaining items...</p>
                    </div>

                    <!-- Results Table -->
                    <div id="remainingItemsTable" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>PO Number</th>
                                        <th>Party</th>
                                        <th>Order Date</th>
                                        <th>Product</th>
                                        <th>Item Code</th>
                                        <th>Ordered</th>
                                        <th>Received</th>
                                        <th>Remaining</th>
                                        <th>Unit Price</th>
                                        <th>Remaining Value</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="remainingItemsBody">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- No Results Message -->
                    <div id="noResultsMessage" class="text-center py-5" style="display: none;">
                        <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No remaining items found</h5>
                        <p class="text-muted">All items in the filtered purchase orders have been received.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loadRemainingItems() {
            const form = document.getElementById('remainingItemsFilterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();

            // Show loading spinner
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('remainingItemsTable').style.display = 'none';
            document.getElementById('noResultsMessage').style.display = 'none';
            document.getElementById('summarySection').style.display = 'none';

            fetch(`{{ route('purchase_orders.remaining_items') }}?${params}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loadingSpinner').style.display = 'none';
                    
                    if (data.remaining_items.length > 0) {
                        // Show summary
                        document.getElementById('summarySection').style.display = 'block';
                        document.getElementById('totalItems').textContent = data.total_count;
                        document.getElementById('totalValue').textContent = '₹' + new Intl.NumberFormat('en-IN').format(data.total_remaining_value);
                        
                        // Count unique purchase orders
                        const uniquePOs = [...new Set(data.remaining_items.map(item => item.purchase_order_number))].length;
                        document.getElementById('uniquePOs').textContent = uniquePOs;

                        // Populate table
                        const tbody = document.getElementById('remainingItemsBody');
                        tbody.innerHTML = '';
                        
                        data.remaining_items.forEach(item => {
                            const row = `
                                <tr>
                                    <td class="fw-bold text-primary">${item.purchase_order_number}</td>
                                    <td>${item.party_name}</td>
                                    <td>${item.order_date}</td>
                                    <td>${item.product_name}</td>
                                    <td>${item.item_code || 'N/A'}</td>
                                    <td class="text-center">${item.ordered_quantity}</td>
                                    <td class="text-center">${item.received_quantity}</td>
                                    <td class="text-center fw-bold text-warning">${item.remaining_quantity}</td>
                                    <td class="text-end">₹${new Intl.NumberFormat('en-IN').format(item.unit_price)}</td>
                                    <td class="text-end fw-bold">₹${new Intl.NumberFormat('en-IN').format(item.remaining_value)}</td>
                                    <td><span class="badge bg-${getStatusColor(item.status)}">${item.status}</span></td>
                                </tr>
                            `;
                            tbody.innerHTML += row;
                        });
                        
                        document.getElementById('remainingItemsTable').style.display = 'block';
                    } else {
                        document.getElementById('noResultsMessage').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loadingSpinner').style.display = 'none';
                    alert('Error loading remaining items. Please try again.');
                });
        }

        function exportRemainingItems() {
            const form = document.getElementById('remainingItemsFilterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            
            window.open(`{{ route('purchase_orders.export_remaining_items') }}?${params}`, '_blank');
        }

        function getStatusColor(status) {
            switch(status.toLowerCase()) {
                case 'pending': return 'warning';
                case 'partial': return 'info';
                case 'completed': return 'success';
                default: return 'secondary';
            }
        }

        // Load remaining items when modal is opened
        document.getElementById('remainingItemsModal').addEventListener('shown.bs.modal', function () {
            loadRemainingItems();
        });
    </script>
</body>
@include('layout.footer')