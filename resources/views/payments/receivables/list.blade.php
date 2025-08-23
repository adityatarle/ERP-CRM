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
                <a href="{{ route('receivables') }}" class="btn btn-light btn-sm"><i class="fa fa-arrow-left me-1"></i>Back to Receivables</a>
            </div>
            <div class="card-body p-3">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('receivables.paymentsList') }}" class="mb-4 filter-form" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 col-sm-6">
                            <label for="tds_filter" class="form-label small">Filter by TDS</label>
                            <select name="tds_filter" id="tds_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all" {{ $tdsFilter == 'all' ? 'selected' : '' }}>All Records</option>
                                <option value="with_tds" {{ $tdsFilter == 'with_tds' ? 'selected' : '' }}>With TDS</option>
                                <option value="without_tds" {{ $tdsFilter == 'without_tds' ? 'selected' : '' }}>Without TDS</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('receivables.paymentsList') }}" class="btn btn-outline-secondary btn-sm w-100">Clear Filter & Sort</a>
                        </div>
                        <!-- Hidden inputs to preserve sort parameters during filtering -->
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                        <input type="hidden" name="sort_dir" value="{{ $sortDir }}">
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    {{-- Replaced 'sale_ref_no' with 'invoice_number' --}}
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'invoice_number', 'sort_dir' => $sortBy == 'invoice_number' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        Invoice #
                                        @if($sortBy == 'invoice_number') <i class="fa fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i> @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'customer_name', 'sort_dir' => $sortBy == 'customer_name' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        Customer
                                        @if($sortBy == 'customer_name') <i class="fa fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i> @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'amount', 'sort_dir' => $sortBy == 'amount' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        Amount Paid
                                        @if($sortBy == 'amount') <i class="fa fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i> @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'tds_amount', 'sort_dir' => $sortBy == 'tds_amount' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        TDS
                                        @if($sortBy == 'tds_amount') <i class="fa fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i> @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'payment_date', 'sort_dir' => $sortBy == 'payment_date' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        Payment Date
                                        @if($sortBy == 'payment_date') <i class="fa fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i> @endif
                                    </a>
                                </th>
                                {{-- NEW COLUMNS ADDED HERE --}}
                                <th>Credit Days</th>
                                <th>
                                    <a href="{{ route('receivables.paymentsList', array_merge(request()->query(), ['sort_by' => 'due_date', 'sort_dir' => $sortBy == 'due_date' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}">
                                        Invoice Due
                                        @if($sortBy == 'due_date') <i class="fa fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }}"></i> @endif
                                    </a>
                                </th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                @php
                                    // Calculate credit days if possible
                                    $creditDays = 'N/A';
                                    if ($payment->invoice && $payment->invoice->issue_date && $payment->invoice->due_date) {
                                        $creditDays = \Carbon\Carbon::parse($payment->invoice->issue_date)->diffInDays(\Carbon\Carbon::parse($payment->invoice->due_date));
                                    }
                                @endphp
                                <tr>
                                    {{-- Updated to show Invoice Number --}}
                                    <td>
                                        <a href="{{ $payment->invoice ? route('invoices.show', $payment->invoice->id) : '#' }}" class="fw-bold">
                                            {{ $payment->invoice->invoice_number ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>{{ $payment->customer->name ?? 'N/A' }}</td>
                                    <td class="text-end">₹{{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($payment->tds_amount, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}</td>
                                    
                                    {{-- NEW COLUMNS' DATA --}}
                                    <td class="text-center">{{ $creditDays }}</td>
                                    <td>
                                        @if($payment->invoice && $payment->invoice->due_date)
                                            {{ \Carbon\Carbon::parse($payment->invoice->due_date)->format('d M, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $payment->notes ?? 'N/A' }}</td>
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
