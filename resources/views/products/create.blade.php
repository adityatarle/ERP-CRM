@include('layout.header')
<style>
    body {
        background-color: #f8f9fa;
    }

    .card {
        border-radius: 10px;
        transition: transform 0.2s;
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


<div class="container p-3 mx-auto">
    <div class="card shadow-sm w-100">
        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add New Product</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control " value="{{ old('name') }}" required>
                    @error('name')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-control " required style="font-size: 13px;">
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                        @endforeach
                    </select>
                    @error('category')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="subcategory" class="form-label">Sub-Category</label>
                    <select name="subcategory" id="subcategory" class="form-control " style="font-size: 13px;">
                        <option value="">Select Sub-Category</option>
                        @foreach ($subcategories as $subcategory)
                        <option value="{{ $subcategory }}" {{ old('subcategory') == $subcategory ? 'selected' : '' }}>
                            {{ $subcategory }}
                        </option>
                        @endforeach
                    </select>
                    @error('subcategory')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" name="price" id="price" class="form-control " step="0.01" min="0" value="{{ old('price') }}" required>
                    @error('price')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="discount" class="form-label">Discount(%)</label>
                    <input type="number" name="discount" id="discount" class="form-control " step="0.01" min="0" value="{{ old('discount') }}" required>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Tally Stock</label>
                    <input type="number" name="stock" id="stock" class="form-control " min="0" value="{{ old('stock') }}" required>
                    @error('stock')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="pstock" class="form-label">Physical Stock</label>
                    <input type="number" name="pstock" id="pstock" class="form-control " min="0" value="{{ old('pstock') }}" required>
                    @error('pstock')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="qty" class="form-label">Challan qty</label>
                    <input type="number" name="qty" id="qty" class="form-control " min="0" value="{{ old('qty') }}" required>
                    @error('qty')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layout.footer')