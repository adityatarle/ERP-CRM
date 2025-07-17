@include('layout.header')

<body class="act-enquiry">
    <div class="main-content-area">
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary">
                    <h1 class="mb-0">Edit Enquiry</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('enquiry.update', $enquiry) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{ old('customer_name', $enquiry->customer_name) }}" required>
                            @error('customer_name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" id="contact_number" class="form-control" value="{{ old('contact_number', $enquiry->contact_number) }}" required>
                            @error('contact_number')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email (Optional)</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $enquiry->email) }}">
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="details" class="form-label">Enquiry Details</label>
                            <textarea name="details" id="details" class="form-control" required>{{ old('details', $enquiry->details) }}</textarea>
                            @error('details')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="new" {{ old('status', $enquiry->status) == 'new' ? 'selected' : '' }}>New</option>
                                <option value="in_progress" {{ old('status', $enquiry->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ old('status', $enquiry->status) == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ old('status', $enquiry->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            @error('status')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Enquiry</button>
                            <a href="{{ route('enquiry.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

@include('layout.footer')