@include('layout.header')

<!-- Include SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    body {
        background-color: #f8f9fa;
    }
    .main-content-area {
        min-height: 100vh;
        opacity: 1 !important;
        display: block !important;
    }
    #invoice-form {
        display: block !important;
    }
    .modal-backdrop {
        z-index: 1040;
    }
    .quantity-error, .sale-price-error, .discount-error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    .header-row {
        background-color: #f8f9fa;
        padding: 10px 15px;
        font-weight: 600;
        color: #4b5563;
        font-size: 0.9rem;
        margin-bottom: 10px;
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
    }
    .product-suggestions {
        position: absolute;
        z-index: 1000;
        background-color: white;
        border: 1px solid #ddd;
        max-height: 200px;
        overflow-y: auto;
        width: 100%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .product-suggestions ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .product-suggestions li {
        padding: 8px 12px;
        cursor: pointer;
    }
    .product-suggestions li:hover {
        background-color: #f0f0f0;
    }
    .itemcode-container {
        margin-top: 10px;
    }
    .itemcode-field {
        margin-bottom: 8px;
    }
</style>

<body class="act-invoice">
    <div class="main-content-area">
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h1 class="mb-0 text-white">Edit Invoice</h1>
                </div>
                <div class="card-body">
                    <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" id="invoice-form">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control" required>
                                <option value="">Select a Customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="ref_no" class="form-label">Ref. No (Challan Number)</label>
                            <input type="text" name="ref_no" id="ref_no" class="form-control" placeholder="Enter challan number"
                                value="{{ old('ref_no', $invoice->sales->first()->ref_no ?? '') }}">
                            @error('ref_no')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="purchase_number" class="form-label">Purchase Number</label>
                            <input type="text" name="purchase_number" id="purchase_number" class="form-control" placeholder="Enter purchase number"
                                value="{{ old('purchase_number', $invoice->purchase_number) }}">
                            @error('purchase_number')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="purchase_date" class="form-label">Purchase Date</label>
                            <input type="date" name="purchase_date" id="purchase_date" class="form-control"
                                value="{{ old('purchase_date', $invoice->purchase_date) }}">
                            @error('purchase_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="product-search" class="form-label">Type to search products</label>
                            <input type="text" id="product-search" class="form-control" placeholder="Search product name...">
                            <div id="product-suggestions" class="product-suggestions list-group position-absolute" style="z-index: 1000;">
                            </div>
                            <button type="button" id="add-product-btn" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                Add New Product
                            </button>
                        </div>

                        <div id="selected-products" class="mt-4">
                            <!-- Header Row -->
                            <div class="row header-row">
                                <div class="col-md-3">Product</div>
                                <div class="col-md-2">Quantity</div>
                                <div class="col-md-2">Unit Price</div>
                                <div class="col-md-2">Discount</div>
                                <div class="col-md-2">itemcodes</div>
                                <div class="col-md-1"></div>
                            </div>
                            <!-- Pre-populated products from the invoice -->
                            @foreach ($invoice->sales as $sale)
                                @foreach ($sale->saleItems as $index => $saleItem)
                                    <div class="row align-items-start selected-product-row" data-product-id="{{ $saleItem->product_id ?? 0 }}">
                                        <div class="col-md-3">
                                            <input type="hidden" name="products[{{$index}}][product_id]" value="{{ $saleItem->product_id ?? 0 }}">
                                            <input type="text" class="form-control" value="{{ $saleItem->product->name ?? 'Unknown Product' }}" disabled>
                                            <div class="stock-below-product">Stock: {{ $saleItem->product->stock ?? 0 }}</div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="d-flex align-items-center">
                                                <input type="number" name="products[{{$index}}][quantity]" class="form-control quantity-input"
                                                    placeholder="Qty" min="1" max="{{ $saleItem->product->stock ?? 0 }}"
                                                    data-stock="{{ $saleItem->product->stock ?? 0 }}" value="{{ $saleItem->quantity ?? 1 }}" required>
                                            </div>
                                            <div class="quantity-error"></div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" name="products[{{$index}}][sale_price]" class="form-control sale-price-input"
                                                    placeholder="Price" min="0" step="0.01" value="{{ $saleItem->unit_price ?? ($saleItem->product->price ?? 0) }}"
                                                    data-original-price="{{ $saleItem->product->price ?? 0 }}" required>
                                            </div>
                                            <div class="sale-price-error"></div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="input-group">
                                                <input type="number" name="products[{{$index}}][discount]" class="form-control discount-input"
                                                    placeholder="Discount" min="0" max="100" step="0.01" value="{{ $saleItem->discount ?? 0 }}">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="discount-error"></div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="itemcode-container">
                                                <div class="itemcode-field">
                                                    <input type="text" name="products[{{$index}}][itemcode]" class="form-control itemcode-input"
                                                        placeholder="itemcode" value="{{ $saleItem->itemcode ?? '' }}">
                                                </div>
                                                <div class="itemcode-field">
                                                    <input type="text" name="products[{{$index}}][secondary_itemcode]" class="form-control itemcode-input"
                                                        placeholder="Secondary itemcode" value="{{ $saleItem->secondary_itemcode ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button type="button" class="btn btn-outline-danger remove-product-btn"
                                                data-product-id="{{ $saleItem->product_id ?? 0 }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>

                        <!-- GST Type and Fields -->
                        <div class="form-group mb-3">
                            <label for="gst_type" class="form-label">GST Type</label>
                            <select name="gst_type" id="gst_type" class="form-control" required>
                                <option value="">Select GST Type</option>
                                <option value="CGST" {{ old('gst_type', $invoice->gst_type) == 'CGST' ? 'selected' : '' }}>CGST</option>
                                <option value="SGST" {{ old('gst_type', $invoice->gst_type) == 'SGST' ? 'selected' : '' }}>SGST</option>
                                <option value="IGST" {{ old('gst_type', $invoice->gst_type) == 'IGST' ? 'selected' : '' }}>IGST</option>
                            </select>
                            @error('gst_type')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Container for GST Fields (Dynamically Updated) -->
                        <div id="gst-fields" class="form-group mb-3">
                            @if ($invoice->gst_type === 'CGST' || $invoice->gst_type === 'SGST')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="cgst" class="form-label">CGST (%)</label>
                                        <input type="number" name="cgst" id="cgst" class="form-control" placeholder="CGST (%)" step="0.01" min="0" max="100"
                                            value="{{ old('cgst', $invoice->cgst ?? 0) }}" required>
                                        <div class="text-danger" id="cgst-error"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="sgst" class="form-label">SGST (%)</label>
                                        <input type="number" name="sgst" id="sgst" class="form-control" placeholder="SGST (%)" step="0.01" min="0" max="100"
                                            value="{{ old('sgst', $invoice->sgst ?? 0) }}" required>
                                        <div class="text-danger" id="sgst-error"></div>
                                    </div>
                                </div>
                            @elseif ($invoice->gst_type === 'IGST')
                                <div class="mb-3">
                                    <label for="igst" class="form-label">IGST (%)</label>
                                    <input type="number" name="igst" id="igst" class="form-control" placeholder="IGST (%)" step="0.01" min="0" max="100"
                                        value="{{ old('igst', $invoice->igst ?? 0) }}" required>
                                    <div class="text-danger" id="igst-error"></div>
                                </div>
                            @endif
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" placeholder="Add description">{{ old('description', $invoice->description) }}</textarea>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Update Invoice</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="add-product-form">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}"
                                    required>
                                <div id="name-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select name="category" id="category" class="form-control" required style="font-size: 13px;">
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
                            <div class="mb-3">
                                <label for="subcategory" class="form-label">Sub-Category</label>
                                <select name="subcategory" id="subcategory" class="form-control" style="font-size: 13px;">
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
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" name="price" id="price" class="form-control" step="0.01" min="0"
                                    value="{{ old('price') }}" required>
                                <div id="price-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="hsn" class="form-label">HSN Code</label>
                                <input type="text" name="hsn" id="hsn" class="form-control" value="{{ old('hsn') }}">
                                <div id="hsn-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="discount" class="form-label">Discount (%)</label>
                                <input type="number" name="discount" id="discount" class="form-control" step="0.01" min="0" max="100"
                                    value="{{ old('discount') }}" required>
                                <div id="discount-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="stock" class="form-label">Tally Stock</label>
                                <input type="number" name="stock" id="stock" class="form-control" min="0"
                                    value="{{ old('stock') }}" required>
                                <div id="stock-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="pstock" class="form-label">Physical Stock</label>
                                <input type="number" name="pstock" id="pstock" class="form-control" min="0"
                                    value="{{ old('pstock') }}" required>
                                <div id="pstock-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="qty" class="form-label">Challan Qty</label>
                                <input type="number" name="qty" id="qty" class="form-control" min="0"
                                    value="{{ old('qty') }}" required>
                                <div id="qty-error" class="text-danger"></div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Save Product</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
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
        const products = @json($products ?? []);
        const searchInput = document.getElementById('product-search');
        const suggestionsBox = document.getElementById('product-suggestions');
        const selectedProductsDiv = document.getElementById('selected-products');
        const addProductForm = document.getElementById('add-product-form');
        const addProductBtn = document.getElementById('add-product-btn');
        const invoiceForm = document.getElementById('invoice-form');
        const submitBtn = invoiceForm.querySelector('button[type="submit"]');
        const modalElement = document.getElementById('addProductModal');
        const gstTypeSelect = document.getElementById('gst_type');
        const gstFieldsContainer = document.getElementById('gst-fields');

        let selectedProducts = @json($invoice->sales->flatMap(fn($sale) => $sale->saleItems->pluck('product_id'))->toArray() ?? []);
        let productStockMap = new Map();
        let modalInstance = null;
        let hasShownLossAlert = false;

        // Initialize modal
        try {
            modalInstance = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
        } catch (error) {
            console.error('Error initializing modal:', error);
            showError('Failed to initialize modal.');
        }

        // Populate product stock map
        products.forEach(p => productStockMap.set(p.id, p.stock));

        // Function to update GST fields based on GST type
        function updateGSTFields() {
            const gstType = gstTypeSelect.value;
            gstFieldsContainer.innerHTML = ''; // Clear existing fields

            if (gstType === 'CGST' || gstType === 'SGST') {
                // Show two fields for CGST and SGST
                gstFieldsContainer.innerHTML = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cgst" class="form-label">CGST (%)</label>
                            <input type="number" name="cgst" id="cgst" class="form-control" placeholder="CGST (%)" step="0.01" min="0" max="100" required>
                            <div class="text-danger" id="cgst-error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sgst" class="form-label">SGST (%)</label>
                            <input type="number" name="sgst" id="sgst" class="form-control" placeholder="SGST (%)" step="0.01" min="0" max="100" required>
                            <div class="text-danger" id="sgst-error"></div>
                        </div>
                    </div>
                `;
            } else if (gstType === 'IGST') {
                // Show one field for IGST
                gstFieldsContainer.innerHTML = `
                    <div class="mb-3">
                        <label for="igst" class="form-label">IGST (%)</label>
                        <input type="number" name="igst" id="igst" class="form-control" placeholder="IGST (%)" step="0.01" min="0" max="100" required>
                        <div class="text-danger" id="igst-error"></div>
                    </div>
                `;
            }
        }

        // Add event listener for GST type change
        gstTypeSelect.addEventListener('change', updateGSTFields);

        // Function to check for potential loss
        function checkPotentialLoss(input) {
            const salePrice = parseFloat(input.value);
            const originalPrice = parseFloat(input.dataset.originalPrice);
            const errorDiv = input.parentElement.nextElementSibling;
            errorDiv.textContent = '';

            if (isNaN(salePrice) || salePrice < 0) {
                errorDiv.textContent = 'Price must be 0 or greater.';
            } else if (salePrice < originalPrice && !hasShownLossAlert) {
                hasShownLossAlert = true;
                Swal.fire({
                    icon: 'warning',
                    title: 'Potential Loss',
                    text: `The sale price (₹${salePrice}) is less than the original price (₹${originalPrice}). This sale will result in a loss.`,
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    hasShownLossAlert = false;
                });
            } else if (salePrice >= originalPrice) {
                hasShownLossAlert = false;
            }
        }

        // Check for potential loss on page load for pre-filled values
        document.querySelectorAll('.sale-price-input').forEach(input => {
            checkPotentialLoss(input);
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
                    item.textContent = `${product.name} (Stock: ${product.stock})`;
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
            productGroup.className = 'row align-items-start selected-product-row';
            productGroup.setAttribute('data-product-id', product.id);

            productGroup.innerHTML = `
                <div class="col-md-3">
                    <input type="hidden" name="products[${index}][product_id]" value="${product.id}">
                    <input type="text" class="form-control" value="${product.name}" disabled>
                    <div class="stock-below-product">Stock: ${product.stock}</div>
                </div>
                <div class="col-md-2">
                    <div class="d-flex align-items-center">
                        <input type="number" name="products[${index}][quantity]" class="form-control quantity-input" 
                            placeholder="Qty" min="1" max="${product.stock}" data-stock="${product.stock}" required>
                        <span class="stock-info">/ ${product.stock}</span>
                    </div>
                    <div class="quantity-error"></div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" name="products[${index}][sale_price]" class="form-control sale-price-input" 
                            placeholder="Price" min="0" step="0.01" value="${product.price}" data-original-price="${product.price}" required>
                    </div>
                    <div class="sale-price-error"></div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <input type="number" name="products[${index}][discount]" class="form-control discount-input" 
                            placeholder="Discount" min="0" max="100" step="0.01" value="0">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="discount-error"></div>
                </div>
                <div class="col-md-2">
                    <div class="itemcode-container">
                        <div class="itemcode-field">
                            <input type="text" name="products[${index}][itemcode]" class="form-control itemcode-input" 
                                placeholder="itemcode" value="${product.itemcode || ''}">
                        </div>
                        <div class="itemcode-field">
                            <input type="text" name="products[${index}][secondary_itemcode]" class="form-control itemcode-input" 
                                placeholder="Secondary itemcode" value="">
                        </div>
                    </div>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-outline-danger remove-product-btn" 
                        data-product-id="${product.id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;

            selectedProductsDiv.appendChild(productGroup);
            // Check for potential loss for the newly added product
            const newSalePriceInput = productGroup.querySelector('.sale-price-input');
            checkPotentialLoss(newSalePriceInput);
            updateSubmitButtonState();
        }

        // Real-Time Validation
        selectedProductsDiv.addEventListener('input', function (e) {
            const target = e.target;
            let errorDiv;

            if (target.classList.contains('quantity-input')) {
                const quantity = parseFloat(target.value);
                const stock = parseFloat(target.dataset.stock);
                errorDiv = target.nextElementSibling;
                errorDiv.textContent = '';

                if (isNaN(quantity) || quantity <= 0) {
                    errorDiv.textContent = 'Quantity must be greater than 0.';
                } else if (quantity > stock) {
                    errorDiv.textContent = `Cannot exceed stock (${stock}).`;
                }
            } else if (target.classList.contains('sale-price-input')) {
                checkPotentialLoss(target);
            } else if (target.classList.contains('discount-input')) {
                const discount = parseFloat(target.value);
                errorDiv = target.nextElementSibling;
                errorDiv.textContent = '';

                if (isNaN(discount) || discount < 0) {
                    errorDiv.textContent = 'Discount must be 0 or greater.';
                } else if (discount > 100) {
                    errorDiv.textContent = 'Discount cannot exceed 100%.';
                }
            }

            updateSubmitButtonState();
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

            if (selectedProducts.length === 0) {
                const headerRow = selectedProductsDiv.querySelector('.header-row');
                if (headerRow) {
                    headerRow.remove();
                }
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
                group.querySelector('.sale-price-input').name = `products[${index}][sale_price]`;
                group.querySelector('.discount-input').name = `products[${index}][discount]`;
                group.querySelectorAll('.itemcode-input')[0].name = `products[${index}][itemcode]`;
                group.querySelectorAll('.itemcode-input')[1].name = `products[${index}][secondary_itemcode]`;
            });
        }

        // Validate All Inputs
        function validateInputs() {
            let isValid = true;
            document.querySelectorAll('.quantity-error, .sale-price-error, .discount-error').forEach(el => el.textContent = '');

            document.querySelectorAll('.quantity-input').forEach(input => {
                const quantity = parseFloat(input.value);
                const stock = parseFloat(input.dataset.stock);
                const errorDiv = input.nextElementSibling;

                if (isNaN(quantity) || quantity <= 0) {
                    errorDiv.textContent = 'Quantity must be greater than 0.';
                    isValid = false;
                } else if (quantity > stock) {
                    errorDiv.textContent = `Cannot exceed stock (${stock}).`;
                    isValid = false;
                }
            });

            document.querySelectorAll('.sale-price-input').forEach(input => {
                const salePrice = parseFloat(input.value);
                const errorDiv = input.parentElement.nextElementSibling;

                if (isNaN(salePrice) || salePrice < 0) {
                    errorDiv.textContent = 'Price must be 0 or greater.';
                    isValid = false;
                }
            });

            document.querySelectorAll('.discount-input').forEach(input => {
                const discount = parseFloat(input.value);
                const errorDiv = input.nextElementSibling;

                if (isNaN(discount) || discount < 0) {
                    errorDiv.textContent = 'Discount must be 0 or greater.';
                    isValid = false;
                } else if (discount > 100) {
                    errorDiv.textContent = 'Discount cannot exceed 100%.';
                    isValid = false;
                }
            });

            // Validate GST fields
            const gstType = gstTypeSelect.value;
            if (gstType === 'CGST' || gstType === 'SGST') {
                const cgstInput = document.getElementById('cgst');
                const sgstInput = document.getElementById('sgst');
                const cgstError = document.getElementById('cgst-error');
                const sgstError = document.getElementById('sgst-error');

                const cgstValue = parseFloat(cgstInput?.value);
                const sgstValue = parseFloat(sgstInput?.value);

                if (isNaN(cgstValue) || cgstValue < 0 || cgstValue > 100) {
                    cgstError.textContent = 'CGST must be between 0 and 100.';
                    isValid = false;
                }
                if (isNaN(sgstValue) || sgstValue < 0 || sgstValue > 100) {
                    sgstError.textContent = 'SGST must be between 0 and 100.';
                    isValid = false;
                }
            } else if (gstType === 'IGST') {
                const igstInput = document.getElementById('igst');
                const igstError = document.getElementById('igst-error');
                const igstValue = parseFloat(igstInput?.value);

                if (isNaN(igstValue) || igstValue < 0 || igstValue > 100) {
                    igstError.textContent = 'IGST must be between 0 and 100.';
                    isValid = false;
                }
            }

            return isValid;
        }

        // Update Submit Button State
        function updateSubmitButtonState() {
            const isValid = validateInputs();
            submitBtn.disabled = !isValid;
        }

        // Add Product Form Submission
        addProductForm.addEventListener('submit', function (e) {
            e.preventDefault();
            document.querySelectorAll('.text-danger').forEach(el => el.textContent = '');

            const formData = new FormData(addProductForm);

            fetch("{{ route('products.store') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const newProduct = data.product;
                    products.push(newProduct);
                    productStockMap.set(newProduct.id, newProduct.stock);
                    addProductForm.reset();
                    modalInstance.hide();
                    cleanUpModal();
                    addProduct(newProduct);
                    showSuccess('Product added successfully!');
                } else {
                    for (const [field, message] of Object.entries(data.errors)) {
                        document.getElementById(`${field}-error`).textContent = message[0];
                    }
                }
            })
            .catch(error => {
                console.error('Error adding product:', error);
                showError('Failed to add product.');
            });
        });

        // Invoice Form Submission
        invoiceForm.addEventListener('submit', function (e) {
            e.preventDefault();
            document.querySelectorAll('.alert-danger').forEach(el => el.remove());

            if (!validateInputs()) {
                showError('Please fix errors before submitting.');
                return;
            }

            const formData = new FormData(this);

            fetch("{{ route('invoices.update', $invoice->id) }}", {
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
                    showSuccess(data.message || 'Invoice updated successfully!');
                    setTimeout(() => {
                        window.location.href = "{{ route('invoices.index') }}";
                    }, 2000);
                } else {
                    if (data.errors) {
                        for (const [field, messages] of Object.entries(data.errors)) {
                            showError(messages.join(', '));
                        }
                    } else {
                        showError(data.message || 'Failed to update invoice.');
                    }
                }
            })
            .catch(error => {
                console.error('Error submitting invoice:', error);
                showError('Failed to update invoice: ' + error.message);
            });
        });

        // Modal Open
        addProductBtn.addEventListener('click', function () {
            if (modalInstance) {
                modalInstance.show();
            } else {
                modalInstance = new bootstrap.Modal(modalElement);
                modalInstance.show();
            }
        });

        // Modal Cleanup
        function cleanUpModal() {
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            document.querySelector('.main-content-area').style.opacity = '1';
            invoiceForm.style.display = 'block';
        }

        modalElement.addEventListener('hidden.bs.modal', cleanUpModal);

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

        // Initialize submit button state on page load
        updateSubmitButtonState();
    }
</script>

@include('layout.footer')