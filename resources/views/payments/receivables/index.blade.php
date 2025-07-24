@include('layout.header')

<style>
    body { background-color: #f4f7f9; }
    .main-content-area { min-height: 100vh; }
    .card-header h5 { font-size: 1.25rem; font-weight: 600; }
    .table thead th {
        background-color: #e9ecef; font-weight: 600;
        color: #495057; white-space: nowrap; padding: 0.75rem 1rem;
    }
    .table td { vertical-align: middle; padding: 0.75rem 1rem; }
    .filter-section {
        background-color: #f8f9fa; border: 1px solid #dee2e6;
        border-radius: .375rem;
    }
    .badge { padding: 0.4em 0.7em; font-size: 0.8rem; font-weight: 500; border-radius: 50rem; }
</style>

<div class="main-content-area">
    <div class="container p-3 mx-auto">
        <div class="card shadow-sm w-100 border-0">
            <div class="card-header bg-primary text-white d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center p-3">
                <h5 class="mb-0 text-white"><i class="fa fa-money-bill-wave me-2"></i> Receivables</h5>
                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    <a href="#" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#receivablePaymentModal">Record Payment</a>
                    <a href="{{ route('receivables.paymentsList') }}" class="btn btn-light btn-sm">View Payment History</a>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- Success/Error Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Filter Form -->
                <div class="filter-section p-3 mb-4">
                    <form method="GET" action="{{ route('receivables') }}" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="customer_search" class="form-label small">Customer Name</label>
                                <input type="text" name="customer_search" id="customer_search" class="form-control form-control-sm" value="{{ $customer_search ?? '' }}" placeholder="Enter customer name">
                            </div>
                            <div class="col-md-3">
                                <label for="start_date" class="form-label small">Start Date (Created)</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label small">End Date (Created)</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                                <a href="{{ route('receivables') }}" class="btn btn-secondary btn-sm"><i class="fa fa-times"></i> Clear</a>
                                <button type="button" class="btn btn-success btn-sm" onclick="exportToExcel()"><i class="fa fa-file-excel"></i> Export</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Receivables Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ref. Number</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Credit Days</th>
                                <th>Due Status</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receivables as $receivable)
                                <tr>
                                    <td class="text-primary fw-bold">{{ $receivable->sale->ref_no ?? $receivable->invoice->invoice_number ?? 'N/A' }}</td>
                                    <td>{{ $receivable->customer->name ?? 'N/A' }}</td>
                                    <td>₹{{ number_format($receivable->amount, 2) }}</td>
                                    <td>{{ $receivable->credit_days ?? 'N/A' }}</td>
                                    <td>
                                        @if ($receivable->is_paid)
                                            <span class="badge bg-light text-dark">N/A (Paid)</span>
                                        @elseif (is_null($receivable->due_days) && !$receivable->created_at)
                                            <span class="badge bg-secondary">Invalid Date</span>
                                        @elseif (is_null($receivable->due_days))
                                            <span class="badge bg-secondary">N/A</span>
                                        @elseif ($receivable->due_days === 0)
                                            <span class="badge bg-warning text-dark">Due Today</span>
                                        @elseif ($receivable->is_overdue)
                                            <span class="badge bg-danger">{{ round($receivable->due_days) }} days overdue</span>
                                        @else
                                            <span class="badge bg-info text-dark">{{ round(abs($receivable->due_days)) }} days remaining</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($receivable->is_paid)
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center p-4">No receivables found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Recording Payment -->
<div class="modal fade" id="receivablePaymentModal" tabindex="-1" aria-labelledby="receivablePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receivablePaymentModalLabel">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('receivables.store') }}" method="POST" id="receivablePaymentForm">
                    @csrf
                    <div class="mb-3">
                        <label for="modal_amount" class="form-label">Amount Received <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="amount" id="modal_amount" class="form-control" step="0.01" min="0.01" required oninput="validateAmount()" placeholder="Enter received amount">
                        </div>
                        <small class="text-muted" id="amount-to-be-paid">Please enter the amount received from customer.</small>
                        @error('amount')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="modal_customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="modal_customer_id" class="form-select" required onchange="fetchSales()">
                            <option value="">Select a Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select the customer who made the payment.</small>
                        @error('customer_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="sales-list" class="form-label fw-bold">Unpaid Invoices/Sales</label>
                        <div id="sales-list" class="border rounded p-3" style="min-height: 100px; background-color: #f8f9fa;">
                            <p class="text-muted mb-0">Select a customer to view unpaid invoices/sales.</p>
                        </div>
                        <input type="hidden" name="receivable_ids" id="receivable_ids" value="[]">
                        @error('receivable_ids')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="deduct_tds" name="deduct_tds" onchange="toggleTdsField()">
                            <label class="form-check-label" for="deduct_tds">Deduct TDS</label>
                        </div>
                    </div>
                    <div class="mb-3" id="tds_amount_field" style="display: none;">
                        <label for="tds_amount" class="form-label">TDS Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="tds_amount" id="tds_amount" class="form-control" step="0.01" min="0" value="0" oninput="updateSelectedSales()">
                        </div>
                        <small class="text-muted">Enter TDS amount deducted by customer.</small>
                        @error('tds_amount')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" id="payment_date" class="form-control" required value="{{ \Carbon\Carbon::today()->toDateString() }}">
                        @error('payment_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="bank_name" class="form-label">Payment Method</label>
                        <select name="bank_name" id="bank_name" class="form-select">
                            <option value="">Select Payment Method (Optional)</option>
                            <option value="Cash">Cash</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="ICICI Bank">ICICI Bank</option>
                            <option value="Union Bank">Union Bank</option>
                            <option value="Other">Other</option>
                        </select>
                        @error('bank_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                        @error('notes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="submit-payment-btn" disabled>Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let salesData = [];
    let enteredAmount = 0;

    window.validateAmount = function() {
        enteredAmount = parseFloat(document.getElementById('modal_amount').value) || 0;
        const amountToBePaidSpan = document.getElementById('amount-to-be-paid');
        const salesList = document.getElementById('sales-list');
        const submitButton = document.getElementById('submit-payment-btn');
        const customerDropdown = document.getElementById('modal_customer_id');
        const hiddenInput = document.getElementById('receivable_ids');

        // Don't clear the customer selection or sales list when amount changes
        // salesList.innerHTML = '<p class="text-muted mb-0">Select a customer to view unpaid invoices/sales.</p>';
        // customerDropdown.value = '';
        hiddenInput.value = '[]';
        submitButton.disabled = true;
        document.getElementById('deduct_tds').checked = false;
        document.getElementById('tds_amount').value = 0;
        toggleTdsField();

        if (enteredAmount <= 0) {
            amountToBePaidSpan.textContent = 'Please enter a valid amount greater than 0.';
        } else {
            amountToBePaidSpan.textContent = `Entered Amount: ₹${enteredAmount.toFixed(2)}`;
            // If there are already displayed sales/invoices, update the allocation
            if (salesData && salesData.length > 0) {
                updateSelectedReceivables();
            }
        }
    }

    window.fetchSales = function() {
        const customerId = document.getElementById('modal_customer_id').value;
        const salesList = document.getElementById('sales-list');
        const amountToBePaidSpan = document.getElementById('amount-to-be-paid');
        const submitButton = document.getElementById('submit-payment-btn');
        const hiddenInput = document.getElementById('receivable_ids');

        salesList.innerHTML = '<p class="text-muted">Loading unpaid invoices/sales...</p>';
        amountToBePaidSpan.textContent = enteredAmount > 0 ? `Entered Amount: ₹${enteredAmount.toFixed(2)}` : 'Please enter a valid amount.';
        hiddenInput.value = '[]';
        salesData = [];
        submitButton.disabled = true;

        if (!customerId) {
            salesList.innerHTML = '<p class="text-muted">Please select a customer to view unpaid invoices/sales.</p>';
            return;
        }

        fetch(`{{ route("receivables.getSalesByCustomer") }}?customer_id=${customerId}`, {
            headers: { 
                'X-Requested-With': 'XMLHttpRequest', 
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
        })
        .then(response => {
            if (response.status === 401) {
                throw new Error('Authentication required. Please log in to access this feature.');
            }
            if (!response.ok) {
                throw new Error(`Failed to fetch invoices/sales (${response.status})`);
            }
            return response.json();
        })
        .then(data => {
            salesData = data;
            salesList.innerHTML = '';
            hiddenInput.value = '[]';

            if (data.length === 0) {
                salesList.innerHTML = '<p class="text-muted">No unpaid invoices/sales found for this customer.</p>';
                submitButton.disabled = true;
                amountToBePaidSpan.textContent = `Entered Amount: ₹${enteredAmount.toFixed(2)}`;
                return;
            }

            let totalOutstanding = data.reduce((sum, sale) => sum + parseFloat(sale.amount), 0);

            // Create table for sales with checkboxes
            const tableContainer = document.createElement('div');
            tableContainer.innerHTML = `
                <div class="alert alert-info alert-sm mb-3">
                    <strong>Instructions:</strong> Select the invoices/sales you want to apply payment to and enter the payment amount for each.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="50"><input type="checkbox" id="select-all-sales" onchange="toggleSelectAll()" title="Select All"></th>
                                <th>Type</th>
                                <th>Reference Number</th>
                                <th width="120">Outstanding Amount</th>
                                <th width="150">Payment Amount</th>
                            </tr>
                        </thead>
                        <tbody id="sales-tbody"></tbody>
                    </table>
                </div>
            `;
            const tbody = tableContainer.querySelector('#sales-tbody');

            data.forEach(receivable => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><input type="checkbox" class="receivable-checkbox" data-id="${receivable.id}" data-amount="${parseFloat(receivable.amount).toFixed(2)}" onchange="updateSelectedReceivables()"></td>
                    <td><span class="badge ${receivable.type === 'Invoice' ? 'bg-primary' : 'bg-secondary'}">${receivable.type}</span></td>
                    <td>${receivable.ref_no}</td>
                    <td>₹${parseFloat(receivable.amount).toFixed(2)}</td>
                    <td><input type="number" class="form-control form-control-sm payment-amount" data-id="${receivable.id}" step="0.01" min="0" value="0" disabled onchange="updateSelectedReceivables()"></td>
                `;
                tbody.appendChild(row);
            });

            salesList.appendChild(tableContainer);
            amountToBePaidSpan.textContent = `Total Outstanding: ₹${totalOutstanding.toFixed(2)}. Entered Amount: ₹${enteredAmount.toFixed(2)}.`;
        })
        .catch(error => {
            console.error('Error fetching invoices/sales:', error);
            if (error.message.includes('Authentication required')) {
                salesList.innerHTML = '<p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Please log in to view unpaid invoices/sales.</p>';
            } else {
                salesList.innerHTML = '<p class="text-danger"><i class="fas fa-exclamation-circle"></i> Error loading invoices/sales: ' + error.message + '</p>';
            }
            submitButton.disabled = true;
        });
    }

    window.toggleSelectAll = function() {
        const selectAllCheckbox = document.getElementById('select-all-sales');
        const checkboxes = document.querySelectorAll('.receivable-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
            const paymentInput = checkbox.closest('tr').querySelector('.payment-amount');
            paymentInput.disabled = !checkbox.checked;
            if (!checkbox.checked) paymentInput.value = '0';
        });
        updateSelectedReceivables();
    }

    window.toggleTdsField = function() {
        const deductTdsCheckbox = document.getElementById('deduct_tds');
        const tdsAmountField = document.getElementById('tds_amount_field');
        const tdsAmountInput = document.getElementById('tds_amount');

        if (deductTdsCheckbox.checked) {
            tdsAmountField.style.display = 'block';
        } else {
            tdsAmountField.style.display = 'none';
            tdsAmountInput.value = 0;
        }
        updateSelectedReceivables();
    }

    window.updateSelectedReceivables = function() {
        const checkboxes = document.querySelectorAll('.receivable-checkbox:checked');
        const hiddenInput = document.getElementById('receivable_ids');
        const amountToBePaidSpan = document.getElementById('amount-to-be-paid');
        const submitButton = document.getElementById('submit-payment-btn');
        const deductTdsCheckbox = document.getElementById('deduct_tds');
        const tdsAmountInput = document.getElementById('tds_amount');

        let tdsAmount = deductTdsCheckbox.checked ? parseFloat(tdsAmountInput.value) || 0 : 0;
        let remainingAmount = enteredAmount - tdsAmount;
        let totalAllocated = 0;
        const selectedReceivables = [];

        if (remainingAmount < 0) {
            amountToBePaidSpan.textContent = `Error: TDS amount (₹${tdsAmount.toFixed(2)}) exceeds entered amount (₹${enteredAmount.toFixed(2)}).`;
            submitButton.disabled = true;
            hiddenInput.value = '[]';
            return;
        }

        checkboxes.forEach(checkbox => {
            const receivableId = checkbox.getAttribute('data-id');
            const outstandingAmount = parseFloat(checkbox.getAttribute('data-amount'));
            const paymentInput = checkbox.closest('tr').querySelector('.payment-amount');
            let paymentAmount = parseFloat(paymentInput.value) || 0;

            // Limit payment amount to outstanding amount and remaining entered amount
            paymentAmount = Math.min(paymentAmount, outstandingAmount, remainingAmount);
            paymentInput.value = paymentAmount.toFixed(2);
            remainingAmount -= paymentAmount;
            totalAllocated += paymentAmount;

            if (paymentAmount > 0) {
                selectedReceivables.push({ id: receivableId, amount: paymentAmount });
            }
        });

        try {
            hiddenInput.value = JSON.stringify(selectedReceivables);
        } catch (e) {
            console.error('Error stringifying selected receivables:', e);
            amountToBePaidSpan.textContent = 'Error processing receivables.';
            submitButton.disabled = true;
            return;
        }

        amountToBePaidSpan.textContent = `Total Outstanding: ₹${salesData.reduce((sum, receivable) => sum + parseFloat(receivable.amount), 0).toFixed(2)}. Allocated: ₹${totalAllocated.toFixed(2)}. TDS: ₹${tdsAmount.toFixed(2)}. Remaining: ₹${remainingAmount.toFixed(2)}.`;
        submitButton.disabled = totalAllocated <= 0 || (totalAllocated + tdsAmount) > enteredAmount;
    }

    function exportToExcel() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const customerSearch = document.getElementById('customer_search').value;

        let exportUrl = '{{ route("receivables.export") }}';
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (customerSearch) params.append('customer_search', customerSearch);

        if (params.toString()) {
            exportUrl += '?' + params.toString();
        }

        window.location.href = exportUrl;
    }
</script>
@include('layout.footer')