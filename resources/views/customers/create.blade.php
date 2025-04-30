@include('layout.header')
<div class="container p-3 mx-auto">
<div class="card shadow-sm w-100">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Add New Customer</h1>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                    <label for="gst_number" class="form-label">GST Number</label>
                    <input type="text" name="gst_number" id="gst_number" class="form-control" value="{{ old('gst_number') }}">
                    @error('gst_number')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control mb-5">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Customer</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .form-label {
            font-weight: 500;
            color: #343a40;
        }
        .form-control {
            border-radius: 5px;
        }
        .text-danger {
            font-size: 0.9rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        @media (max-width: 767px) {
            .card {
                margin: 0 10px;
            }
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
    @include('layout.footer')