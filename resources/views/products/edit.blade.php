@include('layout.header')
<body class="act-product">
    <div class="main-content-area">
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
                            <input type="text" name="name" id="name" class="form-control"
                                value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" name="category" id="category" class="form-control"
                                value="{{ old('category', $product->category) }}" required>
                            @error('category')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="subcategory" class="form-label">Sub-Category</label>
                            <input type="text" name="subcategory" id="subcategory" class="form-control"
                                value="{{ old('subcategory', $product->subcategory) }}">
                            @error('subcategory')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" name="price" id="price" class="form-control" step="0.01" min="0"
                                value="{{ old('price', $product->price) }}" required>
                            @error('price')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="hsn" class="form-label">HSN Code</label>
                            <input type="text" name="hsn" id="hsn" class="form-control"
                                value="{{ old('hsn', $product->hsn) }}">
                            @error('hsn')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="item_code" class="form-label">Item Code</label>
                            <input type="text" name="item_code" id="item_code" class="form-control" 
                                value="{{ old('item_code', $product->item_code) }}" required>
                            @error('item_code')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- <div class="mb-3">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" name="stock" id="stock" class="form-control" min="0"
                                value="{{ old('stock', $product->stock) }}" required>
                            @error('stock')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div> -->
                        <div class="mb-3">
                            <label for="stock" class="form-label">Tally Stock</label>
                            <input type="number" name="stock" id="stock" class="form-control " min="0"
                                value="{{ old('stock',$product->stock) }}" required>
                            @error('stock')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="pstock" class="form-label">Physical Stock</label>
                            <input type="number" name="pstock" id="pstock" class="form-control " min="0"
                                value="{{ old('pstock',$product->pstock) }}" required>
                            @error('pstock')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="qty" class="form-label">Challan qty</label>
                            <input type="number" name="qty" id="qty" class="form-control " min="0"
                                value="{{ old('qty',$product->qty) }}" required>
                            @error('qty')
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
    </div>
</body>
@include('layout.footer')