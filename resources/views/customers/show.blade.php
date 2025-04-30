@include('layout.header')
<div class="container p-3 mx-auto">
<div class="card shadow-sm w-100">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Customer Details</h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- <p><strong>ID:</strong> {{ $customer->id }}</p> -->
                        <p><strong>Name:</strong> {{ $customer->name }}</p>
                        <p><strong>Email:</strong> {{ $customer->email }}</p>
                        <p><strong>Phone:</strong> {{ $customer->phone }}</p>
                        <p><strong>Address:</strong> {{ $customer->address }}</p>
                    </div>
                </div>
                @if ($customer->sales->count())
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
            <h2 class="mb-0">Sales History</h2>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Sale ID</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Sale Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customer->sales as $sale)
                        <tr>
                            <td>{{ $sale->id }}</td>
                            <td>{{ $sale->product->name ?? 'N/A' }}</td>
                            <td>{{ $sale->quantity }}</td>
                            <td>{{ $sale->product->price ?? 'N/A' }}</td>
                            <td>{{ $sale->quantity * ($sale->product->price ?? 0) }}</td>
                            <td>{{ $sale->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="alert alert-info mt-4">
        No sales found for this customer.
    </div>
@endif
                <div class="mt-4">
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Back to Customers</a>
                </div>
            </div>
        </div>
    </div>

    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        p {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        strong {
            color: #343a40;
        }
        .btn-outline-secondary {
            transition: background-color 0.3s, color 0.3s;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }
        @media (max-width: 767px) {
            .card {
                margin: 0 10px;
            }
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
    @include('layout.footer')