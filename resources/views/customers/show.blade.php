@include('layout.header')

<style>
    /* Add some specific styling for the ledger */
    .summary-card {
        border-left: 5px solid #198754; /* Green border for customer summary */
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
            
            <!-- Customer Details & Actions -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center p-3 gap-2">
                    <h1 class="h5 mb-0 d-flex align-items-center">
                        <i class="fa fa-user me-2"></i>
                        {{ $customer->name }} - Customer Ledger
                    </h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-edit me-1"></i> Edit Customer
                        </a>
                        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
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
                    <h5 class="card-title">Account Statement</h5>
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="stat-box p-3">
                                <h6>Total Invoiced</h6>
                                <h4 class="fw-bold text-primary">₹{{ number_format($totalInvoiced, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box p-3">
                                <h6>Total Received</h6>
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
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice #</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($customer->invoices as $invoice)
                                            <tr>
                                                <td>{{ $invoice->created_at->format('d-m-Y') }}</td>
                                                <td>{{ $invoice->invoice_number }}</td>
                                                <td>₹{{ number_format($invoice->total, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ match($invoice->status) {'paid' => 'success', 'on_hold' => 'danger', 'approved' => 'info', default => 'secondary'} }}">{{ ucfirst($invoice->status) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-info" title="View Invoice">
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
                                    <thead>
                                        <tr>
                                            <th>Payment Date</th>
                                            <th>Amount Received</th>
                                            <th>TDS</th>
                                            <th>Notes / Bank</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($customer->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->payment_date->format('d-m-Y') }}</td>
                                                <td>₹{{ number_format($payment->amount, 2) }}</td>
                                                <td>₹{{ number_format($payment->tds_amount, 2) }}</td>
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