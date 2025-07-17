@include('layout.header')

<style>
    body {
        background-color: #f8f9fa;
    }
    .main-content-area {
        min-height: 100vh;
        opacity: 1 !important;
        display: block !important;
    }
    #sales-form {
        display: block !important;
    }
    .quantity-error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
</style>

<body class="act-sales">
    <div class="main-content-area">
        @if (session('success'))
            <div class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif

        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h1 class="mb-0 text-white">Edit Sale</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('sales.update', $sale->id) }}" method="POST" id="sales-form">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control mb-5" required>
                                <option value="">Select Customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="ref_no" class="form-label">Ref. No (Challan Number)</label>
                            <input type="text" name="ref_no" id="ref_no" class="form-control"
                                placeholder="Enter challan number"
                                value="{{ old('ref_no', $sale->ref_no) }}">
                            @error('ref_no')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="product-search" class="form-label">Type to search products</label>
                            <input type="text" id="product-search" class="form-control"
                                placeholder="Search product name...">
                            <div id="product-suggestions" class="list-group position-absolute" style="z-index: 1000;">
                            </div>
                        </div>

                        <div id="selected-products" class="mt-4">
                            @foreach ($sale->saleItems as $index => $item)
                                <div class="row align-items-center mb-2" data-product-id="{{ $item->product_id }}">
                                    <div class="col-md-4">
                                        <input type="hidden" name="products[{{ $index }}][product_id]"
                                            value="{{ $item->product_id }}">
                                        <input type="text" class="form-control" value="{{ $item->product->name }}" disabled>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="products[{{ $index }}][quantity]" class="form-control quantity-input"
                                            placeholder="Qty" min="1" max="{{ $item->product->stock + $item->quantity }}"
                                            data-stock="{{ $item->product->stock + $item->quantity }}"
                                            value="{{ $item->quantity }}" required>
                                        <div class="quantity-error"></div>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="products[{{ $index }}][discount]" class="form-control"
                                            placeholder="Discount (%)" min="0" max="100" step="0.01"
                                            value="{{ $item->discount ?? 0 }}">
                                    </div>
                                    <div class="col-md-2 text-muted">
                                        Available: {{ $item->product->stock + $item->quantity }}
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-product-btn"
                                            data-product-id="{{ $item->product_id }}">
                                            ❌
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary mt-3" id="submit-btn">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load Bootstrap 5
        const bootstrapScript = document.createElement('script');
        bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
        bootstrapScript.onload = function () {
            console.log('Bootstrap JS loaded');
            initializeApp();
        };
        bootstrapScript.onerror = function () {
            console.error('Failed to load Bootstrap JS');
            showError('Failed to load Bootstrap. Some features may not work.');
        };
        document.body.appendChild(bootstrapScript);

        function initializeApp() {
            const products = @json($products);
            const searchInput = document.getElementById('product-search');
            const suggestionsBox = document.getElementById('product-suggestions');
            const selectedProductsDiv = document.getElementById('selected-products');
            const salesForm = document.getElementById('sales-form');
            const submitBtn = document.getElementById('submit-btn');

            let selectedProducts = @json($sale->saleItems->pluck('product_id')->toArray());
            let productStockMap = new Map();

            // Populate product stock map
            products.forEach(p => {
                const existingItem = @json($sale->saleItems)->find(item => item.product_id === p.id);
                const stock = existingItem ? p.stock + existingItem.quantity : p.stock;
                productStockMap.set(p.id, stock);
            });

            // Product Search
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase();
                suggestionsBox.innerHTML = '';
                suggestionsBox.style.display = 'none';

                if (query.length === 0) return;

                const filtered = products.filter(
                    p => p.name.toLowerCase().includes(query) && !selectedProducts.includes(p.id)
                );

                if (filtered.length > 0) {
                    filtered.forEach(product => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = `${product.name} (Stock: ${productStockMap.get(product.id)})`;
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

            // Add Product to Form
            function addProduct(product) {
                if (selectedProducts.includes(product.id)) return;
                selectedProducts.push(product.id);

                const index = selectedProducts.length - 1;

                const productGroup = document.createElement('div');
                productGroup.className = 'row align-items-center mb-2';
                productGroup.setAttribute('data-product-id', product.id);

                productGroup.innerHTML = `
                    <div class="col-md-4">
                        <input type="hidden" name="products[${index}][product_id]" value="${product.id}">
                        <input type="text" class="form-control" value="${product.name}" disabled>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="products[${index}][quantity]" class="form-control quantity-input" 
                            placeholder="Qty" min="1" max="${productStockMap.get(product.id)}" 
                            data-stock="${productStockMap.get(product.id)}" required>
                        <div class="quantity-error"></div>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="products[${index}][discount]" class="form-control" 
                            placeholder="Discount (%)" min="0" max="100" step="0.01" value="0">
                    </div>
                    <div class="col-md-2 text-muted">
                        Available: ${productStockMap.get(product.id)}
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-product-btn" 
                            data-product-id="${product.id}">
                            ❌
                        </button>
                    </div>
                `;

                selectedProductsDiv.appendChild(productGroup);
                updateSubmitButtonState();
            }

            // Real-Time Quantity Validation
            selectedProductsDiv.addEventListener('input', function (e) {
                if (e.target.classList.contains('quantity-input')) {
                    const input = e.target;
                    const quantity = parseFloat(input.value);
                    const stock = parseFloat(input.dataset.stock);
                    const errorDiv = input.nextElementSibling;

                    errorDiv.textContent = '';

                    if (isNaN(quantity) || quantity <= 0) {
                        errorDiv.textContent = 'Quantity must be greater than 0.';
                        submitBtn.disabled = true;
                    } else if (quantity > stock) {
                        errorDiv.textContent = `Quantity cannot exceed stock (${stock}).`;
                        submitBtn.disabled = true;
                    } else {
                        submitBtn.disabled = false;
                    }
                }
            });

            // Event Delegation for Remove Buttons
            selectedProductsDiv.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-product-btn');
                if (btn) {
                    const productId = parseInt(btn.dataset.productId);
                    removeProduct(productId);
                }
            });

            // Remove Product
            function removeProduct(productId) {
                selectedProducts = selectedProducts.filter(id => id !== productId);
                const productElement = selectedProductsDiv.querySelector(`div[data-product-id="${productId}"]`);
                if (productElement) {
                    productElement.remove();
                }
                reindexProducts();
                updateSubmitButtonState();
            }

            // Reindex Products
            function reindexProducts() {
                const productGroups = selectedProductsDiv.querySelectorAll('div[data-product-id]');
                productGroups.forEach((group, index) => {
                    const productId = group.dataset.productId;
                    group.querySelector('input[type="hidden"]').name = `products[${index}][product_id]`;
                    group.querySelector('.quantity-input').name = `products[${index}][quantity]`;
                    group.querySelector('input[name*="discount"]').name = `products[${index}][discount]`;
                });
            }

            // Validate All Quantities
            function validateQuantities() {
                let isValid = true;
                document.querySelectorAll('.quantity-error').forEach(el => el.textContent = '');
                document.querySelectorAll('.quantity-input').forEach(input => {
                    const quantity = parseFloat(input.value);
                    const stock = parseFloat(input.dataset.stock);
                    const errorDiv = input.nextElementSibling;
                    if (isNaN(quantity) || quantity <= 0) {
                        errorDiv.textContent = 'Quantity must be greater than 0.';
                        isValid = false;
                    } else if (quantity > stock) {
                        errorDiv.textContent = `Quantity cannot exceed stock (${stock}).`;
                        isValid = false;
                    }
                });
                return isValid;
            }

            // Update Submit Button State
            function updateSubmitButtonState() {
                const isValid = validateQuantities();
                submitBtn.disabled = !isValid;
            }

            // Sales Form Submission
            salesForm.addEventListener('submit', function (e) {
                e.preventDefault();
                document.querySelectorAll('.alert-danger').forEach(el => el.remove());

                if (!validateQuantities()) {
                    showError('Please fix quantity errors before submitting.');
                    return;
                }

                const formData = new FormData(this);

                fetch("{{ route('sales.update', $sale->id) }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showSuccess(data.message || 'Sale updated successfully!');
                        setTimeout(() => {
                            window.location.href = "{{ route('sales.index') }}";
                        }, 2000);
                    } else {
                        if (data.errors) {
                            for (const [field, messages] of Object.entries(data.errors)) {
                                showError(messages.join(', '));
                            }
                        } else {
                            showError(data.message || 'Failed to update sale.');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error submitting sale:', error);
                    showError('Failed to update sale: ' + error.message);
                });
            });

            function showError(message) {
                const errorMessage = document.createElement('div');
                errorMessage.className = 'alert alert-danger mt-2';
                errorMessage.textContent = message;
                document.querySelector('.main-content-area').prepend(errorMessage);
            }

            function showSuccess(message) {
                const successMessage = document.createElement('div');
                successMessage.className = 'alert alert-success mt-2';
                successMessage.textContent = message;
                document.querySelector('.main-content-area').prepend(successMessage);
            }

            // Initialize submit button state
            updateSubmitButtonState();
        }
    </script>
</body>
@include('layout.footer')