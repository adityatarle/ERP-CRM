@include('layout.header')

<style>
    body { background-color: #f4f7f9; }
    .main-content-area { min-height: 100vh; }
    .card-header h1 { font-size: 1.25rem; font-weight: 600; }
    .form-label { font-weight: 500; color: #495057; }
    .card.form-section { border: 1px solid #dee2e6; box-shadow: none; }
    
    .suggestions-container { position: relative; }
    .product-suggestions, #party_suggestions {
        position: absolute; z-index: 1050; background-color: white;
        border: 1px solid #ced4da; border-radius: 0 0 .375rem .375rem;
        max-height: 250px; overflow-y: auto; width: 100%;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .product-suggestions ul, #party_suggestions ul { list-style: none; padding: 0; margin: 0; }
    .product-suggestions li, #party_suggestions li { padding: 10px 15px; cursor: pointer; font-size: 0.9rem; }
    .product-suggestions li:hover, #party_suggestions li:hover { background-color: #0d6efd; color: white; }

    .products-header, .product-item-row {
        display: grid;
        grid-template-columns: 3fr 1fr 1.5fr 1fr 1fr 1fr 1fr 1fr 0.5fr;
        gap: 1rem; align-items: start; padding: 0.75rem 1rem;
    }
    .products-header {
        background-color: #e9ecef; border-radius: .375rem; font-weight: 600;
        font-size: 0.85rem; color: #495057; text-transform: uppercase;
    }
    .product-item-row {
        background-color: #fff; border: 1px solid #dee2e6;
        border-radius: .375rem; margin-bottom: 0.75rem;
    }
    .remove-product-btn { justify-self: end; }

    .totals-card { background-color: #fff; border: 1px solid #dee2e6; border-radius: .375rem; padding: 1.5rem; }
    .totals-card .row { font-size: 1.1rem; }
    .totals-card .grand-total { font-size: 1.4rem; font-weight: bold; }

    @media (max-width: 1200px) {
        .products-header, .product-item-row { grid-template-columns: 1fr 1fr; }
        .products-header { display: none; }
        .remove-product-btn { justify-self: start; margin-top: 1rem; }
    }
    @media (max-width: 768px) {
        .product-item-row { grid-template-columns: 1fr; }
    }
</style>

<body class="act-purchaseentries">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white">
                    <h1 class="mb-0 h5">Edit Purchase Entry</h1>
                    <a href="{{ route('purchase_entries.index') }}" class="btn btn-light btn-sm">Back to List</a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('purchase_entries.update', $purchaseEntry->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card form-section p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="purchase_order_id" class="form-label">Purchase Order</label>
                                    <select name="purchase_order_id" id="purchase_order_id" class="form-select" required>
                                        <option value="">Select Purchase Order</option>
                                        @foreach($purchaseOrders as $po)
                                            <option value="{{ $po->id }}" {{ $purchaseEntry->purchase_order_id == $po->id ? 'selected' : '' }}>
                                                {{ $po->purchase_order_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('purchase_order_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 suggestions-container">
                                    <label for="party_name" class="form-label">Party</label>
                                    <input type="text" id="party_name" name="party_name" class="form-control"
                                        value="{{ $purchaseEntry->party->name }}" placeholder="Search Party" required autocomplete="off">
                                    <input type="hidden" name="party_id" id="party_id" value="{{ $purchaseEntry->party_id }}">
                                    <div id="party_suggestions"></div>
                                    @error('party_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="invoice_number" class="form-label">Invoice Number</label>
                                    <input type="text" name="invoice_number" class="form-control" value="{{ $purchaseEntry->invoice_number }}" required>
                                    @error('invoice_number')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="invoice_date" class="form-label">Invoice Date</label>
                                    <input type="date" name="invoice_date" class="form-control" value="{{ $purchaseEntry->invoice_date }}" required>
                                    @error('invoice_date')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="purchase_date" class="form-label">Purchase Date</label>
                                    <input type="date" name="purchase_date" class="form-control" value="{{ $purchaseEntry->purchase_date }}" required>
                                    @error('purchase_date')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="note" class="form-label">Note (Optional)</label>
                                    <textarea name="note" class="form-control" placeholder="Add any additional notes">{{ $purchaseEntry->note }}</textarea>
                                    @error('note')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <h4 class="mb-3">Products Received</h4>
                        <div id="products-container">
                            <div class="products-header" style="display: {{ $purchaseEntry->items->isEmpty() ? 'none' : 'grid' }};">
                                <div>Product</div>
                                <div>Qty</div>
                                <div>Price</div>
                                <div>Disc (%)</div>
                                <div>CGST (%)</div>
                                <div>SGST (%)</div>
                                <div>IGST (%)</div>
                                <div>Status</div>
                                <div>Action</div>
                            </div>
                            <div id="products-list">
                                @foreach ($purchaseEntry->items as $index => $item)
                                    <div class="product-item-row" id="row-{{ $index }}">
                                        <div class="suggestions-container">
                                            <label class="form-label d-lg-none">Product</label>
                                            <input type="text" name="products[{{ $index }}][product_name]"
                                                class="form-control product-name" placeholder="Search Product"
                                                value="{{ $item->product->name ?? 'N/A' }}" required autocomplete="off">
                                            <input type="hidden" name="products[{{ $index }}][product_id]"
                                                class="product-id" value="{{ $item->product_id }}">
                                            <div class="product-suggestions"></div>
                                            @error("products.$index.product_id")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="form-label d-lg-none">Qty</label>
                                            <input type="number" name="products[{{ $index }}][quantity]"
                                                placeholder="Quantity" class="form-control quantity-input" min="1"
                                                value="{{ $item->quantity }}" required>
                                            @error("products.$index.quantity")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="form-label d-lg-none">Price</label>
                                            <input type="number" name="products[{{ $index }}][unit_price]"
                                                placeholder="Unit Price" class="form-control unit-price" step="0.01" min="0"
                                                value="{{ $item->unit_price }}" required>
                                            @error("products.$index.unit_price")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="form-label d-lg-none">Discount (%)</label>
                                            <input type="number" name="products[{{ $index }}][discount]"
                                                placeholder="Discount %" class="form-control discount-input" step="0.01" min="0" max="100"
                                                value="{{ $item->discount ?? 0 }}">
                                            @error("products.$index.discount")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="form-label d-lg-none">CGST (%)</label>
                                            <input type="number" name="products[{{ $index }}][cgst_rate]"
                                                placeholder="CGST %" class="form-control cgst-rate" step="0.01" min="0"
                                                value="{{ $item->cgst_rate ?? 0 }}">
                                            @error("products.$index.cgst_rate")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="form-label d-lg-none">SGST (%)</label>
                                            <input type="number" name="products[{{ $index }}][sgst_rate]"
                                                placeholder="SGST %" class="form-control sgst-rate" step="0.01" min="0"
                                                value="{{ $item->sgst_rate ?? 0 }}">
                                            @error("products.$index.sgst_rate")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="form-label d-lg-none">IGST (%)</label>
                                            <input type="number" name="products[{{ $index }}][igst_rate]"
                                                placeholder="IGST %" class="form-control igst-rate" step="0.01" min="0"
                                                value="{{ $item->igst_rate ?? 0 }}">
                                            @error("products.$index.igst_rate")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="form-label d-lg-none">Status</label>
                                            <select name="products[{{ $index }}][status]" class="form-select" required>
                                                <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="received" {{ $item->status == 'received' ? 'selected' : '' }}>Received</option>
                                            </select>
                                            @error("products.$index.status")
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="remove-product-btn">
                                            <button type="button" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <button type="button" id="add-product-btn" class="btn btn-secondary mt-2"><i class="fa fa-plus"></i> Add Product</button>

                        <div class="row mt-4 g-4">
                            <div class="col-lg-7"></div>
                            <div class="col-lg-5">
                                <div class="totals-card">
                                    <div class="row mb-2"><div class="col-7">Subtotal</div><div class="col-5 text-end" id="subtotal">₹0.00</div></div>
                                    <div class="row mb-2"><div class="col-7">Total Discount</div><div class="col-5 text-end" id="total_discount">₹0.00</div></div>
                                    <div class="row mb-2"><div class="col-7">Total CGST</div><div class="col-5 text-end" id="total_cgst">₹0.00</div></div>
                                    <div class="row mb-2"><div class="col-7">Total SGST</div><div class="col-5 text-end" id="total_sgst">₹0.00</div></div>
                                    <div class="row mb-3"><div class="col-7">Total IGST</div><div class="col-5 text-end" id="total_igst">₹0.00</div></div>
                                    <hr><div class="row grand-total"><div class="col-7">Grand Total</div><div class="col-5 text-end" id="grand_total">₹0.00</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary btn-lg">Update Purchase Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let rowCount = {{ count($purchaseEntry->items) }};

        function addProductRow() {
            const rowHtml = `
                <div class="product-item-row" id="row-${rowCount}">
                    <div class="suggestions-container">
                        <label class="form-label d-lg-none">Product</label>
                        <input type="text" name="products[${rowCount}][product_name]" class="form-control product-name" placeholder="Search Product" required autocomplete="off">
                        <input type="hidden" name="products[${rowCount}][product_id]" class="product-id">
                        <div class="product-suggestions"></div>
                    </div>
                    <div><label class="form-label d-lg-none">Qty</label><input type="number" name="products[${rowCount}][quantity]" placeholder="Qty" class="form-control quantity-input" min="1" required></div>
                    <div><label class="form-label d-lg-none">Price</label><input type="number" name="products[${rowCount}][unit_price]" placeholder="Price" class="form-control unit-price" step="0.01" min="0" required></div>
                    <div><label class="form-label d-lg-none">Discount (%)</label><input type="number" name="products[${rowCount}][discount]" placeholder="Disc %" class="form-control discount-input" step="0.01" min="0" max="100" value="0"></div>
                    <div><label class="form-label d-lg-none">CGST (%)</label><input type="number" name="products[${rowCount}][cgst_rate]" placeholder="CGST %" class="form-control cgst-rate" step="0.01" min="0" value="0"></div>
                    <div><label class="form-label d-lg-none">SGST (%)</label><input type="number" name="products[${rowCount}][sgst_rate]" placeholder="SGST %" class="form-control sgst-rate" step="0.01" min="0" value="0"></div>
                    <div><label class="form-label d-lg-none">IGST (%)</label><input type="number" name="products[${rowCount}][igst_rate]" placeholder="IGST %" class="form-control igst-rate" step="0.01" min="0" value="0"></div>
                    <div><label class="form-label d-lg-none">Status</label><select name="products[${rowCount}][status]" class="form-select" required><option value="received" selected>Received</option><option value="pending">Pending</option></select></div>
                    <div class="remove-product-btn"><button type="button" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></button></div>
                </div>`;
            $('#products-list').append(rowHtml);
            $('.products-header').css('display', 'grid');
            rowCount++;
        }

        function calculateTotals() {
            let grandSubtotal = 0;
            let grandTotalDiscount = 0;
            let grandTotalCgst = 0;
            let grandTotalSgst = 0;
            let grandTotalIgst = 0;

            $('.product-item-row').each(function() {
                const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
                const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
                const discountPercent = parseFloat($(this).find('.discount-input').val()) || 0;
                const cgstRate = parseFloat($(this).find('.cgst-rate').val()) || 0;
                const sgstRate = parseFloat($(this).find('.sgst-rate').val()) || 0;
                const igstRate = parseFloat($(this).find('.igst-rate').val()) || 0;

                if (quantity > 0 && unitPrice >= 0) {
                    const basePrice = quantity * unitPrice;
                    const discountAmount = basePrice * (discountPercent / 100);
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

        $(document).ready(function() {
            $('.products-header').css('display', 'grid');
            calculateTotals();

            $('#add-product-btn').on('click', addProductRow);

            $('#products-list').on('click', '.remove-product-btn button', function() {
                $(this).closest('.product-item-row').remove();
                if ($('#products-list .product-item-row').length === 0) {
                    $('.products-header').hide();
                }
                calculateTotals();
            });

            $(document).on('input', '.quantity-input, .unit-price, .discount-input, .cgst-rate, .sgst-rate, .igst-rate', calculateTotals);

            $('#party_name').on('input', function() {
                let query = $(this).val();
                if (query.length > 1) {
                    $.ajax({
                        url: "{{ route('parties.search') }}",
                        data: { query: query },
                        success: function(data) {
                            let suggestions = '<ul>';
                            data.forEach(party => {
                                suggestions += `<li onclick="selectParty(${party.id}, '${party.name}')">${party.name}</li>`;
                            });
                            suggestions += '</ul>';
                            $('#party_suggestions').html(suggestions).show();
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error: ', error);
                            $('#party_suggestions').html('<p>Error loading suggestions.</p>').show();
                        }
                    });
                } else {
                    $('#party_suggestions').hide();
                }
            });

            $(document).on('input', '.product-name', function() {
                let $input = $(this);
                let query = $input.val();
                let $suggestionsDiv = $input.closest('.suggestions-container').find('.product-suggestions');
                if (query.length > 1) {
                    $.ajax({
                        url: "{{ route('products.search') }}",
                        data: { query: query },
                        success: function(data) {
                            let suggestions = '<ul>';
                            data.forEach(product => {
                                suggestions += `<li onclick="selectProduct(${product.id}, '${product.name}')">${product.name}</li>`;
                            });
                            suggestions += '</ul>';
                            $suggestionsDiv.html(suggestions).show();
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error: ', error);
                            $suggestionsDiv.html('<p>Error loading suggestions.</p>').show();
                        }
                    });
                } else {
                    $suggestionsDiv.hide();
                }
            });
        });

        function selectParty(id, name) {
            $('#party_name').val(name);
            $('#party_id').val(id);
            $('#party_suggestions').hide();
        }

        function selectProduct(id, name) {
            let $row = $(event.target).closest('.product-item-row');
            $row.find('.product-name').val(name);
            $row.find('.product-id').val(id);
            $row.find('.product-suggestions').hide();
        }
    </script>
</body>

@include('layout.footer')