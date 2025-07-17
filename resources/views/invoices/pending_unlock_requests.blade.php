
@include('layout.header')


<div class="main-content-area"> {{-- Assuming this class is for your main content wrapper --}}
    <div class="container p-3 mx-auto">
        <div class="card shadow-sm w-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pending Invoice Unlock Requests</h5>
                {{-- Optional: Link back to all invoices or dashboard --}}
                <a href="{{ route('invoices.index') }}" class="btn btn-light btn-sm">All Invoices</a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('info'))
                    <div class="alert alert-info">{{ session('info') }}</div>
                @endif

                @if($pendingRequests->isEmpty())
                    <div class="alert alert-secondary text-center">
                        <i class="fa fa-info-circle me-2"></i>There are no pending unlock requests at this time.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Requested By</th>
                                    <th>Reason for Unlock</th>
                                    <th>Requested At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingRequests as $invoice)
                                    <tr>
                                        <td>
                                            <a href="{{ route('invoices.show', $invoice->id) }}">{{ $invoice->invoice_number }}</a>
                                            <br>
                                            <small class="text-muted">Status: {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</small>
                                        </td>
                                        <td>{{ $invoice->customer->name ?? 'N/A' }}</td>
                                        <td>{{ $invoice->requester->name ?? 'N/A' }}</td>
                                        <td title="{{ $invoice->unlock_reason }}">{{ Str::limit($invoice->unlock_reason, 50) }}</td>
                                        {{-- Assuming updated_at reflects the time the edit_request_status was set to pending_unlock --}}
                                        <td>{{ $invoice->updated_at->format('d-M-Y H:i A') }}</td>
                                        <td>
                                            <a href="{{ route('invoices.manage_unlock_request_form', $invoice->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-cogs me-1"></i> Manage
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination Links --}}
                    @if ($pendingRequests->hasPages())
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $pendingRequests->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>


@include('layout.footer')