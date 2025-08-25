@include('layout.header')

<body class="act-payments">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex flex-column flex-md-row justify-content-between align-items-md-center text-white p-3">
                    <h1 class="mb-2 mb-md-0 h5 text-white">
                        <i class="fa fa-file-invoice-dollar me-2"></i> Payable Details
                    </h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('payments.payables.list') }}" class="btn btn-light btn-sm">
                            <i class="fa fa-arrow-left me-1"></i> Back to History
                        </a>
                        <a href="{{ route('payables') }}" class="btn btn-light btn-sm">
                            <i class="fa fa-list me-1"></i> Back to Payables
                        </a>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-3">Purchase Information</h6>
                                <p class="mb-1"><span class="text-muted">Purchase Number:</span> <strong>{{ $payable->purchaseEntry->purchase_number ?? 'N/A' }}</strong></p>
                                <p class="mb-1"><span class="text-muted">Invoice Number:</span> <strong>{{ $payable->invoice_number ?? 'N/A' }}</strong></p>
                                <p class="mb-0"><span class="text-muted">Invoice Date:</span> <strong>{{ $payable->invoice_date ? \Carbon\Carbon::parse($payable->invoice_date)->format('d M, Y') : 'N/A' }}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-3">Party & Status</h6>
                                <p class="mb-1"><span class="text-muted">Party:</span> <strong>{{ $payable->party->name ?? 'N/A' }}</strong></p>
                                <p class="mb-1">
                                    <span class="text-muted">Status:</span>
                                    @if($payable->payment_status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($payable->payment_status === 'partially_paid')
                                        <span class="badge bg-warning">Partially Paid</span>
                                    @else
                                        <span class="badge bg-secondary">Unpaid</span>
                                    @endif
                                </p>
                                <p class="mb-0"><span class="text-muted">Payments Count:</span> <strong>{{ $payable->payments->count() }}</strong></p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-1 text-muted">Total Amount</p>
                                        <h5 class="mb-0">₹{{ number_format($payable->amount + $payable->total_paid, 2) }}</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1 text-muted">Total Paid</p>
                                        <h5 class="mb-0 text-primary">₹{{ number_format($payable->total_paid, 2) }}</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1 text-muted">Remaining</p>
                                        <h5 class="mb-0 {{ $payable->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">₹{{ number_format($payable->remaining_amount, 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-end">Amount</th>
                                            <th>Bank/Method</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payable->payments as $payment)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}</td>
                                                <td class="text-end">₹{{ number_format($payment->amount, 2) }}</td>
                                                <td>{{ $payment->bank_name ?? 'N/A' }}</td>
                                                <td>{{ $payment->notes ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center p-4">No payments recorded for this purchase.</td>
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

