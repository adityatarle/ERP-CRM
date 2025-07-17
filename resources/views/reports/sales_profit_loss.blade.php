@include('layout.header')
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0">Sales & Profit Analysis</h4>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="bg-white rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-line fa-3x text-primary"></i>
                    <div class="ms-3 text-end">
                        <p class="mb-2">Total Revenue</p>
                        <h6 class="mb-0">₹{{ number_format($grandTotalRevenue, 2) }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="bg-white rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-area fa-3x text-warning"></i>
                    <div class="ms-3 text-end">
                        <p class="mb-2">Total Cost (COGS)</p>
                        <h6 class="mb-0">₹{{ number_format($grandTotalCogs, 2) }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="bg-white rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-pie fa-3x {{ $grandTotalProfit >= 0 ? 'text-success' : 'text-danger' }}"></i>
                    <div class="ms-3 text-end">
                        <p class="mb-2">Overall Profit / Loss</p>
                        <h6 class="mb-0">₹{{ number_format($grandTotalProfit, 2) }}</h6>
                    </div>
                </div>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Product Name</th>
                        <th scope="col">SKU</th>
                        <th scope="col">Total Qty Sold</th>
                        <th scope="col">Total Revenue</th>
                        <th scope="col">Total Cost (COGS)</th>
                        <th scope="col">Profit / Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productStats as $stat)
                        <tr>
                            <td>{{ $stat->name }}</td>
                            <td>{{ $stat->sku ?? 'N/A' }}</td>
                            <td>{{ $stat->quantity_sold }}</td>
                            <td>₹{{ number_format($stat->total_revenue, 2) }}</td>
                            <td>₹{{ number_format($stat->total_cogs, 2) }}</td>
                            <td class="{{ $stat->profit_loss >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                ₹{{ number_format($stat->profit_loss, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No sales data available to generate a report.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-secondary text-white">
                         <td colspan="3" class="text-end">Grand Totals:</td>
                         <td>₹{{ number_format($grandTotalRevenue, 2) }}</td>
                         <td>₹{{ number_format($grandTotalCogs, 2) }}</td>
                         <td class="{{ $grandTotalProfit >= 0 ? 'text-success-emphasis' : 'text-danger-emphasis' }}">
                            ₹{{ number_format($grandTotalProfit, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@include('layout.footer')