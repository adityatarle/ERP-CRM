@include('layout.header')

<!-- Include SweetAlert2 CDN -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Page & Form Styling */
    body { background-color: #f4f7f9; }
    .main-content-area { min-height: 100vh; }
    .card-header h1 { font-size: 1.25rem; font-weight: 600; }
    .form-label { font-weight: 500; color: #495057; margin-bottom: 0.5rem; }
    .card.form-section { border: 1px solid #dee2e6; box-shadow: none; }
    
    /* Product Search & Suggestions */
    .suggestions-container { position: relative; }
    .product-suggestions, .customer-suggestions {
        position: absolute; z-index: 1050; background-color: white;
        border: 1px solid #ced4da; border-radius: 0 0 .375rem .375rem;
        max-height: 250px; overflow-y: auto; width: 100%;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .product-suggestions .list-group-item:hover, .customer-suggestions .list-group-item:hover { background-color: #0d6efd; color: #fff; }

    /* Selected Products Grid Layout */
    .products-header, .product-item-row {
        display: grid;
        grid-template-columns: 3fr 1.25fr 1.75fr 1.5fr 2fr 0.75fr;
        gap: 1rem;
        align-items: start;
        padding: 0.75rem 1rem;
    }
    .products-header {
        background-color: #e9ecef;
        border-radius: .375rem;
        font-weight: 600;
        font-size: 0.85rem;
        color: #495057;
        text-transform: uppercase;
    }
    .product-item-row {
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        margin-bottom: 0.75rem;
    }
    .stock-info { font-size: 0.8rem; color: #6c757d; }
    .product-name-display { font-weight: 500; margin-bottom: 0.25rem; }
    .remove-product-btn { justify-self: end; }
    .validation-error { color: #dc3545; font-size: 0.8rem; margin-top: 0.25rem; }

    /* Totals Section */
    .totals-card { background-color: #fff; border: 1px solid #dee2e6; border-radius: .375rem; padding: 1.5rem; }
    .totals-card .row { font-size: 1.1rem; }
    .totals-card .grand-total { font-size: 1.4rem; font-weight: bold; }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .products-header { display: none; }
        .product-item-row { grid-template-columns: 1fr; padding: 1rem; }
        .remove-product-btn { justify-self: start; margin-top: 1rem; }
    }
</style>

<body class="act-invoice">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            <div id="alert-container"></div>
            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                    <h1 class="mb-0 h5 text-white">Create Invoice</h1>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('invoices.store') }}" method="POST" id="invoice-form">
                        @csrf
                        {{-- Main Invoice Details --}}
                        <div class="card form-section p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-md-6 suggestions-container">
                                    <label for="customer-search" class="form-label">Customer</label>
                                    <input type="text" id="customer-search" class="form-control" placeholder="Type customer name to search..." required>
                                    <input type="hidden" name="customer_id" id="customer_id">
                                    <div id="customer-suggestions" class="list-group customer-suggestions" style="display: none;"></div>
                                </div>
                                <div class="col-md-6"><label for="ref_no" class="form-label">Ref. No (Challan Number)</label><input type="text" name="ref_no" id="ref_no" class="form-control" placeholder="Enter challan number"></div>
                                <div class="col-md-6"><label for="contact_person" class="form-label">Contact Person</label><input type="text" name="contact_person" id="contact_person" class="form-control" placeholder="Enter contact person name (optional)"></div>
                                <div class="col-md-6"><label for="purchase_number" class="form-label">Purchase Number</label><input type="text" name="purchase_number" id="purchase_number" class="form-control" placeholder="Enter purchase order number"></div>
                                <div class="col-md-6"><label for="purchase_date" class="form-label">Purchase Date</label><input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                            </div>
                        </div>

                        {{-- Product Selection --}}
                        <div class="card form-section p-3 mb-4">
                             <div class="row g-3">
                                <div class="col-md-9 suggestions-container"><label for="product-search" class="form-label">Search & Add Products</label><input type="text" id="product-search" class="form-control" placeholder="Type product name to search..."><div id="product-suggestions" class="list-group position-absolute w-100" style="z-index: 1000; display: none;"></div></div>
                                <div class="col-md-3 d-flex align-items-end"><button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="fa fa-plus"></i> Add New Product</button></div>
                            </div>
                        </div>

                        {{-- Selected Products List --}}
                        <div id="selected-products-container" class="mt-4">
                            <div class="products-header" style="display: none;"><!-- Initially hidden -->
                                <div>Product</div><div>Quantity</div><div>Sale Price</div><div>Discount</div><div>Item Codes</div><div>Action</div>
                            </div>
                            <div id="selected-products-list"></div>
                        </div>
                        
                        {{-- Final Section: GST, Description, and Totals --}}
                        <div class="row mt-4 g-4">
                            <div class="col-lg-7">
                                <div class="card form-section p-3 h-100">
                                    <div class="row g-3">
                                        <div class="col-md-6"><label for="gst_type" class="form-label">GST Type</label><select name="gst_type" id="gst_type" class="form-select" required><option value="" disabled selected>Select GST Type</option><option value="CGST">CGST/SGST</option><option value="IGST">IGST</option></select></div>
                                        <div class="col-md-6" id="gst-fields-container"></div>
                                        <div class="col-12"><label for="description" class="form-label">Description</label><textarea name="description" id="description" class="form-control" rows="3" placeholder="Add any additional notes or terms..."></textarea></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="totals-card h-100">
                                    <div class="row mb-2"><div class="col-7">Subtotal</div><div class="col-5 text-end" id="subtotal">₹0.00</div></div>
                                    <div class="row mb-3"><div class="col-7">Total Tax (GST)</div><div class="col-5 text-end" id="total_tax">₹0.00</div></div>
                                    <hr><div class="row grand-total"><div class="col-7 text-dark">Grand Total</div><div class="col-5 text-end text-primary" id="grand_total">₹0.00</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary btn-md">Create Invoice</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="addProductModalLabel">Add New Product</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">
                        <form id="add-product-form">
                            @csrf
                            <div class="mb-3"><label for="name" class="form-label">Name</label><input type="text" name="name" id="name" class="form-control" required><div id="name-error" class="text-danger"></div></div>
                            <div class="mb-3"><label for="category" class="form-label">Category</label><select name="category" id="category" class="form-control" required><option value="">Select Category</option>@if(!empty($categories)) @foreach($categories as $category)<option value="{{ $category }}">{{ $category }}</option>@endforeach @endif</select><div id="category-error" class="text-danger"></div></div>
                            <div class="mb-3"><label for="subcategory" class="form-label">Sub-Category</label><select name="subcategory" id="subcategory" class="form-control"><option value="">Select Sub-Category</option>@if(!empty($subcategories)) @foreach($subcategories as $subcategory)<option value="{{ $subcategory }}">{{ $subcategory }}</option>@endforeach @endif</select><div id="subcategory-error" class="text-danger"></div></div>
                            <div class="mb-3"><label for="price" class="form-label">Price</label><input type="number" name="price" id="price" class="form-control" step="0.01" min="0" required><div id="price-error" class="text-danger"></div></div>
                            <div class="mb-3"><label for="hsn" class="form-label">HSN Code</label><input type="text" name="hsn" id="hsn" class="form-control"><div id="hsn-error" class="text-danger"></div></div>
                            <div class="mb-3"><label for="discount" class="form-label">Discount (%)</label><input type="number" name="discount" id="discount" class="form-control" step="0.01" min="0" max="100" required><div id="discount-error" class="text-danger"></div></div>
                            <div class="mb-3"><label for="stock" class="form-label">Tally Stock</label><input type="number" name="stock" id="stock" class="form-control" min="0" required><div id="stock-error" class="text-danger"></div></div>
                            <div class="mb-3"><label for="pstock" class="form-label">Physical Stock</label><input type="number" name="pstock" id="pstock" class="form-control" min="0" required><div id="pstock-error" class="text-danger"></div></div>
                            <div class="mb-3"><label for="qty" class="form-label">Challan Qty</label><input type="number" name="qty" id="qty" class="form-control" min="0" required><div id="qty-error" class="text-danger"></div></div>
                            <div class="d-flex gap-2"><button type="submit" class="btn btn-primary">Save Product</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

@include('layout.footer')

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initializeApp();
    });

    function initializeApp() {
        const products = @json($products);
        const customers = @json($customers);
        const searchInput = document.getElementById('product-search');
        const suggestionsBox = document.getElementById('product-suggestions');
        const customerSearchInput = document.getElementById('customer-search');
        const customerSuggestionsBox = document.getElementById('customer-suggestions');
        const selectedProductsList = document.getElementById('selected-products-list');
        const productsHeader = document.querySelector('.products-header');
        const addProductForm = document.getElementById('add-product-form');
        const invoiceForm = document.getElementById('invoice-form');
        const gstTypeSelect = document.getElementById('gst_type');
        const gstFieldsContainer = document.getElementById('gst-fields-container');
        let selectedProductIds = new Set();
        let selectedCustomerId = null;
        let productStockMap = new Map(products.map(p => [p.id, p.stock]));
        let modalInstance = new bootstrap.Modal(document.getElementById('addProductModal'));
        let hasShownLossAlert = false;

        // Modal cleanup
        function cleanUpModal() {
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }

        modalInstance._element.addEventListener('hidden.bs.modal', cleanUpModal);

        function addProduct(product) {
            if (selectedProductIds.has(product.id)) {
                const existingRow = selectedProductsList.querySelector(`[data-product-id="${product.id}"]`);
                if (existingRow) {
                    existingRow.classList.add('border-primary', 'shadow-sm');
                    setTimeout(() => existingRow.classList.remove('border-primary', 'shadow-sm'), 1500);
                }
                return;
            }
            selectedProductIds.add(product.id);
            productsHeader.style.display = 'grid';
            const index = selectedProductIds.size - 1;

            const row = document.createElement('div');
            row.className = 'product-item-row';
            row.setAttribute('data-product-id', product.id);
            row.innerHTML = `
                <div>
                    <div class="product-name-display">${product.name}</div>
                    <div class="stock-info">Stock: ${product.stock}</div>
                    <input type="hidden" name="products[${index}][product_id]" value="${product.id}">
                </div>
                <div>
                    <input type="number" name="products[${index}][quantity]" class="form-control quantity-input" placeholder="Qty" min="1" max="${product.stock}" data-stock="${product.stock}" required>
                    <div class="validation-error"></div>
                </div>
                <div>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" name="products[${index}][sale_price]" class="form-control sale-price-input" placeholder="Price" value="${product.price}" data-original-price="${product.price}" min="0" step="0.01" required>
                    </div>
                    <div class="validation-error"></div>
                </div>
                <div>
                    <div class="input-group">
                        <input type="number" name="products[${index}][discount]" class="form-control discount-input" value="0" min="0" max="100" step="0.01">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="validation-error"></div>
                </div>
                <div>
                    <input type="text" name="products[${index}][itemcode]" class="form-control itemcode-input mb-2" placeholder="Item Code" value="${product.itemcode || ''}">
                    <input type="text" name="products[${index}][secondary_itemcode]" class="form-control itemcode-input" placeholder="Secondary Code">
                </div>
                <div class="remove-product-btn">
                    <button type="button" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></button>
                </div>
            `;
            selectedProductsList.appendChild(row);
            reindexProductInputs();
            calculateTotals();
        }

        function reindexProductInputs() {
            selectedProductsList.querySelectorAll('.product-item-row').forEach((row, index) => {
                row.querySelectorAll('input, select').forEach(input => {
                    if (input.name) input.name = input.name.replace(/products\[\d+\]/, `products[${index}]`);
                });
            });
        }

        function calculateTotals() {
            let subtotal = 0;
            selectedProductsList.querySelectorAll('.product-item-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const salePrice = parseFloat(row.querySelector('.sale-price-input').value) || 0;
                const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
                if (quantity > 0 && salePrice >= 0) {
                    const lineTotal = quantity * salePrice;
                    const discountAmount = lineTotal * (discount / 100);
                    subtotal += lineTotal - discountAmount;
                }
            });

            let totalTax = 0;
            const gstType = gstTypeSelect.value;
            if (gstType === 'CGST') {
                const cgst = parseFloat(document.getElementById('cgst')?.value) || 0;
                const sgst = parseFloat(document.getElementById('sgst')?.value) || 0;
                totalTax = subtotal * ((cgst + sgst) / 100);
            } else if (gstType === 'IGST') {
                const igst = parseFloat(document.getElementById('igst')?.value) || 0;
                totalTax = subtotal * (igst / 100);
            }

            document.getElementById('subtotal').textContent = `₹${subtotal.toFixed(2)}`;
            document.getElementById('total_tax').textContent = `₹${totalTax.toFixed(2)}`;
            document.getElementById('grand_total').textContent = `₹${(subtotal + totalTax).toFixed(2)}`;
        }

        function updateGSTFields() {
            const gstType = gstTypeSelect.value;
            gstFieldsContainer.innerHTML = '';
            if (gstType === 'CGST') {
                gstFieldsContainer.innerHTML = `<div class="row"><div class="col-6"><label for="cgst" class="form-label">CGST (%)</label><input type="number" name="cgst" id="cgst" class="form-control gst-input" placeholder="e.g. 9" required></div><div class="col-6"><label for="sgst" class="form-label">SGST (%)</label><input type="number" name="sgst" id="sgst" class="form-control gst-input" placeholder="e.g. 9" required></div></div>`;
            } else if (gstType === 'IGST') {
                gstFieldsContainer.innerHTML = `<label for="igst" class="form-label">IGST (%)</label><input type="number" name="igst" id="igst" class="form-control gst-input" placeholder="e.g. 18" required>`;
            }
            calculateTotals();
        }

        function validateRow(row) {
            let rowIsValid = true;
            // Quantity
            const qtyInput = row.querySelector('.quantity-input');
            const qtyError = qtyInput.nextElementSibling;
            const quantity = parseFloat(qtyInput.value);
            const stock = parseFloat(qtyInput.dataset.stock);
            qtyError.textContent = '';
            if (isNaN(quantity) || quantity <= 0) { qtyError.textContent = 'Must be > 0.'; rowIsValid = false; }
            else if (quantity > stock) { qtyError.textContent = `Max stock: ${stock}`; rowIsValid = false; }

            // Sale Price
            const priceInput = row.querySelector('.sale-price-input');
            const priceError = priceInput.closest('.input-group').nextElementSibling;
            const salePrice = parseFloat(priceInput.value);
            priceError.textContent = '';
            if (isNaN(salePrice) || salePrice < 0) { priceError.textContent = 'Cannot be negative.'; rowIsValid = false; }

            return rowIsValid;
        }

        // --- Customer Search Logic ---
        customerSearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            customerSuggestionsBox.innerHTML = '';
            if (query.length < 1) {
                customerSuggestionsBox.style.display = 'none';
                return;
            }
            const filtered = customers.filter(c => c.name.toLowerCase().includes(query));
            if (filtered.length > 0) {
                filtered.forEach(customer => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = `${customer.name}`;
                    item.addEventListener('click', () => {
                        customerSearchInput.value = customer.name;
                        document.getElementById('customer_id').value = customer.id;
                        selectedCustomerId = customer.id;
                        customerSuggestionsBox.style.display = 'none';
                    });
                    customerSuggestionsBox.appendChild(item);
                });
                customerSuggestionsBox.style.display = 'block';
            } else {
                customerSuggestionsBox.style.display = 'none';
            }
        });

        // --- Combined Click Outside Handler ---
        document.addEventListener('click', (e) => {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar && sidebar.contains(e.target)) {
                return; // Ignore clicks on the sidebar to prevent interference
            }
            if (!customerSuggestionsBox.contains(e.target) && e.target !== customerSearchInput) {
                customerSuggestionsBox.style.display = 'none';
            }
            if (!suggestionsBox.contains(e.target) && e.target !== searchInput) {
                suggestionsBox.style.display = 'none';
            }
        });

        // --- Product Search Logic ---
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            suggestionsBox.innerHTML = '';
            if (query.length < 1) {
                suggestionsBox.style.display = 'none';
                return;
            }
            const filtered = products.filter(p => p.name.toLowerCase().includes(query) && !selectedProductIds.has(p.id));
            if (filtered.length > 0) {
                filtered.forEach(product => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = `${product.name} <small class="text-muted">(Stock: ${product.stock})</small>`;
                    item.addEventListener('click', () => {
                        addProduct(product);
                        searchInput.value = '';
                        suggestionsBox.style.display = 'none';
                    });
                    suggestionsBox.appendChild(item);
                });
                suggestionsBox.style.display = 'block';
            } else {
                suggestionsBox.style.display = 'none';
            }
        });

        selectedProductsList.addEventListener('click', e => {
            const removeBtn = e.target.closest('.remove-product-btn button');
            if (removeBtn) {
                const row = removeBtn.closest('.product-item-row');
                const productId = parseInt(row.dataset.productId);
                selectedProductIds.delete(productId);
                row.remove();
                if (selectedProductIds.size === 0) productsHeader.style.display = 'none';
                reindexProductInputs();
                calculateTotals();
            }
        });

        selectedProductsList.addEventListener('input', e => {
            const row = e.target.closest('.product-item-row');
            if (row) validateRow(row);
            calculateTotals();
        });

        selectedProductsList.addEventListener('blur', e => {
            const priceInput = e.target.closest('.sale-price-input');
            if (priceInput) {
                const salePrice = parseFloat(priceInput.value);
                const originalPrice = parseFloat(priceInput.dataset.originalPrice);
                if (!isNaN(salePrice) && salePrice < originalPrice && !hasShownLossAlert) {
                    hasShownLossAlert = true;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Potential Loss',
                        text: `The sale price (₹${salePrice.toFixed(2)}) is less than the original price (₹${originalPrice.toFixed(2)}). This sale will result in a loss.`,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        hasShownLossAlert = false;
                    });
                }
            }
        }, true);

        gstTypeSelect.addEventListener('change', updateGSTFields);

        // --- Form Submission Logic ---
        invoiceForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let allRowsValid = true;
            selectedProductsList.querySelectorAll('.product-item-row').forEach(row => {
                if (!validateRow(row)) allRowsValid = false;
            });
            if (!allRowsValid || selectedProductIds.size === 0 || !selectedCustomerId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select a customer, add at least one product, and fix all validation errors.'
                });
                return;
            }

            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Creating...`;
            
            const formData = new FormData(this);
            fetch("{{ route('invoices.store') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => window.location.href = "{{ route('invoices.index') }}", 2000);
                } else {
                    let errorHtml = '<ul>';
                    if (data.errors) {
                        for (const messages of Object.values(data.errors)) {
                            errorHtml += `<li>${messages[0]}</li>`;
                        }
                    } else {
                        errorHtml += `<li>${data.message || 'An unknown error occurred.'}</li>`;
                    }
                    errorHtml += '</ul>';
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Failed',
                        html: errorHtml
                    });
                }
            })
            .catch(error => {
                console.error('Submission Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Error',
                    text: 'Could not connect to the server. Please try again.'
                });
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = 'Create Invoice';
            });
        });
        
        addProductForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch("{{ route('products.store') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const newProduct = data.product;
                    products.push(newProduct);
                    productStockMap.set(newProduct.id, newProduct.stock);
                    this.reset();
                    modalInstance.hide();
                    addProduct(newProduct);
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Product added successfully!'
                    });
                } else {
                    for (const [field, message] of Object.entries(data.errors)) {
                        document.getElementById(`${field}-error`).textContent = message[0];
                    }
                }
            })
            .catch(error => console.error('Error adding product:', error));
        });
    }
</script>