@include('layout.header')
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0">Category-Wise Business Report</h4>
            <div>
                <a href="{{ route('reports.category_wise_export') }}" class="btn btn-success">
                    <i class="fa fa-download me-2"></i>Export to Excel
                </a>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-line fa-3x text-primary"></i>
                    <div class="ms-3 text-end">
                        <p class="mb-2">Total Revenue</p>
                        <h6 class="mb-0">₹{{ number_format($grandTotalRevenue, 2) }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-area fa-3x text-warning"></i>
                    <div class="ms-3 text-end">
                        <p class="mb-2">Total Cost (COGS)</p>
                        <h6 class="mb-0">₹{{ number_format($grandTotalCogs, 2) }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-pie fa-3x {{ $grandTotalProfit >= 0 ? 'text-success' : 'text-danger' }}"></i>
                    <div class="ms-3 text-end">
                        <p class="mb-2">Overall Profit / Loss</p>
                        <h6 class="mb-0">₹{{ number_format($grandTotalProfit, 2) }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-percentage fa-3x text-info"></i>
                    <div class="ms-3 text-end">
                        <p class="mb-2">Profit Margin</p>
                        <h6 class="mb-0">{{ number_format($grandTotalProfitMargin, 2) }}%</h6>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Summary Cards --}}
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-boxes fa-3x text-secondary"></i>
                    <div class="ms-3 text-end">
                        <p class="mb-2">Total Quantity Sold</p>
                        <h6 class="mb-0">{{ number_format($grandTotalQuantity) }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-tags fa-3x text-purple"></i>
                    <div class="ms-3 text-end">
                        <p class="mb-2">Total Categories</p>
                        <h6 class="mb-0">{{ count($categoryStats) }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-shopping-cart fa-3x text-orange"></i>
                    <div class="ms-3 text-end">
                        <p class="mb-2">Total Products</p>
                        <h6 class="mb-0">{{ $categoryStats ? $categoryStats->sum('product_count') : 0 }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-white rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-bar fa-3x text-teal"></i>
                    <div class="ms-3 text-end">
                        <p class="mb-2">Avg. Profit Margin</p>
                        <h6 class="mb-0">{{ $categoryStats && $categoryStats->count() > 0 ? number_format($categoryStats->avg('profit_margin'), 2) : 0 }}%</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                                        <thead>
                            <tr class="text-dark">
                                <th scope="col">Category</th>
                                <th scope="col">Subcategories</th>
                                <th scope="col">Product Count</th>
                                <th scope="col">Total Qty Sold</th>
                                <th scope="col">Total Revenue</th>
                                <th scope="col">Total Cost (COGS)</th>
                                <th scope="col">Profit / Loss</th>
                                <th scope="col">Profit Margin</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                <tbody>
                    @forelse ($categoryStats as $stat)
                        <tr>
                            <td class="fw-bold">{{ $stat['category'] }}</td>
                            <td>
                                @if($stat['subcategories']->count() > 0)
                                    @foreach($stat['subcategories'] as $subcategory)
                                        <span class="badge bg-light text-dark me-1">{{ $subcategory }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No subcategories</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $stat['product_count'] }}</td>
                            <td class="text-center">{{ number_format($stat['total_quantity_sold']) }}</td>
                            <td class="text-end">₹{{ number_format($stat['total_revenue'], 2) }}</td>
                            <td class="text-end">₹{{ number_format($stat['total_cogs'], 2) }}</td>
                            <td class="text-end fw-bold {{ $stat['profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">
                                ₹{{ number_format($stat['profit_loss'], 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $stat['profit_margin'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ number_format($stat['profit_margin'], 2) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary" onclick="viewCategoryDetails('{{ $stat['category'] }}')">
                                    <i class="fa fa-eye me-1"></i>View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No category data available to generate a report.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-secondary text-white">
                        <td colspan="3" class="text-end">Grand Totals:</td>
                        <td class="text-center">{{ number_format($grandTotalQuantity) }}</td>
                        <td class="text-end">₹{{ number_format($grandTotalRevenue, 2) }}</td>
                        <td class="text-end">₹{{ number_format($grandTotalCogs, 2) }}</td>
                        <td class="text-end {{ $grandTotalProfit >= 0 ? 'text-success-emphasis' : 'text-danger-emphasis' }}">
                            ₹{{ number_format($grandTotalProfit, 2) }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $grandTotalProfitMargin >= 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ number_format($grandTotalProfitMargin, 2) }}%
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Performance Insights --}}
        @if($categoryStats && count($categoryStats) > 0)
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fa fa-trophy me-2 text-warning"></i>Top Performing Categories</h6>
                    </div>
                    <div class="card-body">
                        @foreach($categoryStats->take(3) as $index => $stat)
                            @if($stat['profit_loss'] > 0)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">{{ $stat['category'] }}</span>
                                <span class="text-success">₹{{ number_format($stat['profit_loss'], 2) }}</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fa fa-chart-line me-2 text-info"></i>Revenue Leaders</h6>
                    </div>
                    <div class="card-body">
                        @foreach($categoryStats->sortByDesc('total_revenue')->take(3) as $stat)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">{{ $stat['category'] }}</span>
                                <span class="text-primary">₹{{ number_format($stat['total_revenue'], 2) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fa fa-info-circle me-2"></i>
                    <strong>No Category Data Available</strong><br>
                    <small class="text-muted">This report requires products with assigned categories and sales data.</small>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Category Details Modal -->
<div class="modal fade" id="categoryDetailsModal" tabindex="-1" aria-labelledby="categoryDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryDetailsModalLabel">Category Details: <span id="modalCategoryName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="categoryDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="exportCategoryDetails()">
                    <i class="fa fa-download me-1"></i>Export Details
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    font-size: 0.8em;
}
.text-purple {
    color: #6f42c1 !important;
}
.text-orange {
    color: #fd7e14 !important;
}
.text-teal {
    color: #20c997 !important;
}
</style>

<script>
function viewCategoryDetails(categoryName) {
    // Show modal
    document.getElementById('modalCategoryName').textContent = categoryName;
    document.getElementById('categoryDetailsContent').innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>';
    
    const modal = new bootstrap.Modal(document.getElementById('categoryDetailsModal'));
    modal.show();
    
    // Load category details via AJAX
    fetch(`/reports/category-details/${encodeURIComponent(categoryName)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCategoryDetails(data.data);
            } else {
                document.getElementById('categoryDetailsContent').innerHTML = 
                    '<div class="alert alert-danger">Error loading category details.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('categoryDetailsContent').innerHTML = 
                '<div class="alert alert-danger">Error loading category details.</div>';
        });
}

function displayCategoryDetails(data) {
    const content = document.getElementById('categoryDetailsContent');
    
    let html = `
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h4>₹${data.totalRevenue.toLocaleString()}</h4>
                        <small>Total Revenue</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h4>₹${data.totalCogs.toLocaleString()}</h4>
                        <small>Total COGS</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4>₹${data.totalProfit.toLocaleString()}</h4>
                        <small>Total Profit</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h4>${data.profitMargin.toFixed(2)}%</h4>
                        <small>Profit Margin</small>
                    </div>
                </div>
            </div>
        </div>
        
        <h6 class="mb-3">Products in this Category</h6>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Item Code</th>
                        <th>Subcategory</th>
                        <th>Quantity Sold</th>
                        <th>Revenue</th>
                        <th>COGS</th>
                        <th>Profit</th>
                        <th>Profit Margin</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    data.products.forEach(product => {
        const profitMargin = product.revenue > 0 ? ((product.revenue - product.cogs) / product.revenue * 100) : 0;
        html += `
            <tr>
                <td>${product.name}</td>
                <td>${product.item_code || 'N/A'}</td>
                <td>${product.subcategory || 'N/A'}</td>
                <td class="text-center">${product.quantity_sold}</td>
                <td class="text-end">₹${product.revenue.toLocaleString()}</td>
                <td class="text-end">₹${product.cogs.toLocaleString()}</td>
                <td class="text-end ${(product.revenue - product.cogs) >= 0 ? 'text-success' : 'text-danger'}">
                    ₹${(product.revenue - product.cogs).toLocaleString()}
                </td>
                <td class="text-center">
                    <span class="badge ${profitMargin >= 0 ? 'bg-success' : 'bg-danger'}">
                        ${profitMargin.toFixed(2)}%
                    </span>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    content.innerHTML = html;
}

function exportCategoryDetails() {
    const categoryName = document.getElementById('modalCategoryName').textContent;
    window.open(`/reports/category-details/${encodeURIComponent(categoryName)}/export`, '_blank');
}
</script>

@include('layout.footer')