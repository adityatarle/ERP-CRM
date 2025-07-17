@include('layout.header')

<style>
    /* Add some basic styling for the filter section */
    .filter-card {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
</style>

<body class="act-purchaseentries">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                    <h1 class="mb-0 h5 text-white"><i class="fa fa-file-invoice me-2"></i>Purchase Entries</h1>
                    <a href="{{ route('purchase_entries.create') }}" class="btn btn-light btn-sm"><i class="fa fa-plus me-1"></i>Create Purchase Entry</a>
                </div>
                <div class="card-body p-3">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- SEARCH AND FILTER FORM -->
                    <div class="card filter-card p-3 mb-4">
                        <form action="{{ route('purchase_entries.index') }}" method="GET">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3"><label for="invoice_number" class="form-label">Invoice Number</label><input type="text" name="invoice_number" id="invoice_number" class="form-control" value="{{ $invoiceNumber ?? '' }}"></div>
                                <div class="col-md-3"><label for="party_name" class="form-label">Party Name</label><input type="text" name="party_name" id="party_name" class="form-control" value="{{ $partyName ?? '' }}"></div>
                                <div class="col-md-2"><label for="start_date" class="form-label">From Date</label><input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate ?? '' }}"></div>
                                <div class="col-md-2"><label for="end_date" class="form-label">To Date</label><input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate ?? '' }}"></div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search me-1"></i>Filter</button>
                                    <a href="{{ route('purchase_entries.index') }}" class="btn btn-secondary w-100 mt-1"><i class="fa fa-times me-1"></i>Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- THE FIX: Display the totals summary card if filters are active -->
                    @if(request()->hasAny(['invoice_number', 'party_name', 'start_date', 'end_date']))
                        <div class="card summary-card p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Filtered Results Summary</h6>
                                <h5 class="mb-0">Total Value: <span class="fw-bold text-primary">₹{{ number_format($filteredTotal, 2) }}</span></h5>
                            </div>
                        </div>
                    @endif
                    <!-- END OF FIX -->

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Purchase Number</th><th>Entry Date</th><th>Invoice Number</th>
                                    <th>Invoice Date</th><th>Party</th><th>Note</th>
                                    <th class="text-end">Total with GST</th><th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseEntries as $entry)
                                    <tr>
                                        <td class="text-primary fw-bold">{{ $entry->purchase_number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($entry->purchase_date)->format('d M, Y') }}</td>
                                        <td>{{ $entry->invoice_number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($entry->invoice_date)->format('d M, Y') }}</td>
                                        <td>{{ $entry->party->name ?? 'N/A' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($entry->note, 30, '...') ?? 'N/A' }}</td>
                                        <td class="text-end">₹{{ number_format($entry->items->sum('total_price'), 2) }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('purchase_entries.show', $entry->id) }}" class="btn btn-sm btn-info" title="View"><i class="fa fa-eye"></i></a>
                                            @if (Auth::check() && Auth::user()->role === 'superadmin')
                                                <a href="{{ route('purchase_entries.edit', $entry->id) }}" class="btn btn-sm btn-primary" title="Edit"><i class="fa fa-edit"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center p-4">No purchase entries found matching your criteria.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($purchaseEntries->hasPages())
                        <div class="card-footer bg-white">
                            {{ $purchaseEntries->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
@include('layout.footer')