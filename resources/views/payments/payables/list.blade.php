@include('layout.header')

<body class="act-payments">
    <div class="main-content-area">
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h5 class="mb-2 mb-md-0">Payment History - Payables</h5>

                    <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                        <a href="{{ route('payables') }}" class="btn btn-light">Back to Payables</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Purchase Number</th>
                                    <th>Party</th>
                                    <th>Invoice Number</th>
                                    <th>Total Amount</th>
                                    <th>Amount Paid</th>
                                    <th>Remaining</th>
                                    <th>Payment Count</th>
                                    <th>Last Payment Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payables as $payable)
                                    <tr>
                                        <td>
                                            <strong>{{ $payable->purchaseEntry->purchase_number ?? 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $payable->party->name ?? 'N/A' }}</td>
                                        <td>{{ $payable->invoice_number ?? 'N/A' }}</td>
                                        <td class="text-end">₹{{ number_format($payable->amount, 2) }}</td>
                                        <td class="text-end">₹{{ number_format($payable->total_paid, 2) }}</td>
                                        <td class="text-end">₹{{ number_format($payable->remaining_amount, 2) }}</td>
                                        <td class="text-center">
                                            @if($payable->payment_count > 0)
                                                <span class="badge bg-info">{{ $payable->payment_count }}</span>
                                            @else
                                                <span class="badge bg-secondary">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php $lastPayment = $payable->payments->sortByDesc('payment_date')->first(); @endphp
                                            @if($lastPayment)
                                                {{ \Carbon\Carbon::parse($lastPayment->payment_date)->format('d M, Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($payable->payment_status === 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($payable->payment_status === 'partially_paid')
                                                <span class="badge bg-warning">Partially Paid</span>
                                            @else
                                                <span class="badge bg-secondary">Unpaid</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($payable->payments->count() > 0)
                                                <a href="{{ route('payables.show', $payable->id) }}" class="btn btn-sm btn-outline-primary">View more</a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center p-4">No payment entries found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
@include('layout.footer')