@include('layout.header')

<style>
    .report-card {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    .metric-card {
        background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .metric-card.positive {
        background: linear-gradient(45deg, #11998e 0%, #38ef7d 100%);
    }
    .metric-card.negative {
        background: linear-gradient(45deg, #ff416c 0%, #ff4b2b 100%);
    }
    .metric-card.neutral {
        background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    }
    .section-header {
        background: #f8f9fc;
        border-left: 4px solid #4e73df;
        padding: 15px;
        margin: 20px 0 15px 0;
        border-radius: 0 5px 5px 0;
    }
    .filter-section {
        background: #f8f9fc;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
    }
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    .export-buttons {
        margin-bottom: 20px;
    }
</style>

<div class="container-fluid pt-4 px-4">
    <div class="row">
        <div class="col-12">
            <div class="bg-light text-center rounded p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="mb-0">Comprehensive Profit & Loss Report</h4>
                    <div class="export-buttons">
                        <a href="{{ route('reports.profit_loss.export', request()->query()) }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </a>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="filter-section">
                    <form method="GET" action="{{ route('reports.profit_loss') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ $filters['start_date'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ $filters['end_date'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-control" id="customer_id" name="customer_id">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            {{ ($filters['customer_id'] ?? '') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" 
                                            {{ ($filters['category'] ?? '') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Generate Report
                            </button>
                            <a href="{{ route('reports.profit_loss') }}" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i> Clear Filters
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card neutral">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-1">Total Revenue</h6>
                                    <h4 class="mb-0">₹{{ number_format($report['summary']['total_revenue'], 2) }}</h4>
                                    <small>{{ $report['summary']['total_invoices'] }} invoices</small>
                                </div>
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card neutral">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-1">Total COGS</h6>
                                    <h4 class="mb-0">₹{{ number_format($report['summary']['total_cogs'], 2) }}</h4>
                                    <small>{{ $report['summary']['total_purchases'] }} purchases</small>
                                </div>
                                <i class="fas fa-shopping-cart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card {{ $report['summary']['gross_profit'] >= 0 ? 'positive' : 'negative' }}">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-1">Gross Profit</h6>
                                    <h4 class="mb-0">₹{{ number_format($report['summary']['gross_profit'], 2) }}</h4>
                                    <small>{{ number_format($report['summary']['gross_profit_margin'], 2) }}% margin</small>
                                </div>
                                <i class="fas fa-chart-area fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="metric-card {{ $report['summary']['net_profit'] >= 0 ? 'positive' : 'negative' }}">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-1">Net Profit</h6>
                                    <h4 class="mb-0">₹{{ number_format($report['summary']['net_profit'], 2) }}</h4>
                                    <small>{{ number_format($report['summary']['net_profit_margin'], 2) }}% margin</small>
                                </div>
                                <i class="fas fa-chart-pie fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Section -->
                <div class="section-header">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Revenue Analysis</h5>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="report-card p-3">
                            <h6>Revenue by Customer</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Revenue</th>
                                            <th>Invoices</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['revenue']['revenue_by_customer']->take(5) as $customer)
                                        <tr>
                                            <td>{{ $customer['customer_name'] }}</td>
                                            <td>₹{{ number_format($customer['total_revenue'], 2) }}</td>
                                            <td>{{ $customer['invoice_count'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="report-card p-3">
                            <h6>Revenue by Category</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Revenue</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['revenue']['revenue_by_category'] as $category)
                                        <tr>
                                            <td>{{ $category['category'] }}</td>
                                            <td>₹{{ number_format($category['total_revenue'], 2) }}</td>
                                            <td>{{ $category['total_quantity'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COGS Section -->
                <div class="section-header">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Cost of Goods Sold (COGS)</h5>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="report-card p-3">
                            <h6>COGS by Category</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Total Cost</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['cost_of_goods_sold']['cogs_by_category'] as $category)
                                        <tr>
                                            <td>{{ $category['category'] }}</td>
                                            <td>₹{{ number_format($category['total_cost'], 2) }}</td>
                                            <td>{{ $category['total_quantity'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="report-card p-3">
                            <h6>Recent Purchases</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Purchase #</th>
                                            <th>Date</th>
                                            <th>Party</th>
                                            <th>Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['cost_of_goods_sold']['purchase_details']->take(5) as $purchase)
                                        <tr>
                                            <td>{{ $purchase['purchase_number'] }}</td>
                                            <td>{{ $purchase['purchase_date'] }}</td>
                                            <td>{{ $purchase['party_name'] }}</td>
                                            <td>₹{{ number_format($purchase['total_cost'], 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operating Expenses Section -->
                <div class="section-header">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Operating Expenses (Quantra)</h5>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="report-card p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Total Operating Expenses: ₹{{ number_format($report['operating_expenses']['total_operating_expenses'], 2) }}</h6>
                                <span class="badge bg-info">{{ $report['operating_expenses']['expense_count'] }} expenses</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Amount</th>
                                            <th>Note</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['operating_expenses']['quantra_expenses'] as $expense)
                                        <tr>
                                            <td>₹{{ number_format($expense['amount'], 2) }}</td>
                                            <td>{{ $expense['note'] }}</td>
                                            <td>{{ $expense['date'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other Income Section -->
                <div class="section-header">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Other Income (Receivable Payments)</h5>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="report-card p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Net Other Income: ₹{{ number_format($report['other_income']['net_other_income'], 2) }}</h6>
                                <div>
                                    <span class="badge bg-success me-2">Total: ₹{{ number_format($report['other_income']['total_other_income'], 2) }}</span>
                                    <span class="badge bg-warning">TDS: ₹{{ number_format($report['other_income']['total_tds'], 2) }}</span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Total Payments</th>
                                            <th>Total TDS</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['other_income']['payments_by_customer'] as $customer)
                                        <tr>
                                            <td>{{ $customer['customer_name'] }}</td>
                                            <td>₹{{ number_format($customer['total_payments'], 2) }}</td>
                                            <td>₹{{ number_format($customer['total_tds'], 2) }}</td>
                                            <td>{{ $customer['payment_count'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other Expenses Section -->
                <div class="section-header">
                    <h5 class="mb-0"><i class="fas fa-minus-circle"></i> Other Expenses (Payable Payments)</h5>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="report-card p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Total Other Expenses: ₹{{ number_format($report['other_expenses']['total_other_expenses'], 2) }}</h6>
                                <span class="badge bg-danger">{{ $report['other_expenses']['payment_count'] }} payments</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Party</th>
                                            <th>Total Payments</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['other_expenses']['payments_by_party'] as $party)
                                        <tr>
                                            <td>{{ $party['party_name'] }}</td>
                                            <td>₹{{ number_format($party['total_payments'], 2) }}</td>
                                            <td>{{ $party['payment_count'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profit Summary -->
                <div class="section-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Profit Summary</h5>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="report-card p-4">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <h6 class="text-muted">Revenue</h6>
                                    <h4 class="text-primary">₹{{ number_format($report['summary']['total_revenue'], 2) }}</h4>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted">COGS</h6>
                                    <h4 class="text-warning">₹{{ number_format($report['summary']['total_cogs'], 2) }}</h4>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted">Gross Profit</h6>
                                    <h4 class="{{ $report['summary']['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ₹{{ number_format($report['summary']['gross_profit'], 2) }}
                                    </h4>
                                    <small>{{ number_format($report['summary']['gross_profit_margin'], 2) }}% margin</small>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted">Net Profit</h6>
                                    <h4 class="{{ $report['summary']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ₹{{ number_format($report['summary']['net_profit'], 2) }}
                                    </h4>
                                    <small>{{ number_format($report['summary']['net_profit_margin'], 2) }}% margin</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <h6 class="text-muted">Operating Expenses</h6>
                                    <h5 class="text-info">₹{{ number_format($report['summary']['operating_expenses'], 2) }}</h5>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted">Other Income</h6>
                                    <h5 class="text-success">₹{{ number_format($report['summary']['other_income'], 2) }}</h5>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted">Other Expenses</h6>
                                    <h5 class="text-danger">₹{{ number_format($report['summary']['other_expenses'], 2) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Info -->
                <div class="text-muted text-center">
                    <small>
                        Report generated on {{ $report['period']['generated_at'] }}
                        @if($report['period']['start_date'] && $report['period']['end_date'])
                            | Period: {{ $report['period']['start_date'] }} to {{ $report['period']['end_date'] }}
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layout.footer')
