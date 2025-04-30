
@include('layout.header')


    <div class="container">
        <h1>Create Purchase Order</h1>
        <form action="{{ route('purchase_orders.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Party</label>
                <select name="party_id" class="form-control" required>
                    @foreach($parties as $party)
                        <option value="{{ $party->id }}">{{ $party->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Order Date</label>
                <input type="date" name="order_date" class="form-control" required>
            </div>
            <div id="products">
                <div class="product-row">
                    <select name="products[0][product_id]" class="form-control" required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="products[0][quantity]" placeholder="Quantity" class="form-control" required>
                    <input type="number" name="products[0][unit_price]" placeholder="Unit Price" class="form-control" step="0.01" required>
                </div>
            </div>
            <button type="button" onclick="addProductRow()">Add Product</button>
            <button type="submit">Create Purchase Order</button>
        </form>
    </div>
    <script>
        let rowCount = 1;
        function addProductRow() {
            let row = `<div class="product-row">
                <select name="products[${rowCount}][product_id]" class="form-control" required>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="products[${rowCount}][quantity]" placeholder="Quantity" class="form-control" required>
                <input type="number" name="products[${rowCount}][unit_price]" placeholder="Unit Price" class="form-control" step="0.01" required>
            </div>`;
            document.getElementById('products').insertAdjacentHTML('beforeend', row);
            rowCount++;
        }
    </script>


    
@include('layout.footer')