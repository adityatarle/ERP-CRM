@include('layout.header')

@if (session('success'))
<div class="alert alert-success mt-2">{{ session('success') }}</div>
@endif

<div class="container p-3 mx-auto">
    <div class="card shadow-sm w-100">
        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
            <h1 class="mb-0">Record Sale</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('sales.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select name="customer_id" id="customer_id" class="form-control mb-5" required>
                        <option value="">Select Customer</option>
                        @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="product-search" class="form-label">Type to search products</label>
                    <input type="text" id="product-search" class="form-control" placeholder="Search product name...">
                    <div id="product-suggestions" class="list-group position-absolute" style="z-index: 1000;"></div>
                </div>

                <div id="selected-products" class="mt-4"></div>

                <button type="submit" class="btn btn-primary mt-3">Save</button>
            </form>
        </div>
    </div>
</div>
<script>
    const products = @json($products);
    const searchInput = document.getElementById('product-search');
    const suggestionsBox = document.getElementById('product-suggestions');
    const selectedProductsDiv = document.getElementById('selected-products');

    let selectedProducts = [];

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        suggestionsBox.innerHTML = '';
        suggestionsBox.style.display = 'none';

        if (query.length === 0) {
            return;
        }

        const filtered = products.filter(
            (p) => p.name.toLowerCase().includes(query) && !selectedProducts.includes(p.id)
        );

        if (filtered.length > 0) {
            filtered.forEach((product) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.textContent = `${product.name} (Stock: ${product.stock})`; // Fixed template literal
                item.addEventListener('click', function () {
                    addProduct(product);
                    searchInput.value = '';
                    suggestionsBox.innerHTML = '';
                    suggestionsBox.style.display = 'none';
                });
                suggestionsBox.appendChild(item);
            });
            suggestionsBox.style.display = 'block';
        }
    });

    function addProduct(product) {
        if (selectedProducts.includes(product.id)) {
            return;
        }
        selectedProducts.push(product.id);

        const index = selectedProducts.length - 1; // Use index to avoid duplicate ID conflicts

        const productGroup = document.createElement('div');
        productGroup.className = 'row align-items-center mb-2';
        productGroup.setAttribute('data-product-id', product.id);

        productGroup.innerHTML = `
            <div class="col-md-5">
                <input type="hidden" name="products[${index}][product_id]" value="${product.id}">
                <input type="text" class="form-control" value="${product.name}" disabled>
            </div>
            <div class="col-md-3">
                <input type="number" name="products[${index}][quantity]" class="form-control" placeholder="Qty" min="1" max="${product.stock}" required>
            </div>
            <div class="col-md-2 text-muted">
                Available: ${product.stock}
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeProduct(${product.id})">
                    ❌
                </button>
            </div>
        `;

        selectedProductsDiv.appendChild(productGroup);
    }

    function removeProduct(productId) {
        selectedProducts = selectedProducts.filter((id) => id !== productId);
        const productElement = selectedProductsDiv.querySelector(`div[data-product-id="${productId}"]`); // Fixed selector
        if (productElement) {
            productElement.remove();
        }
    }
</script>

@include('layout.footer')