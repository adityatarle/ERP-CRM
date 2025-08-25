@include('layout.header')

<style>
    /* Table Styling */
    .table th, .table td {
        vertical-align: middle;
    }
    .table th a {
        color: inherit;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .table th a:hover {
        text-decoration: underline;
    }
    .sort-icon {
        font-size: 0.8rem;
    }
    .main-content-area {
        background-color: #f8f9fa;
    }
    /* Filter Form Styling */
    .filter-form .form-label {
        font-weight: 500;
        color: #495057;
    }
    .filter-form .btn-clear {
        height: calc(2.25rem + 2px); /* Match select height */
        line-height: 1.5;
    }
</style>

<div class="main-content-area">
    <div class="container p-3 mx-auto">
        <div class="card shadow-sm w-100">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h5 class="mb-2 mb-md-0">Payment History - Receivables</h5>

                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    <a href="{{ route('receivables') }}" class="btn btn-light">Back to Receivables</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice Number</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                                <th>Amount Paid</th>
                                <th>Remaining</th>
                                <th>Payment Count</th>
                                <th>Last Payment Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="fw-bold">
                                            {{ $invoice->invoice_number ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>{{ $invoice->customer->name ?? 'N/A' }}</td>
                                    <td class="text-end">₹{{ number_format($invoice->total, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($invoice->amount_paid, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($invoice->remaining_amount, 2) }}</td>
                                    <td class="text-center">
                                        @if($invoice->payment_count > 0)
                                            <span class="badge bg-info">{{ $invoice->payment_count }}</span>
                                        @else
                                            <span class="badge bg-secondary">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->payments->count() > 0)
                                            {{ \Carbon\Carbon::parse($invoice->payments->first()->payment_date)->format('d M, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->payment_status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($invoice->payment_status === 'partially_paid')
                                            <span class="badge bg-warning">Partially Paid</span>
                                        @else
                                            <span class="badge bg-secondary">Unpaid</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center p-4">No payments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layout.footer')
