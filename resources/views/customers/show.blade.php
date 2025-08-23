@include('layout.header')

<style>
    /* Add some specific styling for the ledger */
    .summary-card {
        border-left: 5px solid #0d6efd; /* Blue border for customer summary */
    }
    .table-responsive {
        max-height: 500px; /* Makes long tables scrollable */
    }
    .sub-header {
        font-size: 1.2rem;
        font-weight: 500;
        color: #343a40;
    }
</style>

<body class="act-customers-show">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            
            <!-- Success/Error Messages for Email Action -->
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

            <!-- Customer Details & Actions -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center p-3 gap-2">
                    <h1 class="h5 mb-0 d-flex align-items-center">
                        <i class="fa fa-user me-2"></i>
                        {{ $customer->name }} - Customer Ledger
                    </h1>
                    
                    {{-- UPDATED ACTIONS --}}
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('customers.exportLedger', $customer->id) }}" class="btn btn-sm btn-success">
                            <i class="fa fa-file-excel me-1"></i> Export Ledger
                        </a>
                        <form action="{{ route('customers.emailLedger', $customer->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to email this statement to {{ $customer->email }}?');" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-info text-white" {{ !$customer->email ? 'disabled' : '' }} title="{{ !$customer->email ? 'Customer has no email address' : 'Email statement to customer' }}">
                                <i class="fa fa-paper-plane me-1"></i> Email Ledger
                            </button>
                        </form>
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                    {{-- END OF UPDATED ACTIONS --}}

                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">GSTIN / PAN</dt>
                        <dd class="col-sm-9">{{ $customer->gst_number ?? 'N/A' }} / {{ $customer->pan_number ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $customer->email ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Phone Number</dt>
                        <dd class="col-sm-9">{{ $customer->phone ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Address</dt>
                        <dd class="col-sm-9">{!! nl2br(e($customer->city ? $customer->address .', '. $customer->city : $customer->address)) !!}</dd>
                    </dl>
                </div>
            </div>

            <!-- Account Statement Summary -->
            <div class="card shadow-sm border-0 mb-4 summary-card">
                <div class="card-body">
                    <h5 class="card-title">Account Summary</h5>
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="stat-box p-3">
                                <h6>Total Invoiced</h6>
                                <h4 class="fw-bold text-primary">₹{{ number_format($totalInvoiced, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box p-3">
                                <h6>Total Received (incl. TDS)</h6>
                                <h4 class="fw-bold text-success">₹{{ number_format($totalReceived, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box p-3">
                                <h6>Balance Due</h6>
                                <h4 class="fw-bold text-danger">₹{{ number_format($balanceDue, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Invoice History -->
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header">
                            <h5 class="mb-0 sub-header"><i class="fas fa-file-invoice-dollar me-2"></i>Invoice History</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice #</th>
                                            <th class="text-end">Total</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($customer->invoices->sortByDesc('issue_date') as $invoice)
                                            <tr>
                                                <td>{{ $invoice->issue_date ? $invoice->issue_date->format('d M, Y') : 'N/A' }}</td>
                                                <td>{{ $invoice->invoice_number }}</td>
                                                <td class="text-end">₹{{ number_format($invoice->total, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ match($invoice->payment_status) {'paid' => 'success', 'partially_paid' => 'info', default => 'warning'} }}">{{ str_replace('_', ' ', Str::title($invoice->payment_status)) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-primary" title="View Invoice">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center p-4">No invoice history found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header">
                            <h5 class="mb-0 sub-header"><i class="fas fa-hand-holding-usd me-2"></i>Payment History</h5>
                        </div>
                        <div class="card-body p-0">
                             <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Payment Date</th>
                                            <th class="text-end">Amount Received</th>
                                            <th class="text-end">TDS</th>
                                            <th>Notes / Bank</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($customer->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                                                <td class="text-end">₹{{ number_format($payment->amount, 2) }}</td>
                                                <td class="text-end">₹{{ number_format($payment->tds_amount, 2) }}</td>
                                                <td>
                                                    {{ $payment->notes }}
                                                    @if($payment->bank_name)
                                                        <small class="d-block text-muted">Bank: {{ $payment->bank_name }}</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center p-4">No payment history found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
@include('layout.footer')