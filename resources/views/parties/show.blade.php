
@include('layout.header')

<style>
    .summary-card {
        border-left: 5px solid #0d6efd;
    }
    .table-responsive {
        max-height: 500px; /* Or any height you prefer */
    }
</style>

<body class="act-parties-show">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Party Details & Actions -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center p-3 gap-2">
                    <h1 class="h5 mb-0 d-flex align-items-center">
                        <i class="fa fa-user-tie me-2"></i>
                        {{ $party->name }} - Ledger
                    </h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('parties.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Back to List
                        </a>
                        <a href="{{ route('parties.edit', $party) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-edit me-1"></i> Edit Party
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">GSTIN</dt>
                        <dd class="col-sm-9">{{ $party->gst_in ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $party->email ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Phone Number</dt>
                        <dd class="col-sm-9">{{ $party->phone_number ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Address</dt>
                        <dd class="col-sm-9">{!! nl2br(e($party->address ?? 'N/A')) !!}</dd>
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
                                <h6>Total Billed</h6>
                                <h4 class="fw-bold text-primary">₹{{ number_format($totalBilled, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box p-3">
                                <h6>Total Paid</h6>
                                <h4 class="fw-bold text-success">₹{{ number_format($totalPaid, 2) }}</h4>
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
                <!-- Purchase History -->
                <div class="col-lg-12 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Purchase History</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice #</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($party->purchaseEntries as $entry)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($entry->purchase_date)->format('d-m-Y') }}</td>
                                                <td>{{ $entry->invoice_number }}</td>
                                                <td>₹{{ number_format($entry->items->sum('total_price'), 2) }}</td>
                                                <td>
                                                    @if($entry->payable && $entry->payable->is_paid)
                                                        <span class="badge bg-success">Paid</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">Unpaid</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('purchase_entries.show', $entry->id) }}" class="btn btn-sm btn-outline-info" title="View Entry">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center p-4">No purchase history found.</td>
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
                            <h5 class="mb-0"><i class="fas fa-money-check-alt me-2"></i>Payment History</h5>
                        </div>
                        <div class="card-body p-0">
                             <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Payment Date</th>
                                            <th>Amount Paid</th>
                                            <th>Notes / Bank</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($party->payments as $payment)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                                                <td>₹{{ number_format($payment->amount, 2) }}</td>
                                                <td>
                                                    {{ $payment->notes }}
                                                    @if($payment->bank_name)
                                                        <small class="d-block text-muted">Bank: {{ $payment->bank_name }}</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center p-4">No payment history found.</td>
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