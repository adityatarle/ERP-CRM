@include('layout.header')

<style>
    /* Page & Card Styling */
    body { background-color: #f4f7f9; }
    .main-content-area { min-height: 100vh; }
    .card-header h1 { font-size: 1.25rem; font-weight: 600; }
    .table thead th {
        background-color: #e9ecef; font-weight: 600;
        color: #495057; white-space: nowrap; padding: 0.75rem 1rem;
    }
    .table td { vertical-align: middle; padding: 0.75rem 1rem; }
    .filter-section {
        background-color: #f8f9fa; border: 1px solid #dee2e6;
        border-radius: .375rem;
    }
    .badge {
        padding: 0.4em 0.7em; font-size: 0.8rem;
        font-weight: 500; border-radius: 50rem;
    }
    .badge-primary { background-color: rgba(0, 123, 255, 0.1); color: #004085; }
    .badge-success { background-color: rgba(25, 135, 84, 0.1); color: #0f5132; }
    .badge-warning { background-color: rgba(255, 193, 7, 0.1); color: #664d03; }
    
    /* Total Value Display Styling */
    .total-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border-left: 4px solid transparent;
    }
    
    .total-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .total-card .card-title {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .total-card .h5 {
        font-weight: 700;
        font-size: 1.5rem;
    }
    
    .total-card .text-primary {
        color: #0d6efd !important;
    }
    
    .total-card .text-success {
        color: #198754 !important;
    }
    
    .total-card .text-muted {
        font-size: 0.875rem;
        font-weight: 500;
    }
</style>

<body class="act-invoice">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex flex-column flex-md-row justify-content-between align-items-md-center text-white p-3">
                    <h1 class="mb-2 mb-md-0 h5 text-white">
                        <i class="fa fa-file-invoice me-2"></i> Invoices
                    </h1>
                    <a href="{{ route('invoices.create') }}" class="btn btn-light btn-sm">
                        <i class="fa fa-plus me-1"></i> Create Invoice
                    </a>
                </div>
                <div class="card-body p-3 p-md-4">
                    <!-- Filter Form -->
                    <div class="filter-section p-3 mb-4">
                        <form method="GET" action="{{ route('invoices.index') }}" id="filterForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="customer_search" class="form-label small">Customer Name</label>
                                    <input type="text" name="customer_search" id="customer_search" class="form-control form-control-sm" value="{{ $customer_search ?? '' }}" placeholder="Enter customer name">
                                </div>
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label small">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label small">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}">
                                </div>
                                <div class="col-md-3 d-flex justify-content-start align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-times"></i> Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Total Values Display -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm total-card">
                                <div class="card-body p-3">
                                    <h6 class="card-title text-muted mb-2">
                                        <i class="fa fa-filter me-2"></i>
                                        @if($startDate || $endDate || $customer_search)
                                            Filtered Results
                                        @else
                                            All Invoices
                                        @endif
                                    </h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Total Value:</span>
                                        <span class="h5 mb-0 text-primary">₹{{ number_format($filteredTotal, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="text-muted">Count:</span>
                                        <span class="text-muted">{{ $filteredCount }} invoices</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm total-card">
                                <div class="card-body p-3">
                                    <h6 class="card-title text-muted mb-2">
                                        <i class="fa fa-chart-line me-2"></i>
                                        Overall Summary
                                    </h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Total Value:</span>
                                        <span class="h5 mb-0 text-success">₹{{ number_format($overallTotal, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="text-muted">Count:</span>
                                        <span class="text-muted">{{ $overallCount }} invoices</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoices Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoices as $invoice)
                                    <tr class="{{ $invoice->status === 'on_hold' ? 'table-warning' : '' }}">
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->customer->name }}</td>
                                        <td>₹{{ number_format($invoice->total, 2) }}</td>
                                        <td>
                                            <span class="badge 
                                                {{ $invoice->status === 'pending' ? 'badge-primary' : 
                                                   ($invoice->status === 'approved' ? 'badge-success' : 
                                                   ($invoice->status === 'on_hold' ? 'badge-warning' : 'badge-info')) }}" style="color:#000;">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->created_at)->format('d M, Y') }}</td>
                                        <td class="d-flex flex-wrap gap-1">
                                            <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-warning">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                            @if (in_array($invoice->status, ['approved', 'paid']))
                                                <a href="{{ route('invoices.pdf', $invoice->id) }}" class="btn btn-sm btn-danger pdf-btn" target="_blank">
                                                    <i class="fa fa-file-pdf"></i> PDF
                                                </a>
                                            @elseif (in_array($invoice->status, ['pending', 'on_hold']))
                                                <button type="button" class="btn btn-sm btn-secondary showRestrictedPdfAlert">
                                                    <i class="fa fa-file-pdf"></i> PDF
                                                </button>
                                            @endif

                                            {{-- Edit and Unlock Request Logic --}}
                                            @if (Auth::user()->role === 'superadmin')
                                                <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                @if ($invoice->edit_request_status === 'pending_unlock')
                                                    <a href="{{ route('invoices.manage_unlock_request_form', $invoice->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fa fa-key"></i> Manage Unlock
                                                    </a>
                                                @endif
                                            @else
                                                @if (in_array($invoice->status, ['pending', 'on_hold']))
                                                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </a>
                                                @elseif (in_array($invoice->status, ['approved', 'paid']))
                                                    @if ($invoice->edit_request_status === 'unlock_approved')
                                                        <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-sm btn-info">
                                                            <i class="fa fa-edit"></i> Edit (Unlocked)
                                                        </a>
                                                    @elseif ($invoice->edit_request_status === 'pending_unlock')
                                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                                            <i class="fa fa-clock"></i> Unlock Requested
                                                        </button>
                                                    @elseif ($invoice->edit_request_status === 'unlock_rejected')
                                                        <button class="btn btn-sm btn-outline-danger" disabled title="Reason: {{ $invoice->unlock_decision_reason ?? 'N/A' }}">
                                                            <i class="fa fa-times-circle"></i> Unlock Rejected
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-primary request-unlock-btn"
                                                            data-invoice-id="{{ $invoice->id }}" data-invoice-number="{{ $invoice->invoice_number }}">
                                                            <i class="fa fa-undo"></i> Re-request Unlock
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-primary request-unlock-btn"
                                                            data-invoice-id="{{ $invoice->id }}" data-invoice-number="{{ $invoice->invoice_number }}">
                                                            <i class="fa fa-lock-open"></i> Request Edit Unlock
                                                        </button>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center p-4">No invoices found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($invoices->hasPages())
                        <div class="card-footer bg-light border-top">
                            {{ $invoices->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Unlock Request Modal -->
    <div class="modal fade" id="requestUnlockModal" tabindex="-1" aria-labelledby="requestUnlockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestUnlockModalLabel">Request Unlock for Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="unlockRequestForm" method="POST" action="">
                        @csrf
                        <div class="mb-3">
                            <label for="unlock_reason" class="form-label">Reason for Unlock Request</label>
                            <textarea name="unlock_reason" id="unlock_reason" class="form-control" rows="4" required></textarea>
                            @error('unlock_reason')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
@include('layout.footer')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // PDF restriction alert
    document.querySelectorAll('.showRestrictedPdfAlert').forEach(button => {
        button.addEventListener('click', function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invoice must be approved before downloading.',
                confirmButtonText: 'OK'
            });
        });
    });

    // Unlock request modal
    document.querySelectorAll('.request-unlock-btn').forEach(button => {
        button.addEventListener('click', function () {
            const invoiceId = this.getAttribute('data-invoice-id');
            const invoiceNumber = this.getAttribute('data-invoice-number');
            const form = document.getElementById('unlockRequestForm');
            form.action = `{{ url('invoices') }}/${invoiceId}/request-unlock`;
            document.getElementById('requestUnlockModalLabel').textContent = `Request Unlock for Invoice #${invoiceNumber}`;
            document.getElementById('unlock_reason').value = ''; // Clear previous input
            new bootstrap.Modal(document.getElementById('requestUnlockModal')).show();
        });
    });
});
</script>