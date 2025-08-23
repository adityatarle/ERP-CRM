@include('layout.header')

<style>
    /* Enhanced Receivable Payment History Styling */
    .payment-history-container {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        min-height: 100vh;
        padding: 20px 0;
    }
    
    .summary-cards {
        margin-bottom: 30px;
    }
    
    .summary-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }
    
    .summary-card .card-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        opacity: 0.8;
    }
    
    .summary-card .card-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .summary-card .card-label {
        font-size: 0.9rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .customer-section {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    
    .customer-section:hover {
        transform: translateY(-3px);
    }
    
    .customer-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 20px;
        position: relative;
    }
    
    .customer-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.1);
        clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
    }
    
    .customer-header .customer-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 5px;
        position: relative;
        z-index: 1;
    }
    
    .customer-header .customer-stats {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }
    
    .customer-stat {
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        backdrop-filter: blur(10px);
    }
    
    .payment-timeline {
        padding: 20px;
    }
    
    .payment-item {
        display: flex;
        align-items: center;
        padding: 15px;
        margin-bottom: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #28a745;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .payment-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .payment-item::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 50%;
        transform: translateY(-50%);
        width: 12px;
        height: 12px;
        background: #28a745;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 3px #28a745;
    }
    
    .payment-date {
        min-width: 120px;
        text-align: center;
        padding: 8px 15px;
        background: #28a745;
        color: white;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .payment-details {
        flex: 1;
        margin-left: 20px;
    }
    
    .payment-amount {
        font-size: 1.2rem;
        font-weight: 700;
        color: #28a745;
        margin-bottom: 5px;
    }
    
    .payment-info {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .payment-meta {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        font-size: 0.8rem;
    }
    
    .meta-item {
        background: #e9ecef;
        padding: 4px 8px;
        border-radius: 12px;
        color: #495057;
    }
    
    .payment-actions {
        margin-left: 20px;
    }
    
    .btn-details {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }
    
    .btn-details:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        color: white;
    }
    
    .no-payments {
        text-align: center;
        padding: 40px;
        color: #6c757d;
        font-style: italic;
    }
    
    .back-btn {
        background: rgba(255, 255, 255, 0.9);
        color: #28a745;
        border: 2px solid #28a745;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .back-btn:hover {
        background: #28a745;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        text-decoration: none;
    }
    
    .page-title {
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 30px;
        text-align: center;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }
    
    .filter-section {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    .filter-form .form-label {
        font-weight: 500;
        color: #495057;
    }
    
    .filter-form .btn-clear {
        height: calc(2.25rem + 2px);
        line-height: 1.5;
    }
    
    @media (max-width: 768px) {
        .customer-stats {
            flex-direction: column;
            gap: 10px;
        }
        
        .payment-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .payment-date {
            min-width: auto;
            width: 100%;
            text-align: center;
        }
        
        .payment-actions {
            margin-left: 0;
            width: 100%;
            text-align: center;
        }
        
        .payment-meta {
            flex-direction: column;
            gap: 8px;
        }
    }
</style>

<div class="payment-history-container">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h1 class="page-title">
                <i class="fa fa-history me-3"></i>
                Receivable Payments History
            </h1>
            <a href="{{ route('receivables') }}" class="back-btn">
                <i class="fa fa-arrow-left"></i>
                Back to Receivables
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="row summary-cards">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card text-center">
                    <div class="card-icon text-success">
                        <i class="fa fa-money-bill-wave"></i>
                    </div>
                    <div class="card-value text-success">₹{{ number_format($summary['total_amount'], 2) }}</div>
                    <div class="card-label">Total Payments</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card text-center">
                    <div class="card-icon text-primary">
                        <i class="fa fa-handshake"></i>
                    </div>
                    <div class="card-value text-primary">{{ $summary['total_payments'] }}</div>
                    <div class="card-label">Payment Entries</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card text-center">
                    <div class="card-icon text-info">
                        <i class="fa fa-users"></i>
                    </div>
                    <div class="card-value text-info">{{ $summary['total_customers'] }}</div>
                    <div class="card-label">Total Customers</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card text-center">
                    <div class="card-icon text-warning">
                        <i class="fa fa-percentage"></i>
                    </div>
                    <div class="card-value text-warning">₹{{ number_format($summary['total_tds'], 2) }}</div>
                    <div class="card-label">Total TDS</div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('receivables.paymentsList') }}" class="filter-form" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <label for="tds_filter" class="form-label small">Filter by TDS</label>
                        <select name="tds_filter" id="tds_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" {{ $tdsFilter == 'all' ? 'selected' : '' }}>All Records</option>
                            <option value="with_tds" {{ $tdsFilter == 'with_tds' ? 'selected' : '' }}>With TDS</option>
                            <option value="without_tds" {{ $tdsFilter == 'without_tds' ? 'selected' : '' }}>Without TDS</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="sort_by" class="form-label small">Sort By</label>
                        <select name="sort_by" id="sort_by" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="payment_date" {{ $sortBy == 'payment_date' ? 'selected' : '' }}>Payment Date</option>
                            <option value="invoice_number" {{ $sortBy == 'invoice_number' ? 'selected' : '' }}>Invoice Number</option>
                            <option value="customer_name" {{ $sortBy == 'customer_name' ? 'selected' : '' }}>Customer Name</option>
                            <option value="amount" {{ $sortBy == 'amount' ? 'selected' : '' }}>Amount</option>
                            <option value="due_date" {{ $sortBy == 'due_date' ? 'selected' : '' }}>Due Date</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="sort_dir" class="form-label small">Sort Direction</label>
                        <select name="sort_dir" id="sort_dir" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="desc" {{ $sortDir == 'desc' ? 'selected' : '' }}>Descending</option>
                            <option value="asc" {{ $sortDir == 'asc' ? 'selected' : '' }}>Ascending</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('receivables.paymentsList') }}" class="btn btn-outline-secondary btn-sm w-100">Clear Filter & Sort</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Customer-wise Payment Sections -->
        @forelse($paymentsByCustomer as $customerId => $customerPayments)
            @php
                $customer = $customerPayments->first()->customer;
                $customerTotal = $customerPayments->sum('amount');
                $customerTds = $customerPayments->sum('tds_amount');
                $customerCount = $customerPayments->count();
                $earliestPayment = $customerPayments->min('payment_date');
                $latestPayment = $customerPayments->max('payment_date');
            @endphp
            
            <div class="customer-section">
                <div class="customer-header">
                    <div class="customer-name">{{ $customer->name ?? 'Unknown Customer' }}</div>
                    <div class="customer-stats">
                        <div class="customer-stat">
                            <i class="fa fa-money-bill me-2"></i>
                            ₹{{ number_format($customerTotal, 2) }}
                        </div>
                        <div class="customer-stat">
                            <i class="fa fa-list me-2"></i>
                            {{ $customerCount }} payments
                        </div>
                        <div class="customer-stat">
                            <i class="fa fa-percentage me-2"></i>
                            ₹{{ number_format($customerTds, 2) }} TDS
                        </div>
                        <div class="customer-stat">
                            <i class="fa fa-calendar me-2"></i>
                            {{ \Carbon\Carbon::parse($earliestPayment)->format('M Y') }} - {{ \Carbon\Carbon::parse($latestPayment)->format('M Y') }}
                        </div>
                    </div>
                </div>
                
                <div class="payment-timeline">
                    @foreach($customerPayments->sortByDesc('payment_date') as $payment)
                        @php
                            $creditDays = 'N/A';
                            if ($payment->invoice && $payment->invoice->issue_date && $payment->invoice->due_date) {
                                $creditDays = \Carbon\Carbon::parse($payment->invoice->issue_date)->diffInDays(\Carbon\Carbon::parse($payment->invoice->due_date));
                            }
                        @endphp
                        
                        <div class="payment-item">
                            <div class="payment-date">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                            </div>
                            
                            <div class="payment-details">
                                <div class="payment-amount">
                                    ₹{{ number_format($payment->amount, 2) }}
                                </div>
                                <div class="payment-info">
                                    @if($payment->invoice)
                                        <strong>Invoice:</strong> 
                                        <a href="{{ route('invoices.show', $payment->invoice->id) }}" class="text-decoration-none">
                                            {{ $payment->invoice->invoice_number }}
                                        </a>
                                    @endif
                                    @if($payment->bank_name)
                                        | <strong>Bank:</strong> {{ $payment->bank_name }}
                                    @endif
                                    @if($payment->notes)
                                        | <strong>Notes:</strong> {{ $payment->notes }}
                                    @endif
                                </div>
                                <div class="payment-meta">
                                    @if($payment->tds_amount > 0)
                                        <span class="meta-item">
                                            <i class="fa fa-percentage me-1"></i>
                                            TDS: ₹{{ number_format($payment->tds_amount, 2) }}
                                        </span>
                                    @endif
                                    @if($payment->invoice && $payment->invoice->due_date)
                                        <span class="meta-item">
                                            <i class="fa fa-calendar me-1"></i>
                                            Due: {{ \Carbon\Carbon::parse($payment->invoice->due_date)->format('d M Y') }}
                                        </span>
                                    @endif
                                    @if($creditDays != 'N/A')
                                        <span class="meta-item">
                                            <i class="fa fa-clock me-1"></i>
                                            {{ $creditDays }} days credit
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="payment-actions">
                                <button class="btn btn-details" 
                                        onclick="showPaymentDetails({{ $payment->id }})"
                                        title="View Payment Details">
                                    <i class="fa fa-eye me-1"></i>
                                    Details
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="customer-section">
                <div class="no-payments">
                    <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                    <h4>No Payment History Found</h4>
                    <p>There are no receivable payments recorded yet.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentDetailsModalLabel">
                    <i class="fa fa-info-circle me-2"></i>
                    Payment Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <!-- Payment details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@include('layout.footer')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showPaymentDetails(paymentId) {
    // This function would typically make an AJAX call to get detailed payment information
    // For now, we'll show a simple alert with the payment ID
    Swal.fire({
        title: 'Payment Details',
        text: `Payment ID: ${paymentId}`,
        icon: 'info',
        confirmButtonText: 'OK'
    });
    
    // In a real implementation, you would:
    // 1. Make an AJAX call to get payment details
    // 2. Populate the modal with detailed information
    // 3. Show the modal
}

// Add some interactive features
document.addEventListener('DOMContentLoaded', function() {
    // Add click effects to customer sections
    const customerSections = document.querySelectorAll('.customer-section');
    customerSections.forEach(section => {
        section.addEventListener('click', function(e) {
            if (!e.target.closest('.btn-details')) {
                this.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 200);
            }
        });
    });
});
</script>
