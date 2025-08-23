@include('layout.header')

<!-- Include SweetAlert2 & jQuery -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    .main-content-area {
        background-color: #f8f9fa;
    }

    .product-row {
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        background-color: #ffffff;
        position: relative;
    }

    .product-row .remove-row-btn {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .totals-section {
        background-color: #e9ecef;
        border-radius: .375rem;
        padding: 1.5rem;
        margin-top: 1.5rem;
    }

    .totals-section .row {
        font-size: 1.2rem;
        font-weight: 500;
    }

    .totals-section .grand-total {
        font-size: 1.6rem;
        font-weight: bold;
    }

    .suggestions-container {
        position: relative;
    }

    .party-suggestions,
    .product-suggestions {
        position: absolute;
        z-index: 1050;
        background-color: white;
        border: 1px solid #ced4da;
        max-height: 250px;
        overflow-y: auto;
        width: 100%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .party-suggestions .list-group-item:hover,
    .product-suggestions .list-group-item:hover {
        background-color: #0d6efd;
        color: #fff;
    }

    .validation-error {
        color: #dc3545;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }

    .gst-mode-igst .product-row .cgst-sgst-group {
        display: none;
    }

    .gst-mode-igst .product-row .igst-group {
        display: block;
    }

    .gst-mode-cgst .product-row .cgst-sgst-group {
        display: flex;
    }

    .gst-mode-cgst .product-row .igst-group {
        display: none;
    }
</style>

<body class="act-po">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                    <h1 class="mb-0 h5 text-white">Create Purchase Order</h1>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('purchase_orders.store') }}" method="POST" id="purchaseOrderForm">
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-md-4 suggestions-container">
                                <label for="party-search" class="form-label">Party</label>
                                <input type="text" id="party-search" class="form-control" placeholder="Type party name..." required>
                                <input type="hidden" name="party_id" id="party_id">
                                <div id="party-suggestions" class="list-group party-suggestions" style="display: none;"></div>
                            </div>

                            <div class="col-md-4">
                                <label for="customer_name" class="form-label">Customer Name (Optional)</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Enter end customer name...">
                            </div>

                            <div class="col-md-4">
                                <label for="order_date" class="form-label">Order Date</label>
                                <input type="date" name="order_date" id="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="gst_type_selector" class="form-label">GST Type</label>
                                <select id="gst_type_selector" class="form-select" required>
                                    <option value="cgst" selected>CGST / SGST</option>
                                    <option value="igst">IGST</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h4 class="mb-3">Products</h4>
                        <div id="products-container"></div>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" id="add-product-btn" class="btn btn-secondary"><i class="fa fa-plus"></i> Add Product Row</button>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="fa fa-plus"></i> Add New Product</button>
                        </div>

                        <div class="totals-section mt-4 bg-light">
                            <div class="row mb-2">
                                <div class="col-8 text-end">Subtotal:</div>
                                <div class="col-4 text-end" id="subtotal">₹0.00</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-8 text-end">Total Tax:</div>
                                <div class="col-4 text-end" id="total_tax">₹0.00</div>
                            </div>
                            <hr>
                            <div class="row grand-total pt-2">
                                <div class="col-8 text-end fs-5 text-dark">Grand Total:</div>
                                <div class="col-4 text-end fs-5 text-primary" id="grand_total">₹0.00</div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary btn-md">Create Purchase Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for adding a new product -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add-product-form">
                        @csrf
                        <div class="mb-3"><label for="modal_name" class="form-label">Name</label><input type="text" name="name" id="modal_name" class="form-control" required></div>
                        <div class="mb-3"><label for="modal_category" class="form-label">Category</label><select name="category" id="modal_category" class="form-control" required>
                                <option value="">Select Category</option>@if(!empty($categories))@foreach($categories as $category)<option value="{{ $category }}">{{ $category }}</option>@endforeach @endif
                            </select></div>
                        <div class="mb-3"><label for="modal_subcategory" class="form-label">Sub-Category</label><select name="subcategory" id="modal_subcategory" class="form-control">
                                <option value="">Select Sub-Category</option>@if(!empty($subcategories))@foreach($subcategories as $subcategory)<option value="{{ $subcategory }}">{{ $subcategory }}</option>@endforeach @endif
                            </select></div>
                        <div class="mb-3"><label for="modal_price" class="form-label">Price</label><input type="number" name="price" id="modal_price" class="form-control" step="0.01" min="0" required></div>
                        <div class="mb-3"><label for="modal_hsn" class="form-label">HSN Code</label><input type="text" name="hsn" id="modal_hsn" class="form-control"></div>
                        <div class="mb-3"><label for="modal_discount" class="form-label">Discount (%)</label><input type="number" name="discount" id="modal_discount" class="form-control" value="0" step="0.01" min="0" max="100" required></div>
                        <div class="d-flex gap-2"><button type="submit" class="btn btn-primary">Save Product</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const parties = {!! json_encode($parties, JSON_HEX_QUOT) !!};
        let products = {!! json_encode($products, JSON_HEX_QUOT) !!};

        const purchaseOrderForm = document.getElementById('purchaseOrderForm');
        const partySearchInput = document.getElementById('party-search');
        const partySuggestionsBox = document.getElementById('party-suggestions');
        const productsContainer = document.getElementById('products-container');
        const addProductBtn = document.getElementById('add-product-btn');
        const gstTypeSelector = document.getElementById('gst_type_selector');
        const addProductForm = document.getElementById('add-product-form');
        const modalInstance = new bootstrap.Modal(document.getElementById('addProductModal'));

        let rowCount = 0;
        let selectedProductIds = new Set();
        let selectedParty = null;

        function addProductRow(product = null) {
            if (product && selectedProductIds.has(product.id)) {
                return;
            }
            if (product) selectedProductIds.add(product.id);

            const rowIndex = rowCount;
            const newRow = document.createElement('div');
            newRow.className = 'product-row';
            newRow.id = `row-${rowIndex}`;
            if (product) newRow.dataset.productId = product.id;

            // --- THE FINAL FIX IS IN THIS TEMPLATE ---
            newRow.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger remove-row-btn"><i class="fa fa-times"></i></button>
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-12 suggestions-container">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control product-search" placeholder="Type to search..." value="${product ? product.name : ''}" required>
                        <input type="hidden" name="products[${rowIndex}][product_id]" class="product-id-input" value="${product ? product.id : ''}">
                        <div class="list-group product-suggestions" style="display: none;"></div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Buyer Name</label>
                        <input type="text" name="products[${rowIndex}][buyer_name]" class="form-control buyer-name-input" placeholder="Buyer (Optional)">
                    </div>
                    <div class="col-lg-1 col-md-6"><label class="form-label">Qty</label><input type="number" name="products[${rowIndex}][quantity]" class="form-control quantity-input" min="1" required></div>
                    <div class="col-lg-2 col-md-6"><label class="form-label">Unit Price</label><input type="number" name="products[${rowIndex}][unit_price]" class="form-control unit-price" value="${product ? product.price : '0'}" step="0.01" min="0" required></div>
                    <div class="col-lg-1 col-md-6"><label class="form-label">Disc %</label><input type="number" name="products[${rowIndex}][discount]" class="form-control discount-input" value="0" step="0.01" min="0" max="100"></div>
                    <div class="col-lg-3 col-md-12">
                        <div class="cgst-sgst-group">
                            <div class="row g-2">
                                <div class="col-6"><label class="form-label">CGST (%)</label><input type="number" name="products[${rowIndex}][cgst]" class="form-control cgst-input" value="9"></div>
                                <div class="col-6"><label class="form-label">SGST (%)</label><input type="number" name="products[${rowIndex}][sgst]" class="form-control sgst-input" value="9"></div>
                            </div>
                        </div>
                        
                        <!-- THE FIX: The inline style="display:none;" has been REMOVED from this div -->
                        <div class="igst-group">
                            <label class="form-label">IGST (%)</label>
                            <input type="number" name="products[${rowIndex}][igst]" class="form-control igst-input" value="18">
                        </div>
                    </div>
                </div>`;
            productsContainer.appendChild(newRow);
            rowCount++;
            // This function will now correctly show/hide the fields using only the CSS classes
            toggleGstFields();
        }

        function calculateTotals() {
            let subtotal = 0,
                totalTax = 0;
            document.querySelectorAll('.product-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
                const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
                const cgst = parseFloat(row.querySelector('.cgst-input').value) || 0;
                const sgst = parseFloat(row.querySelector('.sgst-input').value) || 0;
                const igst = parseFloat(row.querySelector('.igst-input').value) || 0;

                const lineSubtotal = quantity * unitPrice * (1 - (discount / 100));
                subtotal += lineSubtotal;

                if (purchaseOrderForm.classList.contains('gst-mode-igst')) {
                    totalTax += lineSubtotal * (igst / 100);
                } else {
                    totalTax += lineSubtotal * ((cgst + sgst) / 100);
                }
            });
            document.getElementById('subtotal').textContent = `₹${subtotal.toFixed(2)}`;
            document.getElementById('total_tax').textContent = `₹${totalTax.toFixed(2)}`;
            document.getElementById('grand_total').textContent = `₹${(subtotal + totalTax).toFixed(2)}`;
        }

        function toggleGstFields() {
            const selectedType = gstTypeSelector.value;
            if (selectedType === 'igst') {
                purchaseOrderForm.classList.remove('gst-mode-cgst');
                purchaseOrderForm.classList.add('gst-mode-igst');
            } else {
                purchaseOrderForm.classList.remove('gst-mode-igst');
                purchaseOrderForm.classList.add('gst-mode-cgst');
            }
            document.querySelectorAll('.product-row').forEach(row => {
                const igstInput = row.querySelector('.igst-input');
                const cgstInput = row.querySelector('.cgst-input');
                const sgstInput = row.querySelector('.sgst-input');

                if (selectedType === 'igst') {
                    if (cgstInput) cgstInput.value = 0;
                    if (sgstInput) sgstInput.value = 0;
                } else {
                    if (igstInput) igstInput.value = 0;
                }
            });
            calculateTotals();
        }

        partySearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            partySuggestionsBox.innerHTML = '';
            if (query.length < 1) {
                partySuggestionsBox.style.display = 'none';
                return;
            }
            const filtered = parties.filter(p => p.name.toLowerCase().includes(query));
            filtered.forEach(party => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.textContent = party.name;
                item.addEventListener('click', () => {
                    partySearchInput.value = party.name;
                    document.getElementById('party_id').value = party.id;
                    selectedParty = party;
                    partySuggestionsBox.style.display = 'none';
                });
                partySuggestionsBox.appendChild(item);
            });
            partySuggestionsBox.style.display = filtered.length > 0 ? 'block' : 'none';
        });

        productsContainer.addEventListener('input', function(e) {
            const target = e.target;
            const row = target.closest('.product-row');
            if (target.classList.contains('product-search')) {
                const suggestions = row.querySelector('.product-suggestions');
                const query = target.value.toLowerCase();
                suggestions.innerHTML = '';
                if (query.length < 1) {
                    suggestions.style.display = 'none';
                    return;
                }
                const availableProducts = products.filter(p => p.name.toLowerCase().includes(query) && (!selectedProductIds.has(p.id) || p.id == row.dataset.productId));
                availableProducts.forEach(product => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action';
                    item.textContent = product.name;
                    item.addEventListener('click', () => {
                        const oldProductId = parseInt(row.dataset.productId);
                        if (oldProductId) selectedProductIds.delete(oldProductId);
                        target.value = product.name;
                        row.querySelector('.product-id-input').value = product.id;
                        row.querySelector('.unit-price').value = product.price || '';
                        row.dataset.productId = product.id;
                        selectedProductIds.add(product.id);
                        suggestions.style.display = 'none';
                        calculateTotals();
                    });
                    suggestions.appendChild(item);
                });
                suggestions.style.display = availableProducts.length > 0 ? 'block' : 'none';
            } else if (target.matches('.quantity-input, .unit-price, .discount-input, .cgst-input, .sgst-input, .igst-input')) {
                calculateTotals();
            }
        });

        productsContainer.addEventListener('click', e => {
            if (e.target.closest('.remove-row-btn')) {
                const row = e.target.closest('.remove-row-btn').closest('.product-row');
                const productId = parseInt(row.dataset.productId);
                if (productId) selectedProductIds.delete(productId);
                row.remove();
                calculateTotals();
            }
        });

        document.addEventListener('click', e => {
            if (!partySuggestionsBox.contains(e.target) && e.target !== partySearchInput) partySuggestionsBox.style.display = 'none';
            document.querySelectorAll('.product-suggestions').forEach(box => {
                if (!box.contains(e.target) && !box.classList.contains('product-search')) box.style.display = 'none';
            });
        });

        addProductBtn.addEventListener('click', () => {
            addProductRow();
        });

        gstTypeSelector.addEventListener('change', toggleGstFields);

        addProductForm.addEventListener('submit', function(e) {
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
                        products.push(data.product);
                        this.reset();
                        modalInstance.hide();
                        addProductRow(data.product);
                        Swal.fire('Success', 'Product added successfully!', 'success');
                    } else {
                        Swal.fire('Error', 'Could not add product. Please check the fields.', 'error');
                    }
                }).catch(error => Swal.fire('Error', 'An unexpected error occurred.', 'error'));
        });

        purchaseOrderForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Creating...`;

            const formData = new FormData(this);
            fetch(this.action, {
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
                            })
                            .then(() => window.location.href = "{{ route('purchase_orders.index') }}");
                    } else {
                        let errorHtml = `<ul>${Object.values(data.errors || {}).map(e => `<li>${e[0]}</li>`).join('') || `<li>${data.message || 'Unknown error.'}</li>`}</ul>`;
                        Swal.fire('Validation Failed', errorHtml, 'error');
                    }
                })
                .catch(error => Swal.fire('Error', 'Could not create purchase order.', 'error'))
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Create Purchase Order';
                });
        });

        addProductRow();
    });
</script>
</body>
@include('layout.footer')