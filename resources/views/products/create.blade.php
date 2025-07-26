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
    .product-card {
        max-width: 800px;
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
    .input-group-text {
        background-color: #e9ecef;
        font-weight: bold;
    }
    .form-check-label {
        cursor: pointer;
    }
</style>

<div class="main-content-area">
    <div class="container p-3 p-md-4">
        <div class="card shadow-sm w-100 product-card">
            <div class="card-header bg-primary text-white p-3 d-flex align-items-center">
                <i class="fa fa-laptop fa-lg me-2"></i>
                <h1 class="mb-0 h5 text-white">Add New Product</h1>
            </div>
            <div class="card-body p-4 p-lg-5">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    
                    {{-- Basic Information Section --}}
                    <h5>Basic Information</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category</label>
                            <select name="category" id="category" class="form-select" required>
                                <option value="" disabled selected>Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="subcategory" class="form-label">Sub-Category</label>
                            <select name="subcategory" id="subcategory" class="form-select">
                                <option value="" disabled selected>Select Sub-Category (Optional)</option>
                                @foreach ($subcategories as $subcategory)
                                    <option value="{{ $subcategory }}" {{ old('subcategory') == $subcategory ? 'selected' : '' }}>
                                        {{ $subcategory }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subcategory')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="section-divider">

                    {{-- Tax & HSN Information Section --}}
                    <h5>Tax & HSN</h5>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="hsn" class="form-label">HSN Code</label>
                            <input type="text" name="hsn" id="hsn" class="form-control" value="{{ old('hsn') }}" placeholder="e.g., 8471">
                            @error('hsn')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="gst" class="form-label">GST Rate</label>
                            <div class="input-group">
                                <input type="number" name="gst" id="gst" class="form-control" step="0.01" min="0" max="100" value="{{ old('gst') }}" placeholder="e.g., 18">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('gst')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_taxable" id="is_taxable" value="1" {{ old('is_taxable', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_taxable">
                                    Is Taxable?
                                </label>
                            </div>
                            @error('is_taxable')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                            <i class="fa fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('layout.footer')