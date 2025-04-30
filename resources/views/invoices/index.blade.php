@include('layout.header')
@if (session('error'))
    <div class="alert alert-danger mt-2">{{ session('error') }}</div>
@endif
    <div class="container p-3 mx-auto">
        <div class="card shadow-sm w-100">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                <h5 class="mb-2 mb-md-0">Invoices</h5>
                <a href="{{ route('invoices.create') }}" class="btn btn-light">Create Invoice</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
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
                                <td class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-sm btn-success">Download PDF</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<style>
    @media (max-width: 576px) {
        .btn-sm {
            width: 100%;
        }
    }
        .card {
            border-radius: 10px;
        }

        .card-header {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        @media (max-width: 767px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .card-header h1 {
                font-size: 1.5rem;
            }

            .table thead {
                display: none;
            }

            .table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #dee2e6;
                border-radius: 0.5rem;
                padding: 0.75rem;
            }

            .table tbody td {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem 0;
                border: none;
            }

            .table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #495057;
            }
        }
    </style>


@include('layout.footer')

