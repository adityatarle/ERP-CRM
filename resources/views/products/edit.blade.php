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
        padding: 2rem 0;
    }
    .product-card {
        max-width: 900px; /* Wider card for better layout */
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
        margin-top: 2rem;
        margin-bottom: 2rem;
        border-color: #dee2e6;
    }
    .input-group-text {
        background-color: #e9ecef;
    }
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #0d6efd; /* Primary color */
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #0d6efd;
    }
</style>

<body class="act-product">
    <div class="main-content-area">
        <div class="container p-3 p-md-4">
            <div class="card shadow-sm w-100 product-card">
                <div class="card-header bg-primary text-white p-3 d-flex align-items-center">
                    <i class="fa fa-edit fa-lg me-2"></i>
                    <h1 class="mb-0 h5 text-white">Edit Product</h1>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <form action="{{ route('products.update', $product->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Section 1: Basic Information --}}
                        <h2 class="section-title">Basic Information</h2>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                                @error('name') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" name="category" id="category" class="form-control" value="{{ old('category', $product->category) }}" required>
                                @error('category') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="subcategory" class="form-label">Sub-Category</label>
                                <input type="text" name="subcategory" id="subcategory" class="form-control" value="{{ old('subcategory', $product->subcategory) }}">
                                @error('subcategory') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="section-divider">

                        {{-- Section 2: Pricing & Codes --}}
                        <h2 class="section-title">Pricing & Codes</h2>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="price" class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" value="{{ old('price', $product->price) }}" required>
                                </div>
                                @error('price') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="hsn" class="form-label">HSN Code (Optional)</label>
                                <input type="text" name="hsn" id="hsn" class="form-control" value="{{ old('hsn', $product->hsn) }}">
                                @error('hsn') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="item_code" class="form-label">Item Code (Optional)</label>
                                {{-- THE FIX: Removed 'required' attribute --}}
                                <input type="text" name="item_code" id="item_code" class="form-control" value="{{ old('item_code', $product->item_code) }}">
                                @error('item_code') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="section-divider">

                        {{-- Section 3: Stock Information --}}
                        <h2 class="section-title">Stock Information</h2>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="stock" class="form-label">Tally Stock (Optional)</label>
                                {{-- THE FIX: Removed 'required' attribute --}}
                                <input type="number" name="stock" id="stock" class="form-control" min="0" value="{{ old('stock', $product->stock) }}">
                                @error('stock') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="pstock" class="form-label">Physical Stock (Optional)</label>
                                {{-- THE FIX: Removed 'required' attribute --}}
                                <input type="number" name="pstock" id="pstock" class="form-control" min="0" value="{{ old('pstock', $product->pstock) }}">
                                @error('pstock') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="qty" class="form-label">Challan Quantity (Optional)</label>
                                {{-- THE FIX: Removed 'required' attribute --}}
                                <input type="number" name="qty" id="qty" class="form-control" min="0" value="{{ old('qty', $product->qty) }}">
                                @error('qty') <div class="text-danger mt-1 small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check me-1"></i> Update Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
@include('layout.footer')