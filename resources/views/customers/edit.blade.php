@include('layout.header')
<div class="container p-3 mx-auto">
<div class="card shadow-sm w-100">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Edit Customer</h1>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3 mt-5">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control mb-5" style="font-size:15px;" value="{{ old('name', $customer->name) }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control mb-5" style="font-size:15px;" value="{{ old('email', $customer->email) }}" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control mb-5" style="font-size:15px;" value="{{ old('phone', $customer->phone) }}">
                        @error('phone')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control mb-5" style="font-size:15px;">{{ old('address', $customer->address) }}</textarea>
                        @error('address')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
        .btn-outline-secondary {
            transition: background-color 0.3s, color 0.3s;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
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