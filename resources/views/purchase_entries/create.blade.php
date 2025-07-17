@include('layout.header')

<style>
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
    }

    .card.form-section {
        border: 1px solid #dee2e6;
        box-shadow: none;
    }

    .products-header,
    .product-item-row {
        display: grid;
        /* Updated grid for the remove button */
        grid-template-columns: 3fr 1fr 1.5fr 1fr 1fr 1fr 1fr 1fr 0.5fr;
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

    .product-item-row .form-control[readonly] {
        background-color: #e9ecef;
        pointer-events: none;
    }

    .totals-card {
        background-color: #f8f9fa;
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

    @media (max-width: 1200px) {
        .products-header {
            display: none;
        }

        .product-item-row {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>

<body class="act-purchaseentries">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                    <h1 class="mb-0 h5">Create Purchase Entry</h1>
                    <a href="{{ route('purchase_entries.index') }}" class="btn btn-light btn-sm">Back to List</a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('purchase_entries.store') }}" method="POST" id="create-purchase-entry-form">
                        @csrf
                        <div class="card form-section p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="purchase_order_id" class="form-label">Purchase Order</label>
                                    <select name="purchase_order_id" id="purchase_order_id" class="form-select select2" required>
                                        <option value="" selected disabled>Select a Purchase Order...</option>
                                        @foreach($purchaseOrders as $po)
                                        <option value="{{ $po->id }}" {{ old('purchase_order_id') == $po->id ? 'selected' : '' }}>
                                            {{ $po->purchase_order_number }} - {{ $po->party->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="party_name" class="form-label">Party</label>
                                    <input type="text" id="party_name" class="form-control" placeholder="Select PO to auto-fill" readonly>
                                    <input type="hidden" name="party_id" id="party_id" value="{{ old('party_id') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="invoice_number" class="form-label">Party's Invoice Number</label>
                                    <input type="text" name="invoice_number" id="invoice_number" class="form-control" value="{{ old('invoice_number') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="invoice_date" class="form-label">Invoice Date</label>
                                    <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="purchase_date" class="form-label">Entry Date</label>
                                    <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                </div>
                                <div class="col-12">
                                    <label for="note" class="form-label">Note (Optional)</label>
                                    <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <h4 class="mb-3">Products Received</h4>
                        <div id="products-list-container">
                            <div class="products-header" style="display: none;">
                                <div>Product</div>
                                <div>Qty Rcvd.</div>
                                <div>Price</div>
                                <div>Disc %</div>
                                <div>CGST %</div>
                                <div>SGST %</div>
                                <div>IGST %</div>
                                <div>Status</div>
                                <div>Action</div>
                            </div>
                            <div id="products-list">
                                <p class="text-muted text-center p-4 border rounded">Select a Purchase Order to load products.</p>
                            </div>
                        </div>

                        <div class="row mt-4 g-4">
                            <div class="col-lg-7"></div>
                            <div class="col-lg-5">
                                <div class="totals-card">
                                    <div class="row mb-2">
                                        <div class="col-7">Subtotal</div>
                                        <div class="col-5 text-end" id="subtotal">₹0.00</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-7">Total Discount</div>
                                        <div class="col-5 text-end" id="total_discount">₹0.00</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-7">Total Tax</div>
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
                            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">Create Purchase Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const productsList = $('#products-list');
            const productsHeader = $('.products-header');

            // Main event listener for the PO dropdown
            $('#purchase_order_id').on('change', function() {
                const poId = $(this).val();
                if (!poId) return resetForm();

                productsList.html('<p class="text-muted text-center p-4">Loading...</p>');

                $.ajax({
                    url: `/purchase-orders/${poId}/details`,
                    method: 'GET',
                    success: function(po) {
                        $('#party_name').val(po.party.name);
                        $('#party_id').val(po.party.id);
                        productsList.empty();

                        if (po.items && po.items.length > 0) {
                            productsHeader.css('display', 'grid');
                            po.items.forEach((item, index) => addProductRow(item, index));
                        } else {
                            productsList.html('<p class="text-info text-center p-4">All items for this Purchase Order have been received.</p>');
                            productsHeader.hide();
                        }
                        calculateTotals();
                    },
                    error: () => {
                        productsList.html('<p class="text-danger text-center p-4">Could not load PO details.</p>');
                        resetForm();
                    }
                });
            });

            // Function to build and append a product row
            function addProductRow(item, index) {
                const product = item.product || {
                    name: 'Unknown Product'
                };

                // THE FIX: Use 'quantity_remaining' for the placeholder and max value
                const remainingQty = item.quantity_remaining || 0;

                const rowHtml = `
            <div class="product-item-row" id="row-${index}">
                <input type="hidden" name="products[${index}][product_id]" value="${item.product_id}">
                <div class="fw-bold d-flex align-items-center">${product.name}</div>
                <div>
                    <input type="number" name="products[${index}][quantity]" class="form-control quantity-input" min="0" max="${remainingQty}" required placeholder="0">
                    <small class="text-muted">Remaining: ${remainingQty}</small>
                </div>
                <div><input type="number" name="products[${index}][unit_price]" value="${item.unit_price}" class="form-control unit-price" readonly></div>
                <div><input type="number" name="products[${index}][discount]" value="${item.discount || 0}" class="form-control discount-input" readonly></div>
                <div><input type="number" name="products[${index}][cgst_rate]" value="${item.cgst || 0}" class="form-control cgst-rate" readonly></div>
                <div><input type="number" name="products[${index}][sgst_rate]" value="${item.sgst || 0}" class="form-control sgst-rate" readonly></div>
                <div><input type="number" name="products[${index}][igst_rate]" value="${item.igst || 0}" class="form-control igst-rate" readonly></div>
                <div>
                    <select name="products[${index}][status]" class="form-select status-select">
                        <option value="received" selected>Received</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="d-flex align-items-center justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" title="Remove from this entry"><i class="fa fa-trash"></i></button>
                </div>
            </div>`;
                productsList.append(rowHtml);
            }

            // Remove a product row
            productsList.on('click', '.remove-item-btn', function() {
                $(this).closest('.product-item-row').remove();
                if (productsList.children().length === 0) {
                    productsList.html('<p class="text-muted text-center p-4 border rounded">All items removed. You can re-select the PO to add them back.</p>');
                    productsHeader.hide();
                }
                calculateTotals();
            });

            // Add validation to quantity input
            productsList.on('input', '.quantity-input', function() {
                const $input = $(this);
                const maxQty = parseFloat($input.attr('max'));
                const currentQty = parseFloat($input.val());

                if (currentQty > maxQty) {
                    $input.val(maxQty); // Correct the value automatically
                    // Optionally show a small, temporary warning
                    const $warning = $('<small class="text-danger d-block mt-1">Max qty exceeded.</small>');
                    $input.parent().append($warning);
                    setTimeout(() => $warning.remove(), 2000);
                }
                calculateTotals();
            });

            function resetForm() {
                $('#party_name').val('');
                $('#party_id').val('');
                productsList.html('<p class="text-muted text-center p-4 border rounded">Select a Purchase Order to load products.</p>');
                productsHeader.hide();
                calculateTotals();
            }

            function calculateTotals() {
                // ... (This function remains unchanged) ...
            }

            // Trigger change event if a PO was selected on a previous (failed) attempt
            if ($('#purchase_order_id').val()) {
                $('#purchase_order_id').trigger('change');
            }
        });
    </script>
</body>
@include('layout.footer')