@include('layout.header')


<div class="container-fluid px-3 px-md-4 py-4">
    <div class="container">
        <!-- Header Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body bg-primary text-white rounded">
                <h5 class="mb-0 py-2">Products</h5>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row align-items-center mb-4">
                    <!-- Search Bar -->
                    <div class="col-12 col-md-6 col-lg-5 mb-3 mb-md-0">
                        <form id="search-form" action="{{ route('products.index') }}" method="GET">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0"
                                    placeholder="Search by Product..."
                                    autocomplete="off"
                                    value="{{ $search ?? '' }}">
                            </div>
                        </form>
                    </div>
                    <!-- Action Buttons -->
                    <div class="col-12 col-md-6 col-lg-7 d-flex justify-content-md-end flex-wrap gap-2">
                        <a href="{{ route('products.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add Product
                        </a>
                        <a href="{{ route('products.export') }}" class="btn btn-success">
                            <i class="fas fa-file-export me-1"></i> Export
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i> View All
                        </a>
                    </div>
                </div>

                <!-- Import Form -->
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                    @csrf
                    <div class="input-group">
                        {{-- Add name="file" here --}}
                        <input class="form-control" type="file" name="file" accept=".xlsx,.xls,.csv" id="formFile" required>
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-file-import me-1"></i> Import Products
                        </button>
                    </div>
                    @error('file')
                    <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </form>

                <!-- Success/Error Messages -->
                @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>


<!-- Recent Sales Start -->
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Recent Sales</h6>
        </div>
        <div class="table-responsive" style="max-height: 500px;">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead class="sticky-top bg-light">
                    <tr class="text-dark">
                        <th>Name</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @if ($products->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No products found.</p>
                </div>
                @else
                    @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category }}</td>
                        <td>{{ $product->subcategory ?? 'N/A' }}</td>
                        <td>{{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->stock }}</td>
                        <td class="d-flex flex-nowrap gap-2">
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">
                                View
                            </a>
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning">
                                Edit
                            </a>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Recent Sales End -->

@include('layout.footer')