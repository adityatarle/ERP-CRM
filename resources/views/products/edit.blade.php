@include('layout.header')
<div class="container p-3 mx-auto">
<div class="card shadow-sm w-100">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Edit Product</h1>
            </div>
            <div class="card-body">
                <form action="{{ route('products.update', $product->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" name="category" id="category" class="form-control" value="{{ old('category', $product->category) }}" required>
                        @error('category')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="subcategory" class="form-label">Sub-Category</label>
                        <input type="text" name="subcategory" id="subcategory" class="form-control" value="{{ old('subcategory', $product->subcategory) }}">
                        @error('subcategory')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" value="{{ old('price', $product->price) }}" required>
                        @error('price')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="discount" class="form-label">Discount(%)</label>
                        <input type="number" name="discount" id="discount" class="form-control" step="0.01" min="0" value="{{ old('discount', $product->discount) }}" required>
                        @error('discount')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" name="stock" id="stock" class="form-control" min="0" value="{{ old('stock', $product->stock) }}" required>
                        @error('stock')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Product</button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@section('styles')
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
    </style>
@endsection
@include('layout.footer')