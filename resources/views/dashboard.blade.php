@include('layout.header')

<!-- Sale & Revenue Start -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-sm-6 col-xl-3">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                <i class="fa fa-chart-line fa-3x text-primary"></i>
                <div class="ms-3">
                    <p class="mb-2">Total Sales</p>
                    <h6 class="mb-0">${{ number_format($totalSales, 2) }}</h6>
                </div>
            </div>
        </div>

        <!-- <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-bar fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Invoices</p>
                                <a href="{{ route('invoices.index') }}"><h6 class="mb-0">Manage Invoices</h6></a>
                            </div>
                        </div>
                    </div> -->
        <!-- <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-area fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Today Revenue</p>
                                <h6 class="mb-0">$1234</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-pie fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Total Revenue</p>
                                <h6 class="mb-0">$1234</h6>
                            </div>
                        </div>
                    </div> -->
    </div>
</div>
<!-- Sale & Revenue End -->

<!-- Main Content with Tables -->
<div class="main-content">
    <!-- Low Stock Products -->
    <div class="pt-5">
        <div class="dashboard-card">
            <div class="card-body">
                <h5>Low Stock Products</h5>
                <div class="table-responsive" style="max-height: 300px;">
                    <table class="table table-striped low-stock">
                        <thead class="bg-white z-0">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowStockProducts as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td style="color: {{ $product->stock < 3 ? 'var(--bs-danger)' : 'var(--bs-warning)' }};">
                                    {{ $product->stock }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No low stock products</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sales Start -->
    <div class="container-fluid pt-4">
        <div class="bg-light text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="mb-0">Recent Sales</h6>
                <a href="">Show All</a>
            </div>
            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0">
                    <thead>
                        <tr class="text-dark">
                            <th scope="col"><input class="form-check-input" type="checkbox"></th>
                            <th scope="col">Customer</th>
                            <th scope="col">Product</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentSales as $sale)
                            <tr>
                                <td><input class="form-check-input" type="checkbox"></td>
                                <td>{{ $sale->customer->name }}</td>
                                <td>
                                    @if ($sale->saleItems->isNotEmpty())
                                        {{ $sale->saleItems->first()->product->name }}
                                    @else
                                        No product
                                    @endif
                                </td>
                                <td>
                                    @if ($sale->saleItems->isNotEmpty())
                                        {{ $sale->saleItems->first()->quantity }}
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>${{ number_format($sale->total_price, 2) }}</td>
                                <td><a class="btn btn-sm btn-primary" href="">Detail</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="no-data">No recent sales</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Recent Sales End -->
</div>

@include('layout.footer')