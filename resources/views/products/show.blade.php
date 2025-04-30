@include('layout.header')
<div class="container p-3 mx-auto">
<div class="card shadow-sm w-100">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Product Details</h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-5"><strong>ID:</strong> {{ $product->id }}</p>
                        <p class="mb-5"><strong>Name:</strong> {{ $product->name }}</p>
                        <p class="mb-5"><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-5"><strong>Stock:</strong> {{ $product->stock }}</p>
                        <p class="mb-5"><strong>Description:</strong> {{ $product->description }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back to Products</a>
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