@include('layout.header')

<body class="act-payments">
    <div class="main-content-area">
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h5 class="mb-2 mb-md-0">Payment Entries</h5>

                    <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                        <a href="{{ route('payables') }}" class="btn btn-light">Back to Payables</a>
                    </div>
                </div>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Payment Date</th>
                            <th>Purchase Number</th>
                            <th>Party</th>
                            <th>Amount</th>
                            <th>Bank Name</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date }}</td>
                                <td>{{ $payment->purchaseEntry->purchase_number ?? 'N/A' }}</td>
                                <td>{{ $payment->party->name ?? 'N/A' }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->bank_name ?? 'N/A' }}</td>
                                <td>{{ $payment->notes ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No payment entries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
@include('layout.footer')