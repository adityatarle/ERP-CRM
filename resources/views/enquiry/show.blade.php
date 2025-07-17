@include('layout.header')

<style>
    /* Page & Card Styling */
    body {
        background-color: #f4f7f9;
    }
    .main-content-area {
        min-height: 100vh;
    }
    .card-header h1 {
        font-size: 1.25rem;
        font-weight: 600;
    }
    .details-card {
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        padding: 1.5rem;
    }
    .details-card .label {
        color: #6c757d;
        width: 140px;
        display: inline-block;
        font-weight: 500;
    }
    .details-card p {
        margin-bottom: 0.75rem;
        font-size: 1rem;
        border-bottom: 1px solid #f1f3f5;
        padding-bottom: 0.75rem;
    }
    .details-card p:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    .sub-header {
        font-size: 1.2rem;
        font-weight: 500;
        color: #343a40;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
        border-left: 2px solid #e9ecef;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-icon {
        position: absolute;
        left: -41px;
        top: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #0d6efd;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .timeline-content {
        background-color: #f8f9fa;
        border-radius: .375rem;
        padding: 1rem;
    }
    .timeline-content .meta {
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>

<body class="act-enquiry">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white p-3">
                    <h1 class="mb-0 h5">
                        <i class="fa fa-question-circle me-2"></i>
                        Enquiry Details
                    </h1>
                    <a href="{{ route('enquiry.index') }}" class="btn btn-light btn-sm">
                        <i class="fa fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row g-4">
                        {{-- Left Column: Enquiry Info & Add Follow-up Form --}}
                        <div class="col-lg-5">
                            <div class="details-card mb-4">
                                <h6 class="sub-header">Enquiry Information</h6>
                                <p><span class="label">Customer Name:</span> <strong>{{ $enquiry->customer_name }}</strong></p>
                                <p><span class="label">Contact Number:</span> {{ $enquiry->contact_number }}</p>
                                <p><span class="label">Email:</span> {{ $enquiry->email ?? 'N/A' }}</p>
                                <p><span class="label">Status:</span> <strong>{{ ucfirst(str_replace('_', ' ', $enquiry->status)) }}</strong></p>
                                <p><span class="label">Received:</span> {{ $enquiry->created_at->format('d M, Y h:i A') }}</p>
                                <p><span class="label">Details:</span> {{ $enquiry->details }}</p>
                            </div>

                            <div class="details-card">
                                <h6 class="sub-header">Add Follow-Up</h6>
                                <form action="{{ route('enquiry.follow-up', $enquiry) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="follow_up_date" class="form-label">Next Follow-Up Date</label>
                                        <input type="date" name="follow_up_date" id="follow_up_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                        @error('follow_up_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="e.g., Called customer, sent quote, meeting scheduled...">{{ old('notes') }}</textarea>
                                        @error('notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Update Enquiry Status</label>
                                        <select name="status" id="status" class="form-select" required>
                                            <option value="new" {{ old('status', $enquiry->status) == 'new' ? 'selected' : '' }}>New</option>
                                            <option value="in_progress" {{ old('status', $enquiry->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="resolved" {{ old('status', $enquiry->status) == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                            <option value="closed" {{ old('status', $enquiry->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                        </select>
                                        @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fa fa-plus me-1"></i> Add Follow-Up
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Right Column: Follow-up History Timeline --}}
                        <div class="col-lg-7">
                            <h6 class="sub-header">Follow-Up History</h6>
                            @if ($enquiry->followUps->isEmpty())
                                <div class="alert alert-light text-center">No follow-ups have been recorded yet.</div>
                            @else
                                <div class="timeline">
                                    @foreach ($enquiry->followUps->sortByDesc('created_at') as $followUp)
                                        <div class="timeline-item">
                                            <div class="timeline-icon">
                                                <i class="fa fa-phone"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <p class="mb-2"><strong>Notes:</strong> {{ $followUp->notes ?? 'N/A' }}</p>
                                                <div class="meta d-flex justify-content-between">
                                                    <span>
                                                        By: <strong>{{ $followUp->user->name ?? 'N/A' }}</strong> | Status set to: <strong>{{ ucfirst(str_replace('_', ' ', $followUp->status)) }}</strong>
                                                    </span>
                                                    <span>
                                                        <i class="fa fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($followUp->follow_up_date)->format('d M, Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

@include('layout.footer')