@include('layout.header')

<div class="container p-3 mx-auto">
    <div class="card shadow-sm w-100">
        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
            <h1 class="mb-0 text-white">Edit Invoice</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('invoices.update', $invoice->id) }}" method="POST">
                @csrf
                @method('PUT') <!-- Required for update -->

                <div class="form-group">
                    <label for="customer_id">Customer</label>
                    <select name="customer_id" class="form-control mb-4" style="font-size: 13px;">
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}"
                                {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="sale_id">Sale</label>
                    <select name="sale_id" id="sale_id" class="form-control mb-4" style="font-size: 13px;">
                        <option value="">Select a Sale</option>
                        @foreach ($sales as $sale)
                            <option value="{{ $sale->id }}"
                                data-customer="{{ $sale->customer->name }}"
                                data-product="{{ $sale->product->name }}"
                                data-quantity="{{ $sale->quantity }}"
                                data-total="{{ $sale->quantity * $sale->product->price }}"
                                {{ old('sale_id', $invoice->sale_id) == $sale->id ? 'selected' : '' }}>
                                Sale #{{ $sale->id }} - {{ $sale->customer->name }} - {{ $sale->product->name }} (Qty:
                                {{ $sale->quantity }}, Total: {{ $sale->quantity * $sale->product->price }})
                            </option>
                        @endforeach
                    </select>
                </div>

                

                <div class="form-group">
                    <label for="issue_date">Issue Date</label>
                    <input type="date" name="issue_date" class="form-control mb-4"
                        value="{{ old('issue_date', $invoice->issue_date) }}" required>
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" name="due_date" class="form-control mb-4"
                        value="{{ old('due_date', $invoice->due_date) }}" required>
                </div>

                <button type="submit" class="btn btn-primary">Update Invoice</button>
            </form>
        </div>
    </div>
</div>

<script>
    const saleSelect = document.getElementById('sale_id');
    const saleDetails = document.getElementById('sale-details');
    const customerName = document.getElementById('customer-name');
    const productName = document.getElementById('product-name');
    const quantity = document.getElementById('quantity');
    const totalPrice = document.getElementById('total-price');

    function updateSaleDetails() {
        const selectedOption = saleSelect.options[saleSelect.selectedIndex];

        if (selectedOption.value) {
            customerName.textContent = selectedOption.getAttribute('data-customer');
            productName.textContent = selectedOption.getAttribute('data-product');
            quantity.textContent = selectedOption.getAttribute('data-quantity');
            totalPrice.textContent = selectedOption.getAttribute('data-total');
            saleDetails.style.display = 'block';
        } else {
            saleDetails.style.display = 'none';
        }
    }

    saleSelect.addEventListener('change', updateSaleDetails);
    window.addEventListener('DOMContentLoaded', updateSaleDetails); // show details if editing existing invoice
</script>

@include('layout.footer')
