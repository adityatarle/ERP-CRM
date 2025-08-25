@include('layout.header')

<style>
    /* Page & Card Styling */
    body {
        background-color: #f4f7f9;
    }

    .main-content-area {
        min-height: 100vh;
    }

    .card-header h5 {
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

    .status-badge {
        padding: 0.4em 0.7em;
        font-size: 0.8rem;
        font-weight: 500;
        border-radius: 50rem;
    }

    .status-paid {
        background-color: rgba(25, 135, 84, 0.1);
        color: #0f5132;
    }

    .status-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: #664d03;
    }

    .status-unpaid {
        background-color: rgba(108, 117, 125, 0.1);
        color: #495057;
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }
</style>

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
                    <!-- Debug Information -->
                    @if(config('app.debug'))
                        <div class="alert alert-info mb-3">
                            <strong>Debug Info:</strong><br>
                            Total Payables: {{ $payables->count() }}<br>
                            @if($payables->count() > 0)
                                First Payable ID: {{ $payables->first()->id }}<br>
                                First Payable Amount: {{ $payables->first()->amount }}<br>
                                First Payable Total Paid: {{ $payables->first()->total_paid ?? 'N/A (using fallback)' }}<br>
                                First Payable Payment Count: {{ $payables->first()->payment_count ?? 'N/A (using fallback)' }}<br>
                                First Payable Payments Relationship Count: {{ $payables->first()->payments ? $payables->first()->payments->count() : 'N/A' }}<br>
                                First Payable Purchase Entry: {{ $payables->first()->purchaseEntry ? $payables->first()->purchaseEntry->purchase_number : 'N/A' }}<br>
                                First Payable Party: {{ $payables->first()->party ? $payables->first()->party->name : 'N/A' }}
                            @endif
                        </div>
                    @endif
                    
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
                                        <td class="text-end">₹{{ number_format($payable->total_paid ?? 0, 2) }}</td>
                                        <td class="text-end">₹{{ number_format($payable->remaining_amount ?? $payable->amount, 2) }}</td>
                                        <td class="text-center">
                                            @if(($payable->payment_count ?? 0) > 0)
                                                <span class="badge bg-info">{{ $payable->payment_count }}</span>
                                            @else
                                                <span class="badge bg-secondary">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($payable->payments && $payable->payments->count() > 0)
                                                {{ \Carbon\Carbon::parse($payable->payments->first()->payment_date)->format('d M, Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $status = 'unpaid';
                                                if (($payable->total_paid ?? 0) >= $payable->amount) {
                                                    $status = 'paid';
                                                } elseif (($payable->total_paid ?? 0) > 0) {
                                                    $status = 'partially_paid';
                                                }
                                            @endphp
                                            @if($status === 'paid')
                                                <span class="status-badge status-paid">Paid</span>
                                            @elseif($status === 'partially_paid')
                                                <span class="status-badge status-pending">Partially Paid</span>
                                            @else
                                                <span class="status-badge status-unpaid">Unpaid</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center p-4">No payable entries found.</td>
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