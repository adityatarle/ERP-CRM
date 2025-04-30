@include('layout.header')

<div class="container">
    <h1>Payment Entries</h1>
    <a href="{{ route('payments.index') }}" class="btn btn-info">Back to Payables</a>
    <table class="table">
        <thead>
            <tr>
                <th>Payment Date</th>
                <th>Purchase Number</th>
                <th>Party</th>
                <th>Amount</th>
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

@include('layout.footer')