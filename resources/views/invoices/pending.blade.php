@include('layout.header')
<div class="container p-3 mx-auto">
<div class="card shadow-sm w-100">
        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
            <h1 class="mb-0 text-dark">Pending Invoices</h1>
        </div>
        <div class="card-body">
            @if ($invoices->isEmpty())
                <p>No pending invoices found.</p>
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
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->total }}</td>
                                <td>{{ $invoice->status }}</td>
                                <td>
                                <form action="{{ route('invoices.approve', $invoice) }}" method="POST" onsubmit="return confirm('Do you really want to approve this invoice?');">
                                    @csrf
                                    <button type="submit" class="btn btn-success" style="margin-bottom: -6px;">Approve</button>
                                </form>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-info" style="margin-left: 105px; margin-top: -45px;">View</a>
                                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning" style="margin-left: 15px; margin-top: -45px;">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@include('layout.footer')