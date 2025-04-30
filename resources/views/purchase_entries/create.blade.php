@include('layout.header')

<div class="container">
    <h1>Create Purchase Entry</h1>
    <form action="{{ route('purchase_entries.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Purchase Date</label>
            <input type="date" name="purchase_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Invoice Number</label>
            <input type="text" name="invoice_number" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Invoice Date</label>
            <input type="date" name="invoice_date" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Party</label>
            <input type="text" id="party_name" name="party_name" class="form-control" placeholder="Search Party" required>
            <input type="hidden" name="party_id" id="party_id">
            <div id="party_suggestions"></div>
        </div>
        <div class="form-group">
            <label>Note</label>
            <textarea name="note" class="form-control" placeholder="Add note (e.g., 'Materials received on 2025-04-29')"></textarea>
            <small class="text-muted">Add 'received' in the note to update stock.</small>
        </div>
        <div id="products">
            <div class="product-row">
                <div class="form-group">
                    <input type="text" name="products[0][product_name]" class="form-control product-name" placeholder="Search Product" required>
                    <input type="hidden" name="products[0][product_id]" class="product-id">
                    <div class="product-suggestions"></div>
                </div>
                <input type="number" name="products[0][quantity]" placeholder="Quantity" class="form-control" required>
                <input type="number" name="products[0][unit_price]" placeholder="Unit Price" class="form-control" step="0.01" required>
                <input type="number" name="products[0][gst_rate]" placeholder="GST Rate (%)" class="form-control" step="0.01" min="0" max="100">
                <select name="products[0][gst_type]" class="form-control">
                    <option value="">Select GST Type</option>
                    <option value="CGST">CGST</option>
                    <option value="SGST">SGST</option>
                    <option value="IGST">IGST</option>
                </select>
            </div>
        </div>
        <button type="button" onclick="addProductRow()">Add Product</button>
        <button type="submit">Create Purchase Entry</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let rowCount = 1;

    function addProductRow() {
        let row = `<div class="product-row">
            <div class="form-group">
                <input type="text" name="products[${rowCount}][product_name]" class="form-control product-name" placeholder="Search Product" required>
                <input type="hidden" name="products[${rowCount}][product_id]" class="product-id">
                <div class="product-suggestions"></div>
            </div>
            <input type="number" name="products[${rowCount}][quantity]" placeholder="Quantity" class="form-control" required>
            <input type="number" name="products[${rowCount}][unit_price]" placeholder="Unit Price" class="form-control" step="0.01" required>
            <input type="number" name="products[${rowCount}][gst_rate]" placeholder="GST Rate (%)" class="form-control" step="0.01" min="0" max="100">
            <select name="products[${rowCount}][gst_type]" class="form-control">
                <option value="">Select GST Type</option>
                <option value="CGST">CGST</option>
                <option value="SGST">SGST</option>
                <option value="IGST">IGST</option>
            </select>
        </div>`;
        document.getElementById('products').insertAdjacentHTML('beforeend', row);
        rowCount++;
    }

    $(document).ready(function() {
        // Party search
        $('#party_name').on('input', function() {
            let query = $(this).val();
            if (query.length > 2) {
                $.ajax({
                    url: "{{ route('parties.search') }}",
                    data: { query: query },
                    success: function(data) {
                        let suggestions = '<ul>';
                        data.forEach(party => {
                            suggestions += `<li onclick="selectParty(${party.id}, '${party.name}')">${party.name}</li>`;
                        });
                        suggestions += '</ul>';
                        $('#party_suggestions').html(suggestions);
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error: ' + error);
                        $('#party_suggestions').html('<p>Error loading suggestions.</p>');
                    }
                });
            } else {
                $('#party_suggestions').html('');
            }
        });

        // Product search (reusing the same logic as party search)
        $(document).on('input', '.product-name', function() {
            let $input = $(this);
            let query = $input.val();
            let $suggestionsDiv = $input.closest('.product-row').find('.product-suggestions');

            if (query.length > 2) {
                $.ajax({
                    url: "{{ route('products.search') }}",
                    data: { query: query },
                    success: function(data) {
                        let suggestions = '<ul>';
                        data.forEach(product => {
                            suggestions += `<li onclick="selectProduct(this, ${product.id}, '${product.name}')">${product.name}</li>`;
                        });
                        suggestions += '</ul>';
                        $suggestionsDiv.html(suggestions);
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error: ' + error);
                        $suggestionsDiv.html('<p>Error loading suggestions.</p>');
                    }
                });
            } else {
                $suggestionsDiv.html('');
            }
        });
    });

    function selectParty(id, name) {
        $('#party_name').val(name);
        $('#party_id').val(id);
        $('#party_suggestions').html('');
    }

    function selectProduct(element, id, name) {
        let $row = $(element).closest('.product-row');
        $row.find('.product-name').val(name);
        $row.find('.product-id').val(id);
        $row.find('.product-suggestions').html('');
    }
</script>

@include('layout.footer')