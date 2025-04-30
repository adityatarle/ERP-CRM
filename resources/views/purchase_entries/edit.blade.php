@include('layout.header')

<div class="container">
    <h1>Edit Purchase Entry #{{ $purchaseEntry->purchase_number }}</h1>
    <form action="{{ route('purchase_entries.update', $purchaseEntry->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Purchase Date</label>
            <input type="date" name="purchase_date" value="{{ old('purchase_date', $purchaseEntry->purchase_date) }}" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Invoice Number</label>
            <input type="text" name="invoice_number" value="{{ old('invoice_number', $purchaseEntry->invoice_number) }}" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Invoice Date</label>
            <input type="date" name="invoice_date" value="{{ old('invoice_date', $purchaseEntry->invoice_date) }}" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Party</label>
            <input type="text" id="party_name" name="party_name" value="{{ old('party_name', $purchaseEntry->party->name) }}" class="form-control" placeholder="Search Party" required>
            <input type="hidden" name="party_id" id="party_id" value="{{ old('party_id', $purchaseEntry->party_id) }}">
            <div id="party_suggestions"></div>
        </div>
        <div class="form-group">
            <label>Note</label>
            <textarea name="note" class="form-control" placeholder="Add note (e.g., 'Materials received on 2025-04-29')">{{ old('note', $purchaseEntry->note) }}</textarea>
            <small class="text-muted">Add 'received' in the note to update stock.</small>
        </div>
        <div id="products">
            @foreach($purchaseEntry->items as $index => $item)
                <div class="product-row">
                    <select name="products[{{ $index }}][product_id]" class="form-control" required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="products[{{ $index }}][quantity]" value="{{ $item->quantity }}" placeholder="Quantity" class="form-control" required>
                    <input type="number" name="products[{{ $index }}][unit_price]" value="{{ $item->unit_price }}" placeholder="Unit Price" class="form-control" step="0.01" required>
                    <input type="number" name="products[{{ $index }}][gst_rate]" value="{{ $item->gst_rate }}" placeholder="GST Rate (%)" class="form-control" step="0.01" min="0" max="100">
                    <select name="products[{{ $index }}][gst_type]" class="form-control">
                        <option value="">Select GST Type</option>
                        <option value="CGST" {{ $item->gst_type == 'CGST' ? 'selected' : '' }}>CGST</option>
                        <option value="SGST" {{ $item->gst_type == 'SGST' ? 'selected' : '' }}>SGST</option>
                        <option value="IGST" {{ $item->gst_type == 'IGST' ? 'selected' : '' }}>IGST</option>
                    </select>
                </div>
            @endforeach
        </div>
        <button type="button" onclick="addProductRow()">Add Product</button>
        <button type="submit">Update Purchase Entry</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let rowCount = {{ $purchaseEntry->items->count() }};
    function addProductRow() {
        let row = `<div class="product-row">
            <select name="products[${rowCount}][product_id]" class="form-control" required>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
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
    });

    function selectParty(id, name) {
        $('#party_name').val(name);
        $('#party_id').val(id);
        $('#party_suggestions').html('');
    }
</script>

@include('layout.footer')