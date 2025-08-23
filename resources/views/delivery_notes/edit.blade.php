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

    /* Enhanced validation styling */
    .form-control.is-valid {
        border-color: #198754;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 2.89 2.89 2.89-2.89.94.94L5.8 9.66z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 1.4 1.4L5.8 7.4'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .form-control.is-valid:focus,
    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }

    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    /* Price field specific styling */
    .price-input:required {
        border-left: 3px solid #dc3545;
    }

    .price-input:required:valid {
        border-left: 3px solid #198754;
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

<body class="act-delivery-notes-edit">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            <div id="alert-container"></div>
            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                    <h1 class="mb-0 h5">Edit Delivery Note: {{ $deliveryNote->delivery_note_number }}</h1>
                    <a href="{{ route('delivery_notes.index') }}" class="btn btn-light btn-sm">Back to List</a>
                </div>
                <div class="card-body p-4">
                    <form method="POST" id="delivery-note-form">
                        @csrf
                        @method('PUT')
                        <!-- Main Details -->
                        <div class="card form-section p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-md-6 suggestions-container">
                                    <label for="customer-search" class="form-label">Customer</label>
                                    <input type="text" id="customer-search" class="form-control" placeholder="Type customer name to search..." value="{{ $deliveryNote->customer->name }}" required>
                                    <input type="hidden" name="customer_id" id="customer_id" value="{{ $deliveryNote->customer_id }}">
                                    <div id="customer-suggestions" class="list-group customer-suggestions" style="display: none;"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="ref_no" class="form-label">Ref. No (Challan Number)</label>
                                    <input type="text" name="ref_no" id="ref_no" class="form-control" placeholder="Enter challan number" value="{{ $deliveryNote->ref_no }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="purchase_number" class="form-label">Purchase Number</label>
                                    <input type="text" name="purchase_number" id="purchase_number" class="form-control" placeholder="Enter purchase order number" value="{{ $deliveryNote->purchase_number }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="purchase_date" class="form-label">Purchase Date</label>
                                    <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ $deliveryNote->purchase_date }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="contact_person" class="form-label">Contact Person</label>
                                    <input type="text" name="contact_person" id="contact_person" class="form-control" placeholder="Enter contact person name" value="{{ $deliveryNote->contact_person }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="delivery_date" class="form-label">Delivery Date</label>
                                    <input type="date" name="delivery_date" id="delivery_date" class="form-control" value="{{ $deliveryNote->delivery_date }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="gst_type" class="form-label">GST Type</label>
                                    <select name="gst_type" id="gst_type" class="form-select" required>
                                        <option value="" disabled {{ !$deliveryNote->gst_type ? 'selected' : '' }}>Select GST Type</option>
                                        <option value="CGST" {{ $deliveryNote->gst_type == 'CGST' ? 'selected' : '' }}>CGST/SGST</option>
                                        <option value="IGST" {{ $deliveryNote->gst_type == 'IGST' ? 'selected' : '' }}>IGST</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="gst-fields-container">
                                    @if($deliveryNote->gst_type == 'CGST')
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="cgst" class="form-label">CGST (%)</label>
                                            <input type="number" name="cgst" id="cgst" class="form-control gst-input" placeholder="e.g. 9" value="{{ $deliveryNote->cgst }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label for="sgst" class="form-label">SGST (%)</label>
                                            <input type="number" name="sgst" id="sgst" class="form-control gst-input" placeholder="e.g. 9" value="{{ $deliveryNote->sgst }}" required>
                                        </div>
                                    </div>
                                    @elseif($deliveryNote->gst_type == 'IGST')
                                    <label for="igst" class="form-label">IGST (%)</label>
                                    <input type="number" name="igst" id="igst" class="form-control gst-input" placeholder="e.g. 18" value="{{ $deliveryNote->igst }}" required>
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Add any additional notes or terms...">{{ $deliveryNote->description }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="e.g., Vehicle number, contact person, etc.">{{ $deliveryNote->notes }}</textarea>
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
                            <div class="items-header">
                                <div>Item</div>
                                <div>Quantity</div>
                                <div>Price</div>
                                <div>Discount</div>
                                <div>Item Codes</div>
                                <div>Action</div>
                            </div>
                            <div id="items-list">
                                @foreach($deliveryNote->items as $index => $item)
                                <div class="item-row" data-product-id="{{ $item->product_id }}">
                                    <div>
                                        <div class="item-name-display">{{ $item->product->name }}</div>
                                        <div class="stock-info">Stock: {{ $item->product->stock + $item->quantity }}</div>
                                        <input type="hidden" name="items[{{ $index }}][product_id]" class="item-id-input" value="{{ $item->product_id }}">
                                    </div>
                                    <div>
                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" placeholder="Qty" min="1" max="{{ $item->product->stock + $item->quantity }}" data-stock="{{ $item->product->stock + $item->quantity }}" value="{{ $item->quantity }}" required>
                                        <div class="validation-error"></div>
                                    </div>
                                    <div>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" name="items[{{ $index }}][price]" class="form-control price-input" placeholder="Price" value="{{ $item->price }}" data-original-price="{{ $item->product->price }}" min="0" step="0.01" required>
                                        </div>
                                        <div class="validation-error"></div>
                                    </div>
                                    <div>
                                        <div class="input-group">
                                            <input type="number" name="items[{{ $index }}][discount]" class="form-control discount-input" value="{{ $item->discount }}" min="0" max="100" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <div class="validation-error"></div>
                                    </div>
                                    <div>
                                        <input type="text" name="items[{{ $index }}][itemcode]" class="form-control itemcode-input mb-2" placeholder="Item Code" value="{{ $item->itemcode }}">
                                        <input type="text" name="items[{{ $index }}][secondary_itemcode]" class="form-control itemcode-input" placeholder="Secondary Code" value="{{ $item->secondary_itemcode }}">
                                    </div>
                                    <div class="remove-item-btn">
                                        <button type="button" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Final Section: Totals -->
                        <div class="row mt-4 g-4">
                            <div class="col-lg-7"></div>
                            <div class="col-lg-5">
                                <div class="totals-card h-100">
                                    <div class="row mb2">
                                        <div class="col-7">Subtotal</div>
                                        <div class="col-5 text-end" id="subtotal">₹0.00</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-7">Total Tax (GST)</div>
                                        <div class="col-5 text-end" id="total_tax">₹0.00</div>
                                    </div>
                                    <hr>
                                    <div class="row grand-total">
                                        <div class="col-7">Grand Total</div>
                                        <div class="col-5 text-end" id="grand_total">₹0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-secondary btn-lg" id="update-btn">
                                Update Delivery Note Only
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg" id="convert-btn">
                                Update & Convert to Invoice
                            </button>
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
            // --- INITIAL SETUP ---
            const products = @json($products);
            const customers = @json($customers);

            // Form and Buttons
            const form = document.getElementById('delivery-note-form');
            const updateBtn = document.getElementById('update-btn');
            const convertBtn = document.getElementById('convert-btn');

            // Main Containers
            const itemsList = document.getElementById('items-list');
            const itemsHeader = document.querySelector('.items-header');

            // Search and Suggestions
            const searchInput = document.getElementById('item-search');
            const suggestionsBox = document.getElementById('item-suggestions');
            const customerSearchInput = document.getElementById('customer-search');
            const customerSuggestionsBox = document.getElementById('customer-suggestions');

            // GST Fields
            const gstTypeSelect = document.getElementById('gst_type');
            const gstFieldsContainer = document.getElementById('gst-fields-container');

            // Modal
            const addItemForm = document.getElementById('add-item-form');
            const modalInstance = new bootstrap.Modal(document.getElementById('addItemModal'));

            // State Management
            let selectedProductIds = new Set(@json($deliveryNote -> items -> pluck('product_id') -> toArray()));
            let selectedCustomerId = {{ $deliveryNote-> customer_id ?? 'null'
        }};
        let itemIndex = {{ $deliveryNote-> items -> count() }}; // Start index from existing items
        let productStockMap = new Map(products.map(p => [p.id, p.stock]));

        // --- CORE FUNCTIONS ---

        function reindexItemInputs() {
            itemsList.querySelectorAll('.item-row').forEach((row, index) => {
                row.querySelectorAll('input, select').forEach(input => {
                    if (input.name) {
                        input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
                    }
                });
            });
        }

        function calculateTotals() {
            let subtotal = 0;
            itemsList.querySelectorAll('.item-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.quantity-input')?.value) || 0;
                const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
                const discount = parseFloat(row.querySelector('.discount-input')?.value) || 0;
                if (quantity > 0 && price >= 0) {
                    const lineTotal = quantity * price;
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
            // Get existing values if they exist, otherwise use defaults
            let cgstValue = document.querySelector('input[name="cgst"]')?.value || '{{ $deliveryNote->cgst ?? 9 }}';
            let sgstValue = document.querySelector('input[name="sgst"]')?.value || '{{ $deliveryNote->sgst ?? 9 }}';
            let igstValue = document.querySelector('input[name="igst"]')?.value || '{{ $deliveryNote->igst ?? 18 }}';

            gstFieldsContainer.innerHTML = ''; // Clear previous fields
            if (gstType === 'CGST') {
                gstFieldsContainer.innerHTML = `
                <div class="row">
                    <div class="col-6">
                        <label for="cgst" class="form-label">CGST (%)</label>
                        <input type="number" name="cgst" id="cgst" class="form-control gst-input" value="${cgstValue}" required>
                    </div>
                    <div class="col-6">
                        <label for="sgst" class="form-label">SGST (%)</label>
                        <input type="number" name="sgst" id="sgst" class="form-control gst-input" value="${sgstValue}" required>
                    </div>
                </div>`;
            } else if (gstType === 'IGST') {
                gstFieldsContainer.innerHTML = `
                <label for="igst" class="form-label">IGST (%)</label>
                <input type="number" name="igst" id="igst" class="form-control gst-input" value="${igstValue}" required>`;
            }
            // Re-attach event listeners to new GST inputs
            document.querySelectorAll('.gst-input').forEach(input => {
                input.addEventListener('input', calculateTotals);
            });
            calculateTotals();
        }

        function validateRow(row) {
            let rowIsValid = true;
            
            // Clear previous validation errors
            row.querySelectorAll('.validation-error').forEach(error => error.textContent = '');
            
            // Validate quantity
            const quantityInput = row.querySelector('.quantity-input');
            const quantity = parseInt(quantityInput.value);
            if (isNaN(quantity) || quantity <= 0) {
                row.querySelector('.quantity-input').closest('div').nextElementSibling.textContent = 'Quantity must be greater than 0.';
                rowIsValid = false;
            }
            
            // Validate price - ENHANCED VALIDATION
            const priceInput = row.querySelector('.price-input');
            const price = parseFloat(priceInput.value);
            if (isNaN(price) || price <= 0) {
                priceInput.closest('.input-group').nextElementSibling.textContent = 'Price must be greater than 0.';
                rowIsValid = false;
            } else if (price < 0.01) {
                priceInput.closest('.input-group').nextElementSibling.textContent = 'Price must be at least ₹0.01.';
                rowIsValid = false;
            }
            
            // Validate discount
            const discountInput = row.querySelector('.discount-input');
            const discount = parseFloat(discountInput.value);
            if (isNaN(discount) || discount < 0 || discount > 100) {
                discountInput.closest('.input-group').nextElementSibling.textContent = 'Discount must be between 0 and 100.';
                rowIsValid = false;
            }
            
            return rowIsValid;
        }

        function validateFormForInvoice() {
            let isValid = true;
            let errorMessages = [];

            if (!selectedCustomerId) {
                errorMessages.push('A customer must be selected.');
                isValid = false;
            }
            if (itemsList.children.length === 0) {
                errorMessages.push('At least one item is required to create an invoice.');
                isValid = false;
            }
            
            // Enhanced item validation with specific price checks
            let hasValidPrices = true;
            itemsList.querySelectorAll('.item-row').forEach((row, index) => {
                if (!validateRow(row)) {
                    isValid = false;
                }
                
                // Additional price validation
                const priceInput = row.querySelector('.price-input');
                const price = parseFloat(priceInput.value);
                if (isNaN(price) || price <= 0) {
                    hasValidPrices = false;
                    errorMessages.push(`Item ${index + 1}: Price must be greater than 0.`);
                }
                
                // Check if price field is empty
                if (!priceInput.value.trim()) {
                    hasValidPrices = false;
                    errorMessages.push(`Item ${index + 1}: Price field cannot be empty.`);
                }
            });
            
            if (!hasValidPrices) {
                isValid = false;
            }

            // Enhanced financial validation
            const gstType = gstTypeSelect.value;
            if (!gstType) {
                errorMessages.push('GST Type must be selected for an invoice.');
                isValid = false;
            } else if (gstType === 'CGST') {
                const cgst = document.getElementById('cgst')?.value;
                const sgst = document.getElementById('sgst')?.value;
                if (!cgst || cgst <= 0) {
                    errorMessages.push('CGST value is required and must be greater than 0.');
                    isValid = false;
                }
                if (!sgst || sgst <= 0) {
                    errorMessages.push('SGST value is required and must be greater than 0.');
                    isValid = false;
                }
            } else if (gstType === 'IGST') {
                const igst = document.getElementById('igst')?.value;
                if (!igst || igst <= 0) {
                    errorMessages.push('IGST value is required and must be greater than 0.');
                    isValid = false;
                }
            }

            // Check if delivery note is already invoiced (additional safety check)
            const isInvoiced = {{ $deliveryNote->is_invoiced ? 'true' : 'false' }};
            if (isInvoiced) {
                errorMessages.push('This delivery note has already been converted to an invoice.');
                isValid = false;
            }

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Cannot Convert to Invoice',
                    html: `Please fix the following issues:<br><ul class="text-start mt-2">${errorMessages.map(msg => `<li>${msg}</li>`).join('')}</ul>`,
                });
            }
            return isValid;
        }

        function addItemRow(product) {
            if (selectedProductIds.has(product.id)) {
                const existingRow = itemsList.querySelector(`[data-product-id="${product.id}"]`);
                if (existingRow) {
                    existingRow.classList.add('border-primary', 'shadow-sm');
                    setTimeout(() => existingRow.classList.remove('border-primary', 'shadow-sm'), 1500);
                }
                return;
            }
            selectedProductIds.add(product.id);
            const row = document.createElement('div');
            row.className = 'item-row';
            row.setAttribute('data-product-id', product.id);
            row.innerHTML = `
                <div><div class="item-name-display">${product.name}</div><div class="stock-info">Stock: ${product.stock}</div><input type="hidden" name="items[${itemIndex}][product_id]" value="${product.id}"></div>
                <div><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity-input" placeholder="Qty" min="1" required><div class="validation-error"></div></div>
                <div><div class="input-group"><span class="input-group-text">₹</span><input type="number" name="items[${itemIndex}][price]" class="form-control price-input" value="${product.price}" required><div class="validation-error"></div></div></div>
                <div><div class="input-group"><input type="number" name="items[${itemIndex}][discount]" class="form-control discount-input" value="0"><span class="input-group-text">%</span></div></div>
                <div><input type="text" name="items[${itemIndex}][itemcode]" class="form-control itemcode-input mb-2" value="${product.itemcode || ''}"><input type="text" name="items[${itemIndex}][secondary_itemcode]" class="form-control"></div>
                <div class="remove-item-btn"><button type="button" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></button></div>
            `;
            itemsList.appendChild(row);
            reindexItemInputs();
            itemIndex++;
        }

        // --- EVENT LISTENERS ---

        updateBtn.addEventListener('click', function (e) {
            e.preventDefault();
            form.action = "{{ route('delivery_notes.update_only', $deliveryNote) }}";
            reindexItemInputs();
            Swal.fire({
                title: 'Confirm Update', text: "This will save changes to the delivery note.",
                icon: 'info', showCancelButton: true, confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });

        convertBtn.addEventListener('click', function (e) {
            e.preventDefault();
            form.action = "{{ route('delivery_notes.convert_to_invoice', $deliveryNote) }}";
            reindexItemInputs();
            
            // Additional validation before calling validateFormForInvoice
            if (!validateAllPriceFields()) {
                return;
            }
            
            if (!validateFormForInvoice()) return;

            const submitButton = this;
            submitButton.disabled = true;
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Converting...`;

            const formData = new FormData(form);
            fetch(form.action, {
                method: 'POST', body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Success!', text: data.message, timer: 2000, showConfirmButton: false })
                            .then(() => window.location.href = "{{ route('invoices.index') }}");
                    } else {
                        let errorHtml = `<ul>${Object.values(data.errors || {}).map(e => `<li>${e[0]}</li>`).join('') || `<li>${data.message || 'Unknown error.'}</li>`}</ul>`;
                        Swal.fire('Validation Failed', errorHtml, 'error');
                    }
                })
                .catch(error => Swal.fire('Submission Error', 'Could not connect to the server.', 'error'))
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Update & Convert to Invoice';
                });
        });

        // NEW FUNCTION: Validate all price fields before form submission
        function validateAllPriceFields() {
            let allPricesValid = true;
            const priceInputs = document.querySelectorAll('.price-input');
            
            priceInputs.forEach((input, index) => {
                const price = parseFloat(input.value);
                const row = input.closest('.item-row');
                const errorSpan = row.querySelector('.validation-error');
                
                // Clear previous errors
                if (errorSpan) errorSpan.textContent = '';
                
                // Check if price is empty
                if (!input.value.trim()) {
                    errorSpan.textContent = 'Price is required for invoice conversion.';
                    input.classList.add('is-invalid');
                    allPricesValid = false;
                    return;
                }
                
                // Check if price is valid number and greater than 0
                if (isNaN(price) || price <= 0) {
                    errorSpan.textContent = 'Price must be greater than 0.';
                    input.classList.add('is-invalid');
                    allPricesValid = false;
                    return;
                }
                
                // Price is valid, remove error styling
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            });
            
            if (!allPricesValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Price Validation Failed',
                    text: 'Please ensure all items have valid prices before converting to invoice.',
                });
            }
            
            return allPricesValid;
        }

        customerSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            customerSuggestionsBox.innerHTML = '';
            if (query.length < 1) { customerSuggestionsBox.style.display = 'none'; return; }
            const filtered = customers.filter(c => c.name.toLowerCase().includes(query));
            filtered.forEach(customer => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.textContent = customer.name;
                item.addEventListener('click', () => {
                    customerSearchInput.value = customer.name;
                    document.getElementById('customer_id').value = customer.id;
                    selectedCustomerId = customer.id;
                    customerSuggestionsBox.style.display = 'none';
                });
                customerSuggestionsBox.appendChild(item);
            });
            customerSuggestionsBox.style.display = filtered.length > 0 ? 'block' : 'none';
        });

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            suggestionsBox.innerHTML = '';
            if (query.length < 1) { suggestionsBox.style.display = 'none'; return; }
            const filtered = products.filter(p => p.name.toLowerCase().includes(query) && !selectedProductIds.has(p.id));
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
            suggestionsBox.style.display = filtered.length > 0 ? 'block' : 'none';
        });

        itemsList.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.remove-item-btn button');
            if (removeBtn) {
                const row = removeBtn.closest('.item-row');
                const productId = parseInt(row.dataset.productId);
                if (productId) selectedProductIds.delete(productId);
                row.remove();
                if (itemsList.children.length === 0) itemsHeader.style.display = 'none';
                reindexItemInputs();
                calculateTotals();
            }
        });

        itemsList.addEventListener('input', e => {
            if (e.target.matches('.quantity-input, .price-input, .discount-input')) {
                calculateTotals();
                
                // Real-time price validation
                if (e.target.matches('.price-input')) {
                    validatePriceField(e.target);
                }
            }
        });

        // NEW FUNCTION: Real-time price validation
        function validatePriceField(priceInput) {
            const price = parseFloat(priceInput.value);
            const row = priceInput.closest('.item-row');
            const errorSpan = row.querySelector('.validation-error');
            
            // Clear previous validation states
            priceInput.classList.remove('is-valid', 'is-invalid');
            
            // Check if price is empty
            if (!priceInput.value.trim()) {
                if (errorSpan) errorSpan.textContent = 'Price is required for invoice conversion.';
                priceInput.classList.add('is-invalid');
                return false;
            }
            
            // Check if price is valid number and greater than 0
            if (isNaN(price) || price <= 0) {
                if (errorSpan) errorSpan.textContent = 'Price must be greater than 0.';
                priceInput.classList.add('is-invalid');
                return false;
            }
            
            // Price is valid
            if (errorSpan) errorSpan.textContent = '';
            priceInput.classList.add('is-valid');
            return true;
        }

        document.addEventListener('click', e => {
            if (!customerSuggestionsBox.contains(e.target) && e.target !== customerSearchInput) customerSuggestionsBox.style.display = 'none';
            if (!suggestionsBox.contains(e.target) && e.target !== searchInput) suggestionsBox.style.display = 'none';
        });

        gstTypeSelect.addEventListener('change', updateGSTFields);

        // Initial Calculations on page load
        calculateTotals();
        updateGSTFields();
        
        // Validate existing price fields on page load
        validateAllExistingPriceFields();
        
        // NEW FUNCTION: Validate all existing price fields on page load
        function validateAllExistingPriceFields() {
            const priceInputs = document.querySelectorAll('.price-input');
            priceInputs.forEach(input => {
                validatePriceField(input);
            });
        }
    });
    </script>
</body>

@include('layout.footer')