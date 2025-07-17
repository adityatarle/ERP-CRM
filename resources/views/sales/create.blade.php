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
    #sales-form { /* This ID is still used by the JS, even if it's an invoice form */
        display: block !important;
    }
    .modal-backdrop {
        z-index: 1040; /* Ensure modal backdrop is below modal */
    }
    .modal {
        z-index: 1050; /* Ensure modal is above backdrop */
    }
    .quantity-error, .sale-price-error, .discount-error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    .header-row {
        background-color: #f0f2f5; /* Slightly different background for product list header */
        padding: 10px 15px;
        font-weight: 600;
        color: #4b5563;
        font-size: 0.9rem;
        margin-bottom: 10px;
        border-radius: 8px;
    }
    .stock-info {
        font-size: 0.85rem;
        color: #6b7280;
        margin-left: 8px;
    }
    .stock-below-product {
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 4px;
    }
    .input-group-text {
        background-color: #f1f3f5;
        border: 1px solid #d1d5db;
        border-radius: 8px 0 0 8px;
        padding: 10px;
        font-size: 0.95rem;
        color: #6b7280;
    }
    .selected-product-row {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }
    .form-label {
        font-weight: 500;
        color: #374151;
    }
    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }
    .btn-primary {
        background-color: #6366f1;
        border-color: #6366f1;
    }
    .btn-primary:hover {
        background-color: #4f46e5;
        border-color: #4f46e5;
    }
     /* General error message display */
    .alert-fixed-top {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1055; /* Above modals */
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>

<body class="act-invoices"> {{-- Updated class if you use it for styling --}}
    <div class="main-content-area">
        {{-- Session messages (e.g., from non-AJAX redirects, though less likely here) --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show alert-fixed-top" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show alert-fixed-top" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
         {{-- Dynamic messages will be added here by JS --}}
        <div id="dynamic-alerts-container"></div>


        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h1 class="mb-0 text-white">Create Invoice</h1>
                </div>
                <div class="card-body">
                    {{-- Form action still points to sales.store, which now handles invoice creation --}}
                    <form action="{{ route('sales.store') }}" method="POST" id="invoice-form"> {{-- Changed ID for clarity, update JS --}}
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-select" required>
                                    <option value="">Select Customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="customer_id-error" class="text-danger"></div>
                                @error('customer_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ref_no" class="form-label">Ref. No (Challan Number)</label>
                                <input type="text" name="ref_no" id="ref_no" class="form-control"
                                    placeholder="Enter challan number" value="{{ old('ref_no') }}">
                                <div id="ref_no-error" class="text-danger"></div>
                                @error('ref_no')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="gst" class="form-label">GST (%) <span class="text-danger">*</span></label>
                                <input type="number" name="gst" id="gst" class="form-control" step="0.01" min="0" max="100" value="{{ old('gst', 0) }}" required>
                                <div id="gst-error" class="text-danger"></div>
                                @error('gst')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="credit_days" class="form-label">Credit Days</label>
                                <input type="number" name="credit_days" id="credit_days" class="form-control" min="0" placeholder="Uses customer default if blank" value="{{ old('credit_days') }}">
                                <div id="credit_days-error" class="text-danger"></div>
                                @error('credit_days')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Invoice Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter invoice description" required>{{ old('description') }}</textarea>
                            <div id="description-error" class="text-danger"></div>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <hr class="my-4">

                        <div class="mb-3">
                            <label for="product-search" class="form-label">Search and Add Products</label>
                            <div class="input-group">
                                <input type="text" id="product-search" class="form-control" placeholder="Type product name...">
                                <button type="button" id="add-new-product-modal-btn" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <i class="bi bi-plus-circle"></i> Add New Product
                                </button>
                            </div>
                            <div id="product-suggestions" class="list-group position-absolute" style="z-index: 1000; width: calc(100% - 170px);"> {{-- Adjust width if needed --}}
                            </div>
                        </div>

                        <div id="selected-products" class="mt-4">
                            <!-- Header row will be added dynamically by JS if products are added -->
                        </div>

                        <div id="products-error" class="text-danger mb-3"></div> {{-- For general 'products array' errors --}}


                        <button type="submit" class="btn btn-success mt-3 w-100" id="submit-invoice-btn">
                            <i class="bi bi-file-earmark-check"></i> Create Invoice
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg"> {{-- Made modal larger --}}
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="add-product-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                                    <div id="name-error" class="text-danger"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="category" id="category" class="form-select" required>
                                        <option value="">Select Category</option>
                                        @if (!empty($categories))
                                            @foreach ($categories as $category)
                                                <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                                    {{ $category }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div id="category-error" class="text-danger"></div>
                                </div>
                            </div>
                             <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="subcategory" class="form-label">Sub-Category</label>
                                    <select name="subcategory" id="subcategory" class="form-select">
                                        <option value="">Select Sub-Category</option>
                                        @if (!empty($subcategories))
                                            @foreach ($subcategories as $subcategory)
                                                <option value="{{ $subcategory }}" {{ old('subcategory') == $subcategory ? 'selected' : '' }}>
                                                    {{ $subcategory }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div id="subcategory-error" class="text-danger"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Default Sale Price <span class="text-danger">*</span></label>
                                    <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" value="{{ old('price') }}" required>
                                    <div id="price-error" class="text-danger"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="barcode" class="form-label">Barcode</label>
                                    <input type="text" name="barcode" id="barcode" class="form-control" value="{{ old('barcode') }}">
                                    <div id="barcode-error" class="text-danger"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="hsn" class="form-label">HSN Code</label>
                                    <input type="text" name="hsn" id="hsn" class="form-control" value="{{ old('hsn') }}">
                                    <div id="hsn-error" class="text-danger"></div>
                                </div>
                            </div>
                             <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="stock" class="form-label">Tally Stock <span class="text-danger">*</span></label>
                                    <input type="number" name="stock" id="stock" class="form-control" min="0" value="{{ old('stock', 0) }}" required>
                                    <div id="stock-error" class="text-danger"></div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="pstock" class="form-label">Physical Stock <span class="text-danger">*</span></label>
                                    <input type="number" name="pstock" id="pstock" class="form-control" min="0" value="{{ old('pstock', 0) }}" required>
                                    <div id="pstock-error" class="text-danger"></div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="qty" class="form-label">Challan Qty <span class="text-danger">*</span></label> {{-- Note: 'qty' here might be confusing if it's initial stock --}}
                                    <input type="number" name="qty" id="qty" class="form-control" min="0" value="{{ old('qty', 0) }}" required>
                                    <div id="qty-error" class="text-danger"></div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-primary">Save Product</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Ensure Bootstrap 5 JS is loaded (as in your original script)
        // This is a simplified check, ideally, ensure it's loaded before initializeApp
        if (typeof bootstrap === 'undefined' || typeof bootstrap.Modal === 'undefined') {
            const bootstrapScript = document.createElement('script');
            bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
            bootstrapScript.onload = function () {
                console.log('Bootstrap JS loaded dynamically');
                initializeApp();
            };
            bootstrapScript.onerror = function () {
                console.error('Failed to load Bootstrap JS');
                showDynamicAlert('Failed to load Bootstrap. Some features may not work.', 'danger');
            };
            document.body.appendChild(bootstrapScript);
        } else {
            initializeApp();
        }


        function initializeApp() {
            const productsData = @json($products); // Renamed for clarity
            const searchInput = document.getElementById('product-search');
            const suggestionsBox = document.getElementById('product-suggestions');
            const selectedProductsDiv = document.getElementById('selected-products');
            const addProductForm = document.getElementById('add-product-form');
            // const addProductBtn = document.getElementById('add-new-product-modal-btn'); // Button to open modal
            const invoiceForm = document.getElementById('invoice-form'); // Updated form ID
            const submitInvoiceBtn = document.getElementById('submit-invoice-btn'); // Updated submit button ID
            const modalElement = document.getElementById('addProductModal');

            let liveProducts = [...productsData]; // Create a mutable copy
            let selectedProductIds = []; // Changed to selectedProductIds for clarity
            let productStockMap = new Map();
            liveProducts.forEach(p => productStockMap.set(p.id, p.stock));

            let modalInstance = null;
            if (modalElement) {
                try {
                    modalInstance = new bootstrap.Modal(modalElement);
                } catch (error) {
                    console.error('Error initializing modal:', error);
                    showDynamicAlert('Failed to initialize modal components.', 'danger');
                }
            }


            // Product Search
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                suggestionsBox.innerHTML = '';
                suggestionsBox.style.display = 'none';

                if (query.length === 0) return;

                const filtered = liveProducts.filter(
                    p => p.name.toLowerCase().includes(query) && !selectedProductIds.includes(p.id)
                );

                if (filtered.length > 0) {
                    filtered.forEach(product => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';
                        item.innerHTML = `${product.name} <span class="badge bg-secondary float-end">Stock: ${productStockMap.get(product.id)}</span>`;
                        item.addEventListener('click', function () {
                            addProductToForm(product);
                            searchInput.value = '';
                            suggestionsBox.innerHTML = '';
                            suggestionsBox.style.display = 'none';
                        });
                        suggestionsBox.appendChild(item);
                    });
                    suggestionsBox.style.display = 'block';
                }
            });
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', function(event) {
                if (!suggestionsBox.contains(event.target) && event.target !== searchInput) {
                    suggestionsBox.style.display = 'none';
                }
            });


            // Add Product to Form
            function addProductToForm(product) {
                if (selectedProductIds.includes(product.id)) {
                    showDynamicAlert(`Product "${product.name}" is already added.`, 'warning');
                    return;
                }
                selectedProductIds.push(product.id);

                // Add header row if this is the first product
                if (selectedProductIds.length === 1 && !selectedProductsDiv.querySelector('.header-row')) {
                    const headerRow = document.createElement('div');
                    headerRow.className = 'row header-row gx-2'; // Added gx-2 for gutter
                    headerRow.innerHTML = `
                        <div class="col-md-4">Product</div>
                        <div class="col-md-2">Quantity</div>
                        <div class="col-md-2">Sale Price</div>
                        <div class="col-md-2">Discount</div>
                        <div class="col-md-1">Barcode</div>
                        <div class="col-md-1 text-end">Action</div>
                    `;
                    selectedProductsDiv.prepend(headerRow);
                }

                const index = selectedProductIds.length - 1; // This index is for array naming, not strictly for display order if items are removed and re-added without re-indexing visually.

                const productGroup = document.createElement('div');
                productGroup.className = 'row align-items-center selected-product-row gx-2';
                productGroup.setAttribute('data-product-id', product.id);

                productGroup.innerHTML = `
                    <div class="col-md-4">
                        <input type="hidden" name="products[${index}][product_id]" value="${product.id}">
                        <input type="text" class="form-control form-control-sm" value="${product.name}" readonly disabled>
                        <div class="stock-below-product">Current Stock: ${productStockMap.get(product.id)}</div>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="products[${index}][quantity]" class="form-control form-control-sm quantity-input" 
                               placeholder="Qty" min="1" max="${productStockMap.get(product.id)}" data-stock="${productStockMap.get(product.id)}" required>
                        <div class="quantity-error"></div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="products[${index}][sale_price]" class="form-control sale-price-input" 
                                placeholder="Price" min="0" step="0.01" value="${product.price || 0}" required>
                        </div>
                        <div class="sale-price-error"></div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group input-group-sm">
                            <input type="number" name="products[${index}][discount]" class="form-control discount-input" 
                                placeholder="Discount" min="0" max="100" step="0.01" value="0">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="discount-error"></div>
                    </div>
                    <div class="col-md-1">
                        <input type="text" name="products[${index}][barcode]" class="form-control form-control-sm barcode-input" 
                            placeholder="Scan/Enter" value="${product.barcode || ''}">
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-product-btn" 
                            data-product-id="${product.id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;

                selectedProductsDiv.appendChild(productGroup);
                updateSubmitButtonState();
            }

            // Real-Time Validation for product rows
            selectedProductsDiv.addEventListener('input', function (e) {
                const target = e.target;
                let errorDiv;

                if (target.classList.contains('quantity-input')) {
                    const quantity = parseFloat(target.value);
                    const stock = parseFloat(target.dataset.stock);
                    errorDiv = target.parentElement.querySelector('.quantity-error'); // Corrected selector
                    errorDiv.textContent = '';

                    if (isNaN(quantity) || quantity <= 0) {
                        errorDiv.textContent = 'Qty > 0.';
                    } else if (quantity > stock) {
                        errorDiv.textContent = `Max: ${stock}.`;
                    }
                } else if (target.classList.contains('sale-price-input')) {
                    const salePrice = parseFloat(target.value);
                    errorDiv = target.parentElement.parentElement.querySelector('.sale-price-error'); // Corrected selector
                    errorDiv.textContent = '';

                    if (isNaN(salePrice) || salePrice < 0) {
                        errorDiv.textContent = 'Price >= 0.';
                    }
                } else if (target.classList.contains('discount-input')) {
                    const discount = parseFloat(target.value);
                     errorDiv = target.parentElement.parentElement.querySelector('.discount-error'); // Corrected selector
                    errorDiv.textContent = '';

                    if (isNaN(discount) || discount < 0) {
                        errorDiv.textContent = 'Disc. >= 0.';
                    } else if (discount > 100) {
                        errorDiv.textContent = 'Max 100%.';
                    }
                }
                updateSubmitButtonState();
            });

            // Event Delegation for Remove Buttons
            selectedProductsDiv.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-product-btn');
                if (btn) {
                    const productId = parseInt(btn.dataset.productId);
                    removeProductFromForm(productId);
                }
            });

            function removeProductFromForm(productId) {
                selectedProductIds = selectedProductIds.filter(id => id !== productId);
                const productElement = selectedProductsDiv.querySelector(`div[data-product-id="${productId}"]`);
                if (productElement) {
                    productElement.remove();
                }

                if (selectedProductIds.length === 0) {
                    const headerRow = selectedProductsDiv.querySelector('.header-row');
                    if (headerRow) headerRow.remove();
                }
                reindexProductInputs();
                updateSubmitButtonState();
            }

            function reindexProductInputs() {
                const productGroups = selectedProductsDiv.querySelectorAll('div.selected-product-row[data-product-id]');
                productGroups.forEach((group, index) => {
                    group.querySelectorAll('input, select, textarea').forEach(input => {
                        if (input.name) {
                            input.name = input.name.replace(/products\[\d+\]/, `products[${index}]`);
                        }
                    });
                });
            }
            
            function validateAllProductInputs() {
                let isValid = true;
                // Clear previous errors
                document.querySelectorAll('.quantity-error, .sale-price-error, .discount-error').forEach(el => el.textContent = '');

                document.querySelectorAll('.quantity-input').forEach(input => {
                    const quantity = parseFloat(input.value);
                    const stock = parseFloat(input.dataset.stock);
                    const errorDiv = input.parentElement.querySelector('.quantity-error');
                    if (isNaN(quantity) || quantity <= 0) {
                        errorDiv.textContent = 'Qty > 0.';
                        isValid = false;
                    } else if (quantity > stock) {
                        errorDiv.textContent = `Max: ${stock}.`;
                        isValid = false;
                    }
                });

                document.querySelectorAll('.sale-price-input').forEach(input => {
                    const salePrice = parseFloat(input.value);
                    const errorDiv = input.parentElement.parentElement.querySelector('.sale-price-error');
                    if (isNaN(salePrice) || salePrice < 0) {
                        errorDiv.textContent = 'Price >= 0.';
                        isValid = false;
                    }
                });

                document.querySelectorAll('.discount-input').forEach(input => {
                    const discount = parseFloat(input.value);
                    const errorDiv = input.parentElement.parentElement.querySelector('.discount-error');
                    if (isNaN(discount) || discount < 0) {
                        errorDiv.textContent = 'Disc. >= 0.';
                        isValid = false;
                    } else if (discount > 100) {
                        errorDiv.textContent = 'Max 100%.';
                        isValid = false;
                    }
                });
                return isValid;
            }

            function updateSubmitButtonState() {
                const formIsValid = validateAllProductInputs(); // Add more form-level validation if needed
                const customerSelected = document.getElementById('customer_id').value !== '';
                const productsAdded = selectedProductIds.length > 0;
                
                submitInvoiceBtn.disabled = !(formIsValid && customerSelected && productsAdded);
            }
             // Initial call to set button state
            updateSubmitButtonState();
            document.getElementById('customer_id').addEventListener('change', updateSubmitButtonState);


            // Add Product Form (Modal) Submission
            addProductForm.addEventListener('submit', function (e) {
                e.preventDefault();
                // Clear previous modal errors
                addProductForm.querySelectorAll('.text-danger').forEach(el => el.textContent = '');
                const submitProductBtn = addProductForm.querySelector('button[type="submit"]');
                submitProductBtn.disabled = true;
                submitProductBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';


                const formData = new FormData(addProductForm);

                fetch("{{ route('products.store') }}", { // Assuming this route exists and returns JSON
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const newProduct = data.product;
                        liveProducts.push(newProduct); // Add to our live list
                        productStockMap.set(newProduct.id, newProduct.stock);

                        addProductForm.reset();
                        if(modalInstance) modalInstance.hide();
                        // Don't automatically add to form, let user search and add
                        // addProductToForm(newProduct); 
                        showDynamicAlert('Product added successfully! You can now search for it.', 'success');
                    } else if (data.errors) {
                        for (const [field, messages] of Object.entries(data.errors)) {
                            const errorEl = document.getElementById(`${field}-error`);
                            if (errorEl) errorEl.textContent = messages.join(', ');
                        }
                        showDynamicAlert('Failed to add product. Please check errors.', 'danger', false); // Don't auto-hide modal errors
                    } else {
                        showDynamicAlert(data.message || 'Failed to add product due to an unknown error.', 'danger', false);
                    }
                })
                .catch(error => {
                    console.error('Error adding product:', error);
                    showDynamicAlert('An network error occurred while adding the product.', 'danger', false);
                })
                .finally(() => {
                    submitProductBtn.disabled = false;
                    submitProductBtn.innerHTML = 'Save Product';
                });
            });

            // Invoice Form Submission
            invoiceForm.addEventListener('submit', function (e) {
                e.preventDefault();
                // Clear previous main form errors
                invoiceForm.querySelectorAll('.text-danger').forEach(el => el.textContent = '');
                document.getElementById('dynamic-alerts-container').innerHTML = ''; // Clear previous dynamic alerts

                if (!validateAllProductInputs()) {
                    showDynamicAlert('Please fix errors in the product list before submitting.', 'warning');
                    return;
                }
                if (selectedProductIds.length === 0) {
                    showDynamicAlert('Please add at least one product to the invoice.', 'warning');
                    document.getElementById('products-error').textContent = 'At least one product is required.';
                    return;
                }


                submitInvoiceBtn.disabled = true;
                submitInvoiceBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating Invoice...';

                const formData = new FormData(this);

                fetch("{{ route('sales.store') }}", { // This route now handles invoice creation
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                })
                .then(response => {
                    if (!response.ok) { // Check for non-2xx responses
                        return response.json().then(errData => {
                            // Construct a more detailed error object to throw
                            const error = new Error(errData.message || `Request failed with status ${response.status}`);
                            error.response = response; // Attach full response
                            error.data = errData; // Attach parsed JSON error data
                            throw error;
                        }).catch(() => { // If error response is not JSON or parsing fails
                             const error = new Error(`Request failed with status ${response.status}`);
                             error.response = response;
                             throw error;
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showDynamicAlert(data.message || 'Invoice created successfully!', 'success');
                        invoiceForm.reset();
                        selectedProductIds = [];
                        selectedProductsDiv.innerHTML = '';
                        updateSubmitButtonState();
                        // Update stock for products in the liveProducts list and map
                        if(data.updated_stocks && Array.isArray(data.updated_stocks)){
                            data.updated_stocks.forEach(item => {
                                productStockMap.set(item.product_id, item.new_stock);
                                const productIndex = liveProducts.findIndex(p => p.id === item.product_id);
                                if(productIndex > -1) {
                                    liveProducts[productIndex].stock = item.new_stock;
                                }
                            });
                        }

                        setTimeout(() => {
                            window.location.href = "{{ route('invoices.index') }}"; // Redirect to invoices index
                        }, 2500);
                    } else {
                        // Handle specific errors if `data.errors` is present
                        let errorMessage = data.message || 'Failed to create invoice. Please review the details.';
                        if (data.errors) {
                            for (const [field, messages] of Object.entries(data.errors)) {
                                const errorElId = `${field.replace(/\./g, '_')}-error`; // products.0.quantity -> products_0_quantity-error
                                const errorEl = document.getElementById(errorElId);
                                if (errorEl) {
                                    errorEl.textContent = messages.join(', ');
                                } else {
                                     // For product errors like products.0.quantity, display near the specific product row
                                    const productErrorMatch = field.match(/products\.(\d+)\.(\w+)/);
                                    if (productErrorMatch) {
                                        const productIndex = parseInt(productErrorMatch[1]);
                                        const productField = productErrorMatch[2];
                                        const productRows = selectedProductsDiv.querySelectorAll('.selected-product-row');
                                        if(productRows[productIndex]){
                                            const inputElement = productRows[productIndex].querySelector(`[name="products[${productIndex}][${productField}]"]`);
                                            if(inputElement){
                                                let errorContainer = inputElement.parentElement.querySelector(`.${productField}-error`);
                                                if(!errorContainer && inputElement.closest('.input-group')){ // For inputs in input-group
                                                    errorContainer = inputElement.closest('.input-group').parentElement.querySelector(`.${productField}-error`);
                                                }
                                                if (errorContainer) {
                                                    errorContainer.textContent = messages.join(', ');
                                                } else {
                                                     console.warn(`Error container for ${field} not found near product row.`);
                                                }
                                            }
                                        }
                                    } else {
                                        // Fallback for other errors
                                        const generalErrorField = document.getElementById(`${field}-error`);
                                        if(generalErrorField) generalErrorField.textContent = messages.join(', ');
                                        else console.warn(`Error field ${field}-error not found.`);
                                    }
                                }
                            }
                        }
                        showDynamicAlert(errorMessage, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error submitting invoice:', error);
                    let displayMessage = 'An unexpected error occurred while creating the invoice.';
                    if (error.data && error.data.message) {
                        displayMessage = error.data.message;
                        if (error.data.errors) { // Add validation errors if available
                            let validationMessages = [];
                            for (const messages of Object.values(error.data.errors)) {
                                validationMessages.push(messages.join(' '));
                            }
                            if(validationMessages.length > 0) displayMessage += ": " + validationMessages.join('; ');
                        }
                    } else if (error.message) {
                        displayMessage = error.message;
                    }
                    showDynamicAlert(displayMessage, 'danger');
                })
                .finally(() => {
                    submitInvoiceBtn.disabled = false;
                    submitInvoiceBtn.innerHTML = '<i class="bi bi-file-earmark-check"></i> Create Invoice';
                });
            });
            
            // Helper for dynamic alerts
            function showDynamicAlert(message, type = 'info', autoHide = true) {
                const alertsContainer = document.getElementById('dynamic-alerts-container');
                const alertId = 'dynAlert-' + Date.now();
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-fixed-top`;
                alertDiv.setAttribute('role', 'alert');
                alertDiv.id = alertId;
                alertDiv.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                alertsContainer.appendChild(alertDiv);

                if (autoHide) {
                    setTimeout(() => {
                        const currentAlert = document.getElementById(alertId);
                        if (currentAlert) {
                            const bsAlert = bootstrap.Alert.getOrCreateInstance(currentAlert);
                            if(bsAlert) bsAlert.close();
                        }
                    }, 5000); // Auto-hide after 5 seconds
                }
            }

             // Modal cleanup (Bootstrap 5 should handle this better, but good practice)
            if (modalElement) {
                modalElement.addEventListener('hidden.bs.modal', function () {
                    // Clear form errors in modal when it's hidden
                    addProductForm.querySelectorAll('.text-danger').forEach(el => el.textContent = '');
                    addProductForm.reset(); // Optionally reset the form
                    // Bootstrap 5 typically removes .modal-backdrop itself.
                    // If issues persist:
                    // document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                    // document.body.classList.remove('modal-open');
                    // document.body.style.overflow = '';
                    // document.body.style.paddingRight = '';
                });
            }

        } // end initializeApp
    </script>
</body>
@include('layout.footer')