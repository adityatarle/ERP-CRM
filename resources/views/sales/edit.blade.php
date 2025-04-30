@include('layout.header')
<div class="container p-3 mx-auto">
<div class="card shadow-sm w-100">
<div class="card-header bg-primary d-flex justify-content-between align-items-center">
<h1 class="mb-0">Edit Sale</h1>
</div>
<div class="card-body">
    <form action="{{ route('sales.update', $sale) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3 mt-5">
            <label for="customer_id" class="form-label">Customer</label>
            <select name="customer_id" id="customer_id" class="form-control mb-5" required style="font-size: 15px;">
                <option value="">Select Customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id', $sale->customer_id) == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
            @error('customer_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="product_id" class="form-label">Product</label>
            <select name="product_id" id="product_id" class="form-control mb-5" required style="font-size: 15px;">
                <option value="">Select Product</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" {{ old('product_id', $sale->product_id) == $product->id ? 'selected' : '' }}>
                        {{ $product->name }} (Stock: {{ $product->stock }})
                    </option>
                @endforeach
            </select>
            @error('product_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control mb-5" value="{{ old('quantity', $sale->quantity) }}" min="1" required style="font-size: 15px;">
            @error('quantity')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
    </div>
    </div>
    </div>
    @include('layout.footer')