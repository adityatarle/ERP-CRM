@include('layout.header')

<body class="act-invoice"> {{-- Or a more specific class if you have one --}}
    <div class="main-content-area">
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Manage Edit Unlock Request for Invoice #{{ $invoice->invoice_number }}</h5>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
                        <p><strong>Customer:</strong> {{ $invoice->customer->name ?? 'N/A' }}</p>
                        <p><strong>Current Status:</strong> <span class="badge bg-info text-dark">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span></p>
                        <p><strong>Requested By:</strong> {{ $invoice->requester->name ?? 'N/A' }} ({{ $invoice->requester->email ?? '' }})</p>
                        <p><strong>Request Date:</strong> {{ $invoice->updated_at->format('d-M-Y H:i A') }} (when status became pending_unlock)</p>
                        <p class="mb-1"><strong>Request Reason:</strong></p>
                        <div class="p-2 border rounded bg-light" style="white-space: pre-wrap;">{{ $invoice->unlock_reason }}</div>
                    </div>

                    <hr>

                    <form action="{{ route('invoices.decide_unlock_request', $invoice->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="decision" class="form-label"><strong>Decision:</strong></label>
                            <select name="decision" id="decision" class="form-select @error('decision') is-invalid @enderror" required>
                                <option value="">-- Select Decision --</option>
                                <option value="approve" {{ old('decision') == 'approve' ? 'selected' : '' }}>Approve Unlock</option>
                                <option value="reject" {{ old('decision') == 'reject' ? 'selected' : '' }}>Reject Unlock</option>
                            </select>
                            @error('decision')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="decision_reason" class="form-label">Reason for Decision (Optional for Approve, <strong>Required for Reject</strong>):</label>
                            <textarea name="decision_reason" id="decision_reason" class="form-control @error('decision_reason') is-invalid @enderror" rows="3">{{ old('decision_reason') }}</textarea>
                            @error('decision_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success"><i class="fa fa-check-circle"></i> Submit Decision</button>
                            <a href="{{ route('invoices.pending_unlock_requests') }}" class="btn btn-secondary"><i class="fa fa-list"></i> Back to Pending Requests</a>
                            <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-info"><i class="fa fa-eye"></i> View Invoice</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

@include('layout.footer')