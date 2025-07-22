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

<body class="act-receiptnotes">
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
                    <h1 class="mb-0 h5">Create Receipt Note</h1>
                    <a href="{{ route('receipt_notes.index') }}" class="btn btn-light btn-sm">Back to List</a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('receipt_notes.store') }}" method="POST" id="create-receipt-note-form">
                        @csrf
                        <div class="card form-section p-3 mb-4">
                            <div class="row g-3">
                               <div class="col-md-6">
                                    <label for="purchase_order_id" class="form-label">Select Purchase Order</label>
                                    <select name="purchase_order_id" id="purchase_order_id" class="form-select select2" required>
                                        <option value="" selected disabled>-- Type to search PO with remaining items --</option>
                                    </select>
                                    <input type="hidden" name="purchase_order_number" id="purchase_order_number" value="{{ old('purchase_order_number') }}">
                                    <small class="form-text text-muted">Only showing purchase orders with remaining items to receive</small>
                                    @error('purchase_order_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="party_name" class="form-label">Party</label>
                                    <input type="text" id="party_name" class="form-control" placeholder="Select PO to auto-fill" readonly>
                                    <input type="hidden" name="party_id" id="party_id" value="{{ old('party_id') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="receipt_number" class="form-label">Receipt Number</label>
                                    <input type="text" name="receipt_number" id="receipt_number" class="form-control" value="{{ old('receipt_number') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="receipt_date" class="form-label">Receipt Date</label>
                                    <input type="date" name="receipt_date" id="receipt_date" class="form-control" value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                                </div>
                                
                                <div class="col-12">
                                    <label for="note" class="form-label">Note (Optional)</label>
                                    <textarea name="note" id="note" class="form-control" rows="2" placeholder="Add any additional notes">{{ old('note') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <h4 class="mb-3 text-primary">Products Received</h4>
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
                                    <div class="row mb-2">
                                        <div class="col-7">Total CGST</div>
                                        <div class="col-5 text-end" id="total_cgst">₹0.00</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-7">Total SGST</div>
                                        <div class="col-5 text-end" id="total_sgst">₹0.00</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-7">Total IGST</div>
                                        <div class="col-5 text-end" id="total_igst">₹0.00</div>
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
                            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">Create Receipt Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for Purchase Order field with AJAX
            $('#purchase_order_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Type to search for purchase orders...',
                allowClear: true,
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route("receipt_notes.search_purchase_orders") }}',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            search: params.term || ''
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                templateResult: function(item) {
                    if (!item.id) {
                        return item.text;
                    }
                    
                    // Create custom template showing PO details
                    var $result = $(
                        '<div class="d-flex justify-content-between align-items-center">' +
                            '<div>' +
                                '<div class="fw-bold text-primary">' + (item.purchase_order_number || '') + '</div>' +
                                '<div class="text-muted small">' + (item.party_name || '') + '</div>' +
                            '</div>' +
                            '<div class="text-end">' +
                                '<span class="badge bg-warning text-dark">' + (item.remaining_count || 0) + ' items pending</span>' +
                            '</div>' +
                        '</div>'
                    );
                    return $result;
                },
                templateSelection: function(item) {
                    if (item.purchase_order_number) {
                        return item.purchase_order_number + ' - ' + item.party_name + ' (' + item.remaining_count + ' items pending)';
                    }
                    return item.text;
                }
            });

            // Load initial data when page loads
            $('#purchase_order_id').select2('open').select2('close');
            const productsList = $('#products-list');
            const productsHeader = $('.products-header');

            // Main event listener for the PO dropdown
            $('#purchase_order_id').on('change', function() {
                const poId = $(this).val();
                if (!poId) return resetForm();

                // Get the selected option data from Select2
                const selectedData = $(this).select2('data')[0];
                
                productsList.html('<p class="text-muted text-center p-4">Loading...</p>');

                // Pre-fill party information from Select2 data if available
                if (selectedData && selectedData.party_name) {
                    $('#party_name').val(selectedData.party_name);
                    $('#party_id').val(selectedData.party_id);
                    $('#purchase_order_number').val(selectedData.purchase_order_number);
                }

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

                const remainingQty = item.quantity_remaining || 0;

                const rowHtml = `
                    <div class="product-item-row" id="row-${index}">
                        <input type="hidden" name="products[${index}][product_id]" value="${item.product_id}">
                        <div class="fw-bold d-flex align-items-center">${product.name}</div>
                        <div>
                            <input type="number" name="products[${index}][quantity]" class="form-control quantity-input" min="0" max="${remainingQty}" required placeholder="0">
                            <small class="text-muted">Remaining: ${remainingQty}</small>
                        </div>
                        <div><input type="number" name="products[${index}][unit_price]" value="" class="form-control unit-price" readonly></div>
                        <div><input type="number" name="products[${index}][discount]" value="" class="form-control discount-input" readonly></div>
                        <div><input type="number" name="products[${index}][cgst_rate]" value="" class="form-control cgst-rate" readonly></div>
                        <div><input type="number" name="products[${index}][sgst_rate]" value="" class="form-control sgst-rate" readonly></div>
                        <div><input type="number" name="products[${index}][igst_rate]" value="" class="form-control igst-rate" readonly></div>
                        <div>
                            <select name="products[${index}][status]" class="form-select status-select">
                                <option value="received" selected>Received</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center justify-content-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" title="Remove from this receipt"><i class="fa fa-trash"></i></button>
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
                    $input.val(maxQty);
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
                let grandSubtotal = 0;
                let grandTotalDiscount = 0;
                let grandTotalCgst = 0;
                let grandTotalSgst = 0;
                let grandTotalIgst = 0;

                const discountRate = parseFloat($('#discount').val()) || 0;

                $('.product-item-row').each(function() {
                    const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
                    const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
                    const cgstRate = parseFloat($(this).find('.cgst-rate').val()) || 0;
                    const sgstRate = parseFloat($(this).find('.sgst-rate').val()) || 0;
                    const igstRate = parseFloat($(this).find('.igst-rate').val()) || 0;

                    if (quantity > 0 && unitPrice >= 0) {
                        const basePrice = quantity * unitPrice;
                        const discountAmount = basePrice * (discountRate / 100);
                        const priceAfterDiscount = basePrice - discountAmount;

                        const cgstAmount = priceAfterDiscount * (cgstRate / 100);
                        const sgstAmount = priceAfterDiscount * (sgstRate / 100);
                        const igstAmount = priceAfterDiscount * (igstRate / 100);

                        grandSubtotal += priceAfterDiscount;
                        grandTotalDiscount += discountAmount;
                        grandTotalCgst += cgstAmount;
                        grandTotalSgst += sgstAmount;
                        grandTotalIgst += igstAmount;
                    }
                });

                $('#subtotal').text('₹' + grandSubtotal.toFixed(2));
                $('#total_discount').text('₹' + grandTotalDiscount.toFixed(2));
                $('#total_cgst').text('₹' + grandTotalCgst.toFixed(2));
                $('#total_sgst').text('₹' + grandTotalSgst.toFixed(2));
                $('#total_igst').text('₹' + grandTotalIgst.toFixed(2));
                $('#grand_total').text('₹' + (grandSubtotal + grandTotalCgst + grandTotalSgst + grandTotalIgst).toFixed(2));
            }

            // Trigger change event if a PO was selected on a previous (failed) attempt
            if ($('#purchase_order_id').val()) {
                $('#purchase_order_id').trigger('change');
            }

            $(document).on('input', '.quantity-input, #discount', calculateTotals);
        });
    </script>
</body>
@include('layout.footer')