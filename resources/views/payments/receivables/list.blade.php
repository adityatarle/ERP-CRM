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
            <div class="card-header bg-primary d-flex justify-content-between align-items-center flex-wrap text-white">
                <h5 class="mb-2 mb-md-0">Receivable Payments History</h5>
                <a href="{{ route('receivables') }}" class="btn btn-light">Back to Receivables</a>
            </div>
            <div class="card-body p-3">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('receivables.paymentsList') }}" class="mb-3 filter-form" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 col-sm-6">
                            <label for="tds_filter" class="form-label">TDS Filter</label>
                            <select name="tds_filter" id="tds_filter" class="form-control form-select" onchange="this.form.submit()">
                                <option value="all" {{ $tdsFilter == 'all' ? 'selected' : '' }}>All Records</option>
                                <option value="with_tds" {{ $tdsFilter == 'with_tds' ? 'selected' : '' }}>With TDS</option>
                                <option value="without_tds" {{ $tdsFilter == 'without_tds' ? 'selected' : '' }}>Without TDS</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('receivables.paymentsList', ['tds_filter' => 'all', 'sort_by' => 'payment_date', 'sort_dir' => 'desc']) }}" class="btn btn-secondary btn-sm btn-clear">Clear</a>
                        </div>
                        <!-- Hidden inputs to preserve sort parameters -->
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <input type="hidden" name="sort_dir" value="{{ $sortDir }}">
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'sale_ref_no', 'sort_dir' => $sortBy == 'sale_ref_no' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        Sale Reference
                                        @if($sortBy == 'sale_ref_no')
                                            <span class="sort-icon">{{ $sortDir == 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'customer_name', 'sort_dir' => $sortBy == 'customer_name' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        Customer
                                        @if($sortBy == 'customer_name')
                                            <span class="sort-icon">{{ $sortDir == 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'amount', 'sort_dir' => $sortBy == 'amount' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        Amount
                                        @if($sortBy == 'amount')
                                            <span class="sort-icon">{{ $sortDir == 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'tds_amount', 'sort_dir' => $sortBy == 'tds_amount' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        TDS
                                        @if($sortBy == 'tds_amount')
                                            <span class="sort-icon">{{ $sortDir == 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'payment_date', 'sort_dir' => $sortBy == 'payment_date' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        Payment Date
                                        @if($sortBy == 'payment_date')
                                            <span class="sort-icon">{{ $sortDir == 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'bank_name', 'sort_dir' => $sortBy == 'bank_name' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        Bank Name
                                        @if($sortBy == 'bank_name')
                                            <span class="sort-icon">{{ $sortDir == 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'notes', 'sort_dir' => $sortBy == 'notes' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        Notes
                                        @if($sortBy == 'notes')
                                            <span class="sort-icon">{{ $sortDir == 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>{{ $payment->sale->ref_no ?? 'Sale #' . $payment->sale_id }}</td>
                                    <td>{{ $payment->customer->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ number_format($payment->tds_amount, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                                    <td>{{ $payment->bank_name ?? 'N/A' }}</td>
                                    <td>{{ $payment->notes ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No payments found.</td>
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