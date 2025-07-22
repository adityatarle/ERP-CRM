<!DOCTYPE html>
<html lang="en">

<head>
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
            align-items: center;
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
        
        /* Added style for readonly inputs */
        .product-item-row .form-control[readonly] {
            background-color: #e9ecef !important; /* Use important to override other styles */
            cursor: not-allowed;
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
                gap: 0.75rem;
            }
        }
    </style>
</head>

<body class="act-receiptnotes">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                    <h1 class="mb-0 h5">Edit Receipt Note</h1>
                    <a href="{{ route('receipt_notes.index') }}" class="btn btn-light btn-sm">Back to List</a>
                </div>
                <div class="card-body p-4">
                    <!-- Update Form -->
                    <form action="{{ route('receipt_notes.update', $receiptNote->id) }}" method="POST" id="edit-receipt-note-form">
                        @csrf
                        @method('PUT')
                        <div class="card form-section p-3 mb-4">
                            <div class="row g-3">
                                
                                <div class="col-md-6">
                                    <label for="purchase_order_id" class="form-label">Purchase Order (Optional)</label>
                                    <input type="text"name="purchase_order_id" id="purchase_order_id" class="form-control" value="{{ $receiptNote->purchase_order_number }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="party_name" class="form-label">Party</label>
                                    <input type="text" id="party_name" class="form-control" value="{{ $receiptNote->party->name }}" readonly>
                                    <input type="hidden" name="party_id" id="party_id" value="{{ $receiptNote->party_id }}">
                                    @error('party_id')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="receipt_number" class="form-label">Receipt Number</label>
                                    <input type="text" name="receipt_number" id="receipt_number" class="form-control" value="{{ old('receipt_number', $receiptNote->receipt_number) }}" required>
                                    @error('receipt_number')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="receipt_date" class="form-label">Receipt Date</label>
                                    <input type="date" name="receipt_date" id="receipt_date" class="form-control" value="{{ old('receipt_date', $receiptNote->receipt_date) }}" required>
                                    @error('receipt_date')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="invoice_number" class="form-label">Invoice Number</label>
                                    <input type="text" name="invoice_number" id="invoice_number" class="form-control" value="{{ old('invoice_number', $receiptNote->invoice_number) }}">
                                    @error('invoice_number')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="invoice_date" class="form-label">Invoice Date</label>
                                    <input type="date" name="invoice_date" id="invoice_date" class="form-control" value="{{ old('invoice_date', $receiptNote->invoice_date) }}">
                                    @error('invoice_date')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12">
                                    <label for="note" class="form-label">Note (Optional)</label>
                                    <textarea name="note" id="note" class="form-control" rows="2" placeholder="Add any additional notes">{{ old('note', $receiptNote->note) }}</textarea>
                                    @error('note')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="mb-3 text-primary">Products Received</h4>
                        <div id="products-list-container">
                            <div class="products-header">
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
                                @foreach($receiptNote->items as $index => $item)
                                <div class="product-item-row" id="row-{{ $index }}">
                                    <input type="hidden" name="products[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                    <div class="fw-bold d-flex align-items-center">{{ $item->product->name }}</div>
                                    <div>
                                        <input type="number" name="products[{{ $index }}][quantity]" class="form-control quantity-input" min="0" max="{{ $item->quantity_available }}" value="{{ old('products.' . $index . '.quantity', $item->quantity) }}" required>
                                        <small class="text-muted">Max: {{ $item->quantity_available }}</small>
                                    </div>
                                    <div><input type="number" name="products[{{ $index }}][unit_price]" class="form-control unit-price" value="{{ old('products.' . $index . '.unit_price', $item->unit_price) }}" step="0.01" required></div>
                                    <div><input type="number" name="products[{{ $index }}][discount]" class="form-control discount-input" value="{{ old('products.' . $index . '.discount', $item->discount ?? $receiptNote->discount) }}" step="0.01" min="0" max="100"></div>
                                    <div><input type="number" name="products[{{ $index }}][cgst_rate]" class="form-control cgst-rate" value="{{ old('products.' . $index . '.cgst_rate', $item->cgst_rate) }}" step="0.01" min="0" max="100"></div>
                                    <div><input type="number" name="products[{{ $index }}][sgst_rate]" class="form-control sgst-rate" value="{{ old('products.' . $index . '.sgst_rate', $item->sgst_rate) }}" step="0.01" min="0" max="100"></div>
                                    <div><input type="number" name="products[{{ $index }}][igst_rate]" class="form-control igst-rate" value="{{ old('products.' . $index . '.igst_rate', $item->igst_rate) }}" step="0.01" min="0" max="100"></div>
                                    <div>
                                        <select name="products[{{ $index }}][status]" class="form-select status-select">
                                            <option value="received" {{ $item->status == 'received' ? 'selected' : '' }}>Received</option>
                                            <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" title="Remove from this receipt"><i class="fa fa-trash"></i></button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <label for="add_product" class="form-label">Add Product</label>
                                <select id="add_product" class="form-select select2">
                                    <option value="" selected disabled>Select a product...</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-name="{{ $product->name }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
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

                        <div class="mt-4 text-end d-flex justify-content-end gap-2">
                             <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">Update Receipt Note</button>
                             <!-- Conversion Form Trigger -->
                             <button type="button" class="btn btn-success btn-lg" id="convert-btn">Convert to Purchase Entry</button>
                        </div>
                    </form>

                    <!-- Conversion Form (Hidden) -->
                    <form action="{{ route('receipt_notes.convert', $receiptNote->id) }}" method="POST" id="convert-receipt-note-form" class="d-none">
                        @csrf
                        @method('POST')
                        {{-- This form will be populated by JavaScript before submission --}}
                    </form>

                </div>
            </div>
        </div>
    </div>

    @include('layout.footer')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            const productsList = $('#products-list');
            const productsHeader = $('.products-header');
            let productIndex = {{ $receiptNote->items->count() }};
            
            // --- HELPER & LOGIC FUNCTIONS ---

            const formatCurrency = (amount) => '₹' + parseFloat(amount || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            // NEW: Function to handle CGST/SGST vs IGST logic
            function updateTaxFields($row) {
                const $cgstInput = $row.find('.cgst-rate');
                const $sgstInput = $row.find('.sgst-rate');
                const $igstInput = $row.find('.igst-rate');

                const cgstVal = parseFloat($cgstInput.val()) || 0;
                const sgstVal = parseFloat($sgstInput.val()) || 0;
                const igstVal = parseFloat($igstInput.val()) || 0;

                if (cgstVal > 0 || sgstVal > 0) {
                    // If CGST or SGST has a value, disable IGST
                    $igstInput.val('').prop('readonly', true);
                } else if (igstVal > 0) {
                    // If IGST has a value, disable CGST and SGST
                    $cgstInput.val('').prop('readonly', true);
                    $sgstInput.val('').prop('readonly', true);
                } else {
                    // If all are empty, enable all
                    $cgstInput.prop('readonly', false);
                    $sgstInput.prop('readonly', false);
                    $igstInput.prop('readonly', false);
                }
            }

            function calculateTotals() {
                let subtotal = 0;
                let totalDiscount = 0;
                let totalCgst = 0;
                let totalSgst = 0;
                let totalIgst = 0;
                let grandTotal = 0;

                $('.product-item-row').each(function() {
                    const $row = $(this);
                    const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                    const unitPrice = parseFloat($row.find('.unit-price').val()) || 0;
                    const discountPercent = parseFloat($row.find('.discount-input').val()) || 0;
                    const cgstRate = parseFloat($row.find('.cgst-rate').val()) || 0;
                    const sgstRate = parseFloat($row.find('.sgst-rate').val()) || 0;
                    const igstRate = parseFloat($row.find('.igst-rate').val()) || 0;

                    if (quantity > 0 && unitPrice >= 0) {
                        const itemSubtotal = quantity * unitPrice;
                        const itemDiscountAmount = itemSubtotal * (discountPercent / 100);
                        const taxableValue = itemSubtotal - itemDiscountAmount;
                        const cgstAmount = taxableValue * (cgstRate / 100);
                        const sgstAmount = taxableValue * (sgstRate / 100);
                        const igstAmount = taxableValue * (igstRate / 100);
                        const itemTotal = taxableValue + cgstAmount + sgstAmount + igstAmount;

                        subtotal += itemSubtotal;
                        totalDiscount += itemDiscountAmount;
                        totalCgst += cgstAmount;
                        totalSgst += sgstAmount;
                        totalIgst += igstAmount;
                        grandTotal += itemTotal;
                    }
                });
                
                $('#subtotal').text(formatCurrency(subtotal));
                $('#total_discount').text(formatCurrency(totalDiscount));
                $('#total_cgst').text(formatCurrency(totalCgst));
                $('#total_sgst').text(formatCurrency(totalSgst));
                $('#total_igst').text(formatCurrency(totalIgst));
                $('#grand_total').text(formatCurrency(grandTotal));
            }

            // --- UI & EVENT HANDLERS ---
            
            if (productsList.children('.product-item-row').length > 0) {
                productsHeader.css('display', 'grid');
            } else {
                productsList.html('<p class="text-muted text-center p-4 border rounded">No products on this receipt note.</p>');
                productsHeader.hide();
            }

            $('#add_product').on('change', function() {
                const productId = $(this).val();
                if (!productId) return;
                const productName = $(this).find('option:selected').data('name');
                if (productsList.find('p').length > 0) productsList.empty();

                const rowHtml = `
                    <div class="product-item-row" id="row-${productIndex}">
                        <input type="hidden" name="products[${productIndex}][product_id]" value="${productId}">
                        <div class="fw-bold d-flex align-items-center">${productName}</div>
                        <div>
                            <input type="number" name="products[${productIndex}][quantity]" class="form-control quantity-input" min="0" max="9999" required placeholder="0">
                            <small class="text-muted">Max: 9999</small>
                        </div>
                        <div><input type="number" name="products[${productIndex}][unit_price]" class="form-control unit-price" step="0.01" required placeholder="0.00"></div>
                        <div><input type="number" name="products[${productIndex}][discount]" class="form-control discount-input" step="0.01" min="0" max="100" placeholder="0"></div>
                        <div><input type="number" name="products[${productIndex}][cgst_rate]" class="form-control cgst-rate" step="0.01" min="0" max="100" placeholder="0"></div>
                        <div><input type="number" name="products[${productIndex}][sgst_rate]" class="form-control sgst-rate" step="0.01" min="0" max="100" placeholder="0"></div>
                        <div><input type="number" name="products[${productIndex}][igst_rate]" class="form-control igst-rate" step="0.01" min="0" max="100" placeholder="0"></div>
                        <div>
                            <select name="products[${productIndex}][status]" class="form-select status-select">
                                <option value="received" selected>Received</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center justify-content-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" title="Remove from this receipt"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>`;
                productsList.append(rowHtml);
                productsHeader.css('display', 'grid');
                productIndex++;
                $(this).val('').trigger('change.select2');
                calculateTotals();
            });

            productsList.on('click', '.remove-item-btn', function() {
                $(this).closest('.product-item-row').remove();
                if (productsList.children('.product-item-row').length === 0) {
                    productsList.html('<p class="text-muted text-center p-4 border rounded">No products on this receipt note.</p>');
                    productsHeader.hide();
                }
                calculateTotals();
            });

            productsList.on('input', '.quantity-input', function() {
                const $input = $(this);
                const maxQty = parseFloat($input.attr('max')) || 9999;
                const currentQty = parseFloat($input.val());
                $input.parent().find('.text-danger').remove();
                if (currentQty > maxQty) {
                    $input.val(maxQty);
                    $input.parent().append('<small class="text-danger d-block mt-1">Max qty exceeded.</small>');
                    setTimeout(() => $input.parent().find('.text-danger').remove(), 2500);
                }
            });

            // --- FORM SUBMISSION LOGIC ---

            $('#convert-btn').on('click', function(e) {
                e.preventDefault();
                const invoiceNumber = $('#invoice_number').val().trim();
                const invoiceDate = $('#invoice_date').val().trim();

                if (!invoiceNumber || !invoiceDate) {
                    alert('Please fill in both Invoice Number and Invoice Date before converting to a purchase entry.');
                    return;
                }
                
                const getAmount = (id) => parseFloat(document.getElementById(id).innerText.replace(/[₹,]/g, '')) || 0;
                const $convertForm = $('#convert-receipt-note-form').empty();
                
                $convertForm.append('@csrf @method("POST")');
                $convertForm.append(`<input type="hidden" name="party_id" value="${$('#party_id').val()}">`);
                $convertForm.append(`<input type="hidden" name="purchase_order_id" value="{{ $receiptNote->purchase_order_id }}">`);
                $convertForm.append(`<input type="hidden" name="receipt_number" value="${$('#receipt_number').val()}">`);
                $convertForm.append(`<input type="hidden" name="receipt_date" value="${$('#receipt_date').val()}">`);
                $convertForm.append(`<input type="hidden" name="invoice_number" value="${invoiceNumber}">`);
                $convertForm.append(`<input type="hidden" name="invoice_date" value="${invoiceDate}">`);
                $convertForm.append(`<input type="hidden" name="note" value="${$('#note').val()}">`);
                
                $('.product-item-row').each(function(index) {
                    const $row = $(this);
                    $convertForm.append(`<input type="hidden" name="products[${index}][product_id]" value="${$row.find('input[name$="[product_id]"]').val()}">`);
                    $convertForm.append(`<input type="hidden" name="products[${index}][quantity]" value="${$row.find('.quantity-input').val()}">`);
                    $convertForm.append(`<input type="hidden" name="products[${index}][unit_price]" value="${$row.find('.unit-price').val()}">`);
                    $convertForm.append(`<input type="hidden" name="products[${index}][discount]" value="${$row.find('.discount-input').val()}">`);
                    $convertForm.append(`<input type="hidden" name="products[${index}][cgst_rate]" value="${$row.find('.cgst-rate').val()}">`);
                    $convertForm.append(`<input type="hidden" name="products[${index}][sgst_rate]" value="${$row.find('.sgst-rate').val()}">`);
                    $convertForm.append(`<input type="hidden" name="products[${index}][igst_rate]" value="${$row.find('.igst-rate').val()}">`);
                    $convertForm.append(`<input type="hidden" name="products[${index}][status]" value="${$row.find('.status-select').val()}">`);
                });
                
                $convertForm.append(`<input type="hidden" name="subtotal" value="${getAmount('subtotal').toFixed(2)}">`);
                $convertForm.append(`<input type="hidden" name="total_discount" value="${getAmount('total_discount').toFixed(2)}">`);
                $convertForm.append(`<input type="hidden" name="total_cgst" value="${getAmount('total_cgst').toFixed(2)}">`);
                $convertForm.append(`<input type="hidden" name="total_sgst" value="${getAmount('total_sgst').toFixed(2)}">`);
                $convertForm.append(`<input type="hidden" name="total_igst" value="${getAmount('total_igst').toFixed(2)}">`);
                $convertForm.append(`<input type="hidden" name="grand_total" value="${getAmount('grand_total').toFixed(2)}">`);
                
                $convertForm.submit();
            });


            // Recalculate totals and update tax fields whenever a relevant input changes
            $(document).on('input', '.quantity-input, .unit-price, .discount-input, .cgst-rate, .sgst-rate, .igst-rate', function() {
                const $row = $(this).closest('.product-item-row');
                // If the changed input is a tax field, update the readonly states
                if ($(this).is('.cgst-rate, .sgst-rate, .igst-rate')) {
                    updateTaxFields($row);
                }
                calculateTotals();
            });

            // --- INITIALIZATION ---

            // Apply tax logic to all existing rows on page load
            $('.product-item-row').each(function() {
                updateTaxFields($(this));
            });

            // Trigger initial calculation on page load
            calculateTotals();
        });
    </script>

</body>
</html>