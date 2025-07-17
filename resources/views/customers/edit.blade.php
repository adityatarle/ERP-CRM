@include('layout.header')
<body class="act-customers">
    <div class="main-content-area">
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
                            <input type="text" name="name" id="name" class="form-control mb-5" style="font-size:15px;"
                                value="{{ old('name', $customer->name) }}" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control mb-5"
                                style="font-size:15px;" value="{{ old('email', $customer->email) }}" required>
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control mb-5" style="font-size:15px;"
                                value="{{ old('phone', $customer->phone) }}">
                            @error('phone')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="gst_number" class="form-label">GST Number</label>
                            <input type="text" name="gst_number" id="gst_number" class="form-control"
                                value="{{ old('gst_number', $customer->gst_number) }}">
                            @error('gst_number')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="pan_number" class="form-label">PAN Number</label>
                            <input type="text" name="pan_number" id="pan_number" class="form-control mb-5"
                                value="{{ old('pan_number', $customer->pan_number) }}">
                            @error('pan_number')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea name="address" id="address" class="form-control mb-5"
                                style="font-size:15px;">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" name="city" id="city"
                                class="form-control" value="{{ old('city',$customer->city) }}">
                            @error('city')
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
    </div>
</body>
@include('layout.footer')