@include('layout.header')

<!-- Include SweetAlert2 CDN -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Page & Form Styling */
    body {
        background-color: #f4f7f9;
    }

    .main-content-area {
        min-height: 100vh;
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

    .card.form-section {
        border: 1px solid #dee2e6;
        box-shadow: none;
    }

    /* Search & Suggestions */
    .suggestions-container {
        position: relative;
    }

    .customer-suggestions,
    .product-suggestions {
        position: absolute;
        z-index: 1050;
        background-color: white;
        border: 1px solid #ced4da;
        border-radius: 0 0 .375rem .375rem;
        max-height: 250px;
        overflow-y: auto;
        width: 100%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .customer-suggestions .list-group-item:hover,
    .product-suggestions .list-group-item:hover {
        background-color: #0d6efd;
        color: #fff;
    }

    /* Items Grid Layout */
    .items-header,
    .item-row {
        display: grid;
        grid-template-columns: 3fr 1.25fr 1.75fr 1.5fr 2fr 0.75fr;
        gap: 1rem;
        align-items: start;
        padding: 0.75rem 1rem;
    }

    .items-header {
        background-color: #e9ecef;
        border-radius: .375rem;
        font-weight: 600;
        font-size: 0.85rem;
        color: #495057;
        text-transform: uppercase;
    }

    .item-row {
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        margin-bottom: 0.75rem;
    }

    .stock-info {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .item-name-display {
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .remove-item-btn {
        justify-self: end;
    }

    .validation-error {
        color: #dc3545;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    /* Totals Section */
    .totals-card {
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        padding: 1.5rem;
    }

    .totals-card .row {
        font-size: 1.1rem;
    }

    .totals-card .grand-total {
        font-size: 1.4rem;
        font-weight: bold;
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .items-header {
            display: none;
        }

        .item-row {
            grid-template-columns: 1fr;
            padding: 1rem;
        }

        .remove-item-btn {
            justify-self: start;
            margin-top: 1rem;
        }
    }
</style>

<body class="act-delivery-notes-create">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            <div id="alert-container"></div>
            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                    <h1 class="mb-0 h5 text-white">Create Delivery Note</h1>
                    <a href="{{ route('delivery_notes.index') }}" class="btn btn-light btn-sm">Back to List</a>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('delivery_notes.store') }}" id="delivery-note-form">
                        @csrf
                        <!-- Main Details -->
                        <div class="card form-section p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-md-6 suggestions-container">
                                    <label for="customer-search" class="form-label">Customer</label>
                                    <input type="text" id="customer-search" class="form-control" placeholder="Type customer name to search..." required>
                                    <input type="hidden" name="customer_id" id="customer_id">
                                    <div id="customer-suggestions" class="list-group customer-suggestions" style="display: none;"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="ref_no" class="form-label">Ref. No (Challan Number)</label>
                                    <input type="text" name="ref_no" id="ref_no" class="form-control" placeholder="Enter challan number">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="contact_person" class="form-label">Contact Person</label>
                                    <input type="text" name="contact_person" class="form-control" rows="2" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="delivery_date" class="form-label">Delivery Date</label>
                                    <input type="date" name="delivery_date" id="delivery_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                
                                <!-- <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Add any additional notes or terms..."></textarea>
                                </div> -->
                                <div class="col-12">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="e.g., Vehicle number, contact person, etc."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Product Selection -->
                        <div class="card form-section p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-md-9 suggestions-container">
                                    <label for="item-search" class="form-label">Search & Add Items</label>
                                    <input type="text" id="item-search" class="form-control" placeholder="Type item name to search...">
                                    <div id="item-suggestions" class="list-group product-suggestions" style="display: none;"></div>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                        <i class="fa fa-plus"></i> Add New Item
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Items List -->
                        <div id="items-container" class="mt-4">
                            <div class="items-header" style="display: none;">
                                <div>Item</div>
                                <div>Quantity</div>
                                <div>Price</div>
                                <div>Discount</div>
                                <div>Item Codes</div>
                                <div>Action</div>
                            </div>
                            <div id="items-list"></div>
                        </div>

                        <!-- Final Section: Totals -->
                        <div class="row mt-4 g-4">
                            <div class="col-lg-7"></div>
                            <div class="col-lg-5">
                                <div class="totals-card h-100">
                                    <div class="row mb-2">
                                        <div class="col-7">Subtotal</div>
                                        <div class="col-5 text-end" id="subtotal">₹0.00</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-7">Total Tax (GST)</div>
                                        <div class="col-5 text-end" id="total_tax">₹0.00</div>
                                    </div>
                                    <hr>
                                    <div class="row grand-total">
                                        <div class="col-7 text-dark">Grand Total</div>
                                        <div class="col-5 text-end text-primary" id="grand_total">₹0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary btn-md">Create Delivery Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Item Modal -->
        <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemModalLabel">Add New Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="add-item-form">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                                <div id="name-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select name="category" id="category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                                <div id="category-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="subcategory" class="form-label">Sub-Category</label>
                                <select name="subcategory" id="subcategory" class="form-control">
                                    <option value="">Select Sub-Category</option>
                                    @foreach($subcategories as $subcategory)
                                    <option value="{{ $subcategory }}">{{ $subcategory }}</option>
                                    @endforeach
                                </select>
                                <div id="subcategory-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" required>
                                <div id="price-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="hsn" class="form-label">HSN Code</label>
                                <input type="text" name="hsn" id="hsn" class="form-control">
                                <div id="hsn-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="discount" class="form-label">Discount (%)</label>
                                <input type="number" name="discount" id="discount" class="form-control" step="0.01" min="0" max="100" required>
                                <div id="discount-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="stock" class="form-label">Tally Stock</label>
                                <input type="number" name="stock" id="stock" class="form-control" min="0" required>
                                <div id="stock-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="pstock" class="form-label">Physical Stock</label>
                                <input type="number" name="pstock" id="pstock" class="form-control" min="0" required>
                                <div id="pstock-error" class="text-danger"></div>
                            </div>
                            <div class="mb-3">
                                <label for="qty" class="form-label">Challan Qty</label>
                                <input type="number" name="qty" id="qty" class="form-control" min="0" required>
                                <div id="qty-error" class="text-danger"></div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Save Item</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initializeApp();
        });

        function initializeApp() {
            const products = @json($products);
            const customers = @json($customers);
            const searchInput = document.getElementById('item-search');
            const suggestionsBox = document.getElementById('item-suggestions');
            const customerSearchInput = document.getElementById('customer-search');
            const customerSuggestionsBox = document.getElementById('customer-suggestions');
            const itemsList = document.getElementById('items-list');
            const itemsHeader = document.querySelector('.items-header');
            const addItemForm = document.getElementById('add-item-form');
            const form = document.getElementById('delivery-note-form');
            // DELETED GST-related const declarations here

            let selectedProductIds = new Set();
            let selectedCustomerId = null;
            let itemIndex = 0;
            let productStockMap = new Map(products.map(p => [p.id, p.stock]));
            let modalInstance = new bootstrap.Modal(document.getElementById('addItemModal'));
            let hasShownLossAlert = false;

            // Initial totals calculation
            calculateTotals();

            // Modal cleanup
            function cleanUpModal() {
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }

            modalInstance._element.addEventListener('hidden.bs.modal', cleanUpModal);

            function addItemRow(product = null) {
                if (product && selectedProductIds.has(product.id)) {
                    const existingRow = itemsList.querySelector(`[data-product-id="${product.id}"]`);
                    if (existingRow) {
                        existingRow.classList.add('border-primary', 'shadow-sm');
                        setTimeout(() => existingRow.classList.remove('border-primary', 'shadow-sm'), 1500);
                    }
                    return;
                }
                if (product) selectedProductIds.add(product.id);
                const row = document.createElement('div');
                row.className = 'item-row';
                row.setAttribute('data-product-id', product ? product.id : '');
                const safeName = product ? product.name.replace(/"/g, '"') : '';
                row.innerHTML = `
                <div>
                    <div class="item-name-display">${safeName}</div>
                    <div class="stock-info">${product ? `Stock: ${product.stock}` : ''}</div>
                    <input type="hidden" name="items[${itemIndex}][product_id]" class="item-id-input" ${product ? `value="${product.id}"` : ''}>
                    <div class="suggestions-container">
                        <input type="text" class="form-control item-search" placeholder="Type item name to search..." value="${safeName}">
                        <div class="list-group product-suggestions" style="display: none;"></div>
                    </div>
                </div>
                <div>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity-input" placeholder="Qty" min="1" ${product ? `max="${product.stock}" data-stock="${product.stock}"` : ''} required>
                    <div class="validation-error"></div>
                </div>
                <div>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" name="items[${itemIndex}][price]" class="form-control price-input" placeholder="Price" ${product ? `value="${product.price}" data-original-price="${product.price}"` : ''} min="0" step="0.01" required>
                    </div>
                    <div class="validation-error"></div>
                </div>
                <div>
                    <div class="input-group">
                        <input type="number" name="items[${itemIndex}][discount]" class="form-control discount-input" value="0" min="0" max="100" step="0.01">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="validation-error"></div>
                </div>
                <div>
                    <input type="text" name="items[${itemIndex}][itemcode]" class="form-control itemcode-input mb-2" placeholder="Item Code">
                    <input type="text" name="items[${itemIndex}][secondary_itemcode]" class="form-control itemcode-input" placeholder="Secondary Code">
                </div>
                <div class="remove-item-btn">
                    <button type="button" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></button>
                </div>
            `;
                itemsList.appendChild(row);
                itemsHeader.style.display = 'grid';
                itemIndex++;
                if (product) calculateTotals();
            }

            function reindexItemInputs() {
                itemsList.querySelectorAll('.item-row').forEach((row, index) => {
                    row.querySelectorAll('input, select').forEach(input => {
                        if (input.name) input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
                    });
                });
            }

            // *** SIMPLIFIED calculateTotals() FUNCTION ***
            function calculateTotals() {
                let subtotal = 0;
                itemsList.querySelectorAll('.item-row').forEach(row => {
                    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                    const price = parseFloat(row.querySelector('.price-input').value) || 0;
                    const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
                    if (quantity > 0 && price >= 0) {
                        const lineTotal = quantity * price;
                        const discountAmount = lineTotal * (discount / 100);
                        subtotal += lineTotal - discountAmount;
                    }
                });

                // Tax calculation is removed. Grand Total is now the same as Subtotal.
                const grandTotal = subtotal;

                document.getElementById('subtotal').textContent = `₹${subtotal.toFixed(2)}`;
                // The #total_tax element is gone, so we don't update it.
                document.getElementById('grand_total').textContent = `₹${grandTotal.toFixed(2)}`;
            }

            // *** DELETED THE ENTIRE updateGSTFields() FUNCTION ***

            function validateRow(row) {
                let rowIsValid = true;
                // Item
                const itemIdInput = row.querySelector('.item-id-input');
                const itemSearchInput = row.querySelector('.item-search');
                const itemError = row.querySelector('.stock-info');
                itemError.textContent = '';
                if (!itemIdInput.value) {
                    itemError.textContent = 'Please select an item.';
                    rowIsValid = false;
                }

                // Quantity
                const qtyInput = row.querySelector('.quantity-input');
                const qtyError = qtyInput.nextElementSibling;
                const quantity = parseFloat(qtyInput.value);
                const stock = parseFloat(qtyInput.dataset.stock);
                qtyError.textContent = '';
                if (isNaN(quantity) || quantity <= 0) {
                    qtyError.textContent = 'Must be > 0.';
                    rowIsValid = false;
                } else if (quantity > stock) {
                    qtyError.textContent = `Max stock: ${stock}`;
                    rowIsValid = false;
                }

                // Price
                const priceInput = row.querySelector('.price-input');
                const priceError = priceInput.closest('.input-group').nextElementSibling;
                const price = parseFloat(priceInput.value);
                priceError.textContent = '';
                if (isNaN(price) || price < 0) {
                    priceError.textContent = 'Cannot be negative.';
                    rowIsValid = false;
                }

                // Discount
                const discountInput = row.querySelector('.discount-input');
                const discountError = discountInput.closest('.input-group').nextElementSibling;
                const discount = parseFloat(discountInput.value);
                discountError.textContent = '';
                if (isNaN(discount) || discount < 0 || discount > 100) {
                    discountError.textContent = 'Must be 0-100.';
                    rowIsValid = false;
                }

                return rowIsValid;
            }

            // Customer Search Logic
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
                        item.innerHTML = customer.name;
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

            // Item Search Logic
            itemsList.addEventListener('input', function(e) {
                if (e.target.classList.contains('item-search')) {
                    const row = e.target.closest('.item-row');
                    const suggestionsBox = row.querySelector('.product-suggestions');
                    const query = e.target.value.toLowerCase();
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
                                e.target.value = product.name;
                                const itemIdInput = row.querySelector('.item-id-input');
                                const quantityInput = row.querySelector('.quantity-input');
                                const priceInput = row.querySelector('.price-input');
                                itemIdInput.value = product.id;
                                quantityInput.dataset.stock = product.stock;
                                quantityInput.max = product.stock;
                                priceInput.value = product.price;
                                priceInput.dataset.originalPrice = product.price;
                                row.setAttribute('data-product-id', product.id);
                                selectedProductIds.add(product.id);
                                suggestionsBox.style.display = 'none';
                                quantityInput.dispatchEvent(new Event('input'));
                                calculateTotals();
                            });
                            suggestionsBox.appendChild(item);
                        });
                        suggestionsBox.style.display = 'block';
                    } else {
                        suggestionsBox.style.display = 'none';
                    }
                }
                if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input') || e.target.classList.contains('discount-input')) {
                    const row = e.target.closest('.item-row');
                    validateRow(row);
                    calculateTotals();
                }
            });

            // Global Item Search Logic
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
                            addItemRow(product);
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

            // Click Outside to Hide Suggestions
            document.addEventListener('click', function(e) {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && sidebar.contains(e.target)) return;
                if (!customerSuggestionsBox.contains(e.target) && e.target !== customerSearchInput) {
                    customerSuggestionsBox.style.display = 'none';
                }
                if (!suggestionsBox.contains(e.target) && e.target !== searchInput) {
                    suggestionsBox.style.display = 'none';
                }
                document.querySelectorAll('.product-suggestions').forEach(box => {
                    if (!box.contains(e.target) && !e.target.classList.contains('item-search')) {
                        box.style.display = 'none';
                    }
                });
            });

            // Remove Item Row
            itemsList.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.remove-item-btn button');
                if (removeBtn) {
                    const row = removeBtn.closest('.item-row');
                    const productId = parseInt(row.dataset.productId);
                    if (productId) selectedProductIds.delete(productId);
                    row.remove();
                    if (itemsList.children.length === 0) {
                        itemsHeader.style.display = 'none';
                    }
                    reindexItemInputs();
                    calculateTotals();
                }
            });

            // Price Validation
            itemsList.addEventListener('blur', e => {
                const priceInput = e.target.closest('.price-input');
                if (priceInput) {
                    const salePrice = parseFloat(priceInput.value);
                    const originalPrice = parseFloat(priceInput.dataset.originalPrice);
                    if (!isNaN(salePrice) && salePrice < originalPrice && !hasShownLossAlert) {
                        hasShownLossAlert = true;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Potential Loss',
                            text: `The price (₹${salePrice.toFixed(2)}) is less than the original price (₹${originalPrice.toFixed(2)}). This may result in a loss.`,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            hasShownLossAlert = false;
                        });
                    }
                }
            }, true);

            // *** DELETED GST EVENT LISTENER ***
            // gstTypeSelect.addEventListener('change', updateGSTFields);

            // Form Submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                let allRowsValid = true;
                let errorMessages = [];

                if (!selectedCustomerId) {
                    errorMessages.push('Please select a customer.');
                }

                if (itemsList.children.length === 0) {
                    errorMessages.push('Please add at least one item.');
                }

                itemsList.querySelectorAll('.item-row').forEach(row => {
                    if (!validateRow(row)) {
                        allRowsValid = false;
                    }
                });

                if (!allRowsValid) {
                    errorMessages.push('Please fix all validation errors in the item rows.');
                }

                if (errorMessages.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: '<ul>' + errorMessages.map(msg => `<li>${msg}</li>`).join('') + '</ul>'
                    });
                    return;
                }

                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Creating...`;

                const formData = new FormData(this);
                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: data.messageType || 'success',
                                title: 'Success!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(() => window.location.href = "{{ route('delivery_notes.index') }}", 2000);
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
                        submitButton.textContent = 'Create Delivery Note';
                    });
            });

            // Add Item Form Submission
            addItemForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch("{{ route('products.store') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
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
                            addItemRow(newProduct);
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Item added successfully!'
                            });
                        } else {
                            for (const [field, message] of Object.entries(data.errors)) {
                                document.getElementById(`${field}-error`).textContent = message[0];
                            }
                        }
                    })
                    .catch(error => console.error('Error adding item:', error));
            });
        }
    </script>
</body>

@include('layout.footer')