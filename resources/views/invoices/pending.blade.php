@include('layout.header')
<body class="act-invoice">
    <div class="main-content-area">
        @if (session('success'))
            <div class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-2">{{ session('error') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning mt-2">{{ session('warning') }}</div>
        @endif
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h1 class="mb-0 text-white">Pending Invoices</h1>
                </div>
                <div class="card-body">
                    @if ($invoices->isEmpty())
                        <p>No pending or on-hold invoices found.</p>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr class="{{ $invoice->status === 'on_hold' ? 'table-warning' : '' }}">
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->customer->name }}</td>
                                        <td>{{ number_format($invoice->total, 2) }}</td>
                                        <td>
                                            <span class="badge 
                                                {{ $invoice->status === 'pending' ? 'badge-primary' : 
                                                   ($invoice->status === 'on_hold' ? 'badge-warning' : '') }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($invoice->status === 'pending')
                                                <form action="{{ route('invoices.approve', $invoice) }}" method="POST"
                                                    onsubmit="return confirm('Do you really want to approve this invoice?');"
                                                    style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Approve</button>
                                                </form>
                                            @else
                                                <button class="btn btn-success" disabled title="Cannot approve due to overdue receivables">Approve</button>
                                            @endif
                                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-info">View</a>
                                            <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
@include('layout.footer')