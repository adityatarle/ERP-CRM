@include('layout.header')

<body class="act-parties-edit">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto" style="max-width: 700px;">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <p class="fw-bold">Please fix the following errors:</p>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('parties.update', $party) }}" method="POST">
                @csrf
                @method('PUT') {{-- Important: This tells Laravel to use the UPDATE route method --}}
                
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h1 class="h5 mb-0 d-flex align-items-center">
                            <i class="fa fa-edit me-2"></i>Edit Party: {{ $party->name }}
                        </h1>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Party Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $party->name) }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $party->email) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone_number" class="form-label fw-bold">Phone Number</label>
                                <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', $party->phone_number) }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="gst_in" class="form-label fw-bold">GSTIN</label>
                            <input type="text" class="form-control @error('gst_in') is-invalid @enderror" id="gst_in" name="gst_in" value="{{ old('gst_in', $party->gst_in) }}" maxlength="15">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label fw-bold">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $party->address) }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-end py-3">
                        <a href="{{ route('parties.show', $party) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> Update Party
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
@include('layout.footer')