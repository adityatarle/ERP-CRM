@include('layout.header')

<div class="container p-3 mx-auto">
    <div class="card shadow-sm w-100">
        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
            <h1 class="mb-0">Create Invoice</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('invoices.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="customer_id">Customer</label>
                    <select name="customer_id" id="customer_id" class="form-control mb-5" style="font-size: 13px;">
                        <option value="">Select a Customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="sale_ids">Sales</label>
                    <select name="sale_ids[]" id="sale_ids" class="form-control mb-5" style="font-size: 13px;" multiple>
                        <option value="">Select Sales</option>
                        @foreach ($sales as $sale)
                            <option value="{{ $sale->id }}" 
                                    data-customer-id="{{ $sale->customer_id }}"
                                    class="sale-option">
                                Sale #{{ $sale->id }} - {{ $sale->customer->name }} - 
                                @if ($sale->saleItems->isNotEmpty())
                                    @foreach ($sale->saleItems as $saleItem)
                                        {{ $saleItem->product->name }} (Qty: {{ $saleItem->quantity }}, Total: ${{ number_format($saleItem->total_price, 2) }})@if (!$loop->last), @endif
                                    @endforeach
                                @else
                                    No products
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="gst">GST (%)</label>
                    <input type="number" name="gst" id="gst" class="form-control mb-5" step="0.01" min="0" value="" required>
                </div>
                <div class="form-group">
                    <label for="issue_date">Issue Date</label>
                    <input type="date" name="issue_date" class="form-control mb-5" required>
                </div>
                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" name="due_date" class="form-control mb-5" required>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const customerSelect = document.getElementById('customer_id');
        const saleSelect = document.getElementById('sale_ids');
        const allSaleOptions = Array.from(document.querySelectorAll('.sale-option'));

        customerSelect.addEventListener('change', function () {
            const selectedCustomerId = this.value;
            
            // Reset sale selection
            saleSelect.value = '';

            // Filter sales based on customer
            allSaleOptions.forEach(option => {
                if (selectedCustomerId === '' || option.getAttribute('data-customer-id') === selectedCustomerId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        });
    });
</script>

@include('layout.footer')