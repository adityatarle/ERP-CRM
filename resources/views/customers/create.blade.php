@include('layout.header')

<style>
    /* Page & Form Styling */
    body {
        background-color: #f4f7f9;
    }
    .main-content-area {
        min-height: 100vh;
        display: flex;
        align-items: center;
    }
    .customer-card {
        max-width: 900px;
        margin: auto;
        border: none;
    }
    .card-header h1 {
        font-size: 1.25rem;
        font-weight: 600;
    }
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    .section-divider {
        margin-top: 1.5rem;
        margin-bottom: 1.5rem;
        border-color: #dee2e6;
    }
    .section-title {
        font-size: 1.1rem;
        font-weight: 500;
        color: #0d6efd; /* Bootstrap primary color */
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
</style>

<body class="act-customers">
    <div class="main-content-area">
        <div class="container p-3 p-md-4">
            <div class="card shadow-sm w-100 customer-card">
                <div class="card-header bg-primary text-white p-3 d-flex align-items-center">
                    <i class="fa fa-user-plus fa-lg me-2"></i>
                    <h1 class="mb-0 h5">Add New Customer</h1>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <form action="{{ route('customers.store') }}" method="POST">
                        @csrf
                        
                        {{-- Contact Information Section --}}
                        <h6 class="section-title">Contact Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Customer Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                                @error('name')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                                @error('phone')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                                @error('email')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr class="section-divider">

                        {{-- Tax Information Section --}}
                        <h6 class="section-title">Tax Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="gst_number" class="form-label">GST Number</label>
                                <input type="text" name="gst_number" id="gst_number" class="form-control" value="{{ old('gst_number') }}">
                                @error('gst_number')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="pan_number" class="form-label">PAN Number</label>
                                <input type="text" name="pan_number" id="pan_number" class="form-control" value="{{ old('pan_number') }}">
                                @error('pan_number')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr class="section-divider">

                        {{-- Address Information Section --}}
                        <h6 class="section-title">Address Details</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="address" class="form-label">Street Address</label>
                                <textarea name="address" id="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                                @error('address')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                {{-- CORRECTED: The name is now 'city' --}}
                                <label for="city" class="form-label">City</label>
                                <input type="text" name="city" id="city" class="form-control" value="{{ old('city') }}">
                                @error('city')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                            </div>
                            {{-- You can add State and Pincode fields here in the future --}}
                             <div class="col-md-6">
                                {{-- Example for a State field --}}
                                <label for="state" class="form-label">State</label>
                                <input type="text" name="state" id="state" class="form-control" value="{{ old('state') }}">
                                @error('state')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        
                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check me-1"></i> Save Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
@include('layout.footer')