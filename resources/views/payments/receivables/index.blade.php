@include('layout.header')

<style>
    /* Page & Card Styling */
    body {
        background-color: #f4f7f9;
    }

    .main-content-area {
        min-height: 100vh;
    }

    .card-header h1 {
        font-size: 1.25rem;
        font-weight: 600;
    }

    .table thead th {
        background-color: #e9ecef;
        font-weight: 600;
        color: #495057;
        white-space: nowrap;
        padding: 0.75rem 1rem;
    }

    .table td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
    }

    .filter-section {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: .375rem;
    }

    .status-badge {
        padding: 0.4em 0.7em;
        font-size: 0.8rem;
        font-weight: 500;
        border-radius: 50rem;
    }

    .status-paid {
        background-color: rgba(25, 135, 84, 0.1);
        color: #0f5132;
    }

    .status-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: #664d03;
    }

    .status-unpaid {
        background-color: rgba(108, 117, 125, 0.1);
        color: #495057;
    }

    .btn-group .btn {
        margin-right: 0.25rem;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .table th {
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }
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
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice Number</th>
                                <th>Customer</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Total Amount</th>
                                <th>Amount Paid</th>
                                <th>Remaining</th>
                                <th>Payment Count</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td>
                                        <strong>{{ $invoice->invoice_number ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $invoice->customer->name ?? 'N/A' }}</td>
                                    <td>{{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('d M, Y') : 'N/A' }}</td>
                                    <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M, Y') : 'N/A' }}</td>
                                    <td class="text-end">₹{{ number_format($invoice->total, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($invoice->amount_paid, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($invoice->remaining_amount, 2) }}</td>
                                    <td class="text-center">
                                        @if($invoice->payment_count > 0)
                                            <span class="badge bg-info">{{ $invoice->payment_count }}</span>
                                        @else
                                            <span class="badge bg-secondary">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->payment_status === 'paid')
                                            <span class="status-badge status-paid">Paid</span>
                                        @elseif($invoice->payment_status === 'partially_paid')
                                            <span class="status-badge status-pending">Partially Paid</span>
                                        @else
                                            <span class="status-badge status-unpaid">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($invoice->payment_count > 0)
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="showPaymentDetails({{ $invoice->id }}, '{{ $invoice->invoice_number ?? 'N/A' }}')">
                                                    <i class="fa fa-eye me-1"></i>View Payments
                                                </button>
                                            @endif
                                            @if($invoice->remaining_amount > 0.01)
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        onclick="recordPayment({{ $invoice->id }}, '{{ $invoice->customer->name ?? 'N/A' }}', {{ $invoice->remaining_amount }})">
                                                    <i class="fa fa-plus me-1"></i>Record Payment
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center p-4">No invoice entries found.</td>
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
                        <small class="text-muted" id="amount-to-be-paid">Please enter the amount received from the customer.</small>
                        @error('amount')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="modal_customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="modal_customer_id" class="form-select" required onchange="fetchInvoices()">
                            <option value="" disabled selected>Select a Customer</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select the customer who made the payment.</small>
                        @error('customer_id')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="invoices-list" class="form-label fw-bold">Unpaid Invoices</label>

                        <!-- ================================== -->
                        <!-- ==== ADD THIS SEARCH BAR HERE ==== -->
                        <!-- ================================== -->
                        <div class="mb-2">
                            <input type="text" id="invoice_search_input" class="form-control form-control-sm" placeholder="Search by Invoice #" oninput="filterInvoices()">
                        </div>
                        
                        <div id="invoices-list" class="border rounded p-3" style="min-height: 100px; max-height: 250px; overflow-y: auto; background-color: #f8f9fa;">
                            <p class="text-muted mb-0">Enter an amount and select a customer to view unpaid invoices.</p>
                        </div>

                        <input type="hidden" name="invoice_ids" id="invoice_ids" value="[]">
                        @error('invoice_ids')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="deduct_tds" name="deduct_tds" onchange="toggleTdsField()">
                                    <label class="form-check-label" for="deduct_tds">Deduct TDS</label>
                                </div>
                            </div>
                            <div class="mb-3" id="tds_amount_field" style="display: none;">
                                <label for="tds_amount" class="form-label">TDS Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="tds_amount" id="tds_amount" class="form-control" step="0.01" min="0" value="0" oninput="updateSelectedInvoices()">
                                </div>
                                <small class="text-muted">Enter TDS amount deducted.</small>
                                @error('tds_amount')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Payment Date</label>
                                <input type="date" name="payment_date" id="payment_date" class="form-control" required value="{{ \Carbon\Carbon::today()->toDateString() }}">
                                @error('payment_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
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
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                        @error('notes')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submit-payment-btn" disabled>Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentDetailsModalLabel">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="paymentDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Use more descriptive variable names for clarity
        let invoiceData = [];
        let enteredAmount = 0;

        // --- Step 1: Triggered when user types in the "Amount Received" field ---
        window.validateAmount = function() {
            enteredAmount = parseFloat(document.getElementById('modal_amount').value) || 0;
            const amountInfoSpan = document.getElementById('amount-to-be-paid');
            const customerDropdown = document.getElementById('modal_customer_id');
            const invoiceListDiv = document.getElementById('invoices-list');
            const hiddenInput = document.getElementById('invoice_ids');
            const submitButton = document.getElementById('submit-payment-btn');

            // NEW: Clear the search input when the amount changes
            document.getElementById('invoice_search_input').value = '';

            // Reset everything when the amount changes
            customerDropdown.value = '';
            invoiceListDiv.innerHTML = '<p class="text-muted mb-0">Select a customer to view unpaid invoices.</p>';
            hiddenInput.value = '[]';
            submitButton.disabled = true;
            document.getElementById('deduct_tds').checked = false;
            document.getElementById('tds_amount').value = 0;
            toggleTdsField(); // This will hide the TDS field

            amountInfoSpan.textContent = enteredAmount > 0 ?
                `Entered Amount: ₹${enteredAmount.toFixed(2)}` :
                'Please enter the amount received from the customer.';
        }

        // --- Step 2: Triggered when user selects a Customer ---
        window.fetchInvoices = function() {
            const customerId = document.getElementById('modal_customer_id').value;
            const invoiceListDiv = document.getElementById('invoices-list');
            const submitButton = document.getElementById('submit-payment-btn');

            // NEW: Clear the search input when a new customer is selected
            document.getElementById('invoice_search_input').value = '';

            // Reset UI for new customer selection
            invoiceListDiv.innerHTML = '<p class="text-muted mb-0">Loading invoices...</p>';
            submitButton.disabled = true;
            invoiceData = [];

            if (!customerId || enteredAmount <= 0) {
                invoiceListDiv.innerHTML = '<p class="text-muted mb-0">Please enter an amount first, then select a customer.</p>';
                return;
            }

            // Fetch unpaid invoices from the server
            fetch(`{{ route("receivables.getInvoicesByCustomer") }}?customer_id=${customerId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Network error: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    invoiceData = data;

                    if (!Array.isArray(data) || data.length === 0) {
                        invoiceListDiv.innerHTML = '<p class="text-muted fw-bold">No unpaid invoices found for this customer.</p>';
                        return;
                    }

                    // Build the table and auto-allocate the payment
                    buildInvoicesTable(data);
                    autoAllocatePayment();
                    updateSelectedInvoices(); // Update totals and button state
                })
                .catch(error => {
                    console.error('Error fetching invoices:', error);
                    invoiceListDiv.innerHTML = `<p class="text-danger"><strong>Error:</strong> ${error.message}</p>`;
                });
        }

        // Helper function to create the invoice table
        function buildInvoicesTable(data) {
            const invoiceListDiv = document.getElementById('invoices-list');
            invoiceListDiv.innerHTML = ''; // Clear loading text

            const table = document.createElement('table');
            table.className = 'table table-sm table-bordered';
            table.innerHTML = `
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="select-all-invoices" onchange="toggleSelectAll()"></th>
                        <th>Invoice #</th>
                        <th>Outstanding</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            const tbody = table.querySelector('tbody');

            data.forEach(invoice => {
                const row = document.createElement('tr');
                
                // MODIFIED: Add a class to the row so the filter function can find it
                row.className = 'invoice-row';
                
                row.innerHTML = `
                    <td><input type="checkbox" class="invoice-checkbox" data-id="${invoice.id}" data-amount="${invoice.amount.toFixed(2)}" onchange="updateSelectedInvoices()"></td>
                    <td>${invoice.ref_no}</td>
                    <td>₹${invoice.amount.toFixed(2)}</td>
                    <td><input type="number" class="form-control form-control-sm payment-amount" data-id="${invoice.id}" step="0.01" min="0" value="0.00" oninput="updateSelectedInvoices()"></td>
                `;
                tbody.appendChild(row);
            });
            invoiceListDiv.appendChild(table);
        }

        // Helper function to automatically apply the payment to the oldest invoices first
        function autoAllocatePayment() {
            let remainingToAllocate = enteredAmount;
            const checkboxes = document.querySelectorAll('.invoice-checkbox');

            checkboxes.forEach(checkbox => {
                const paymentInput = checkbox.closest('tr').querySelector('.payment-amount');
                paymentInput.value = '0.00';
                checkbox.checked = false;
            });

            checkboxes.forEach(checkbox => {
                if (remainingToAllocate <= 0) return;

                const outstanding = parseFloat(checkbox.dataset.amount);
                const paymentInput = checkbox.closest('tr').querySelector('.payment-amount');
                const paymentAmount = Math.min(outstanding, remainingToAllocate);

                if (paymentAmount > 0) {
                    checkbox.checked = true;
                    paymentInput.value = paymentAmount.toFixed(2);
                    remainingToAllocate -= paymentAmount;
                }
            });
        }
        
        // ===============================================
        // ==== NEW FUNCTION TO FILTER THE INVOICES ====
        // ===============================================
        window.filterInvoices = function() {
            const searchTerm = document.getElementById('invoice_search_input').value.toLowerCase();
            const rows = document.querySelectorAll('.invoice-row');

            rows.forEach(row => {
                // The invoice number is in the second cell (index 1)
                const invoiceNumberCell = row.cells[1]; 
                if (invoiceNumberCell) {
                    const invoiceNumber = invoiceNumberCell.textContent.toLowerCase();
                    // Show or hide based on match
                    if (invoiceNumber.includes(searchTerm)) {
                        row.style.display = ''; // Show
                    } else {
                        row.style.display = 'none'; // Hide
                    }
                }
            });
        }

        // --- Step 3: Triggered by almost any user action in the modal to recalculate everything ---
        window.updateSelectedInvoices = function() {
            const checkedInvoices = document.querySelectorAll('.invoice-checkbox:checked');
            const hiddenInput = document.getElementById('invoice_ids');
            const amountInfoSpan = document.getElementById('amount-to-be-paid');
            const submitButton = document.getElementById('submit-payment-btn');
            const tdsAmount = parseFloat(document.getElementById('tds_amount').value) || 0;

            let totalAllocated = 0;
            const selectedInvoicesForSubmission = [];

            // Recalculate total allocated amount from payment input fields
            checkedInvoices.forEach(checkbox => {
                const paymentInput = checkbox.closest('tr').querySelector('.payment-amount');
                const paymentAmount = parseFloat(paymentInput.value) || 0;
                totalAllocated += paymentAmount;

                if (paymentAmount > 0) {
                    selectedInvoicesForSubmission.push({
                        id: checkbox.dataset.id,
                        amount: paymentAmount
                    });
                }
            });

            // Update the hidden input for form submission
            hiddenInput.value = JSON.stringify(selectedInvoicesForSubmission);

            // Update the information display text
            const totalPayment = totalAllocated + tdsAmount;
            const balance = enteredAmount - totalPayment;
            amountInfoSpan.innerHTML = `Entered: <b class="text-dark">₹${enteredAmount.toFixed(2)}</b> | Allocated: <b class="text-primary">₹${totalAllocated.toFixed(2)}</b> | TDS: <b class="text-info">₹${tdsAmount.toFixed(2)}</b> | Balance: <b class="${Math.abs(balance) > 0.01 ? 'text-danger' : 'text-success'}">₹${balance.toFixed(2)}</b>`;

            // Enable or disable the submit button based on valid conditions
            const isInvalid = totalAllocated <= 0 || Math.abs(balance) > 0.01; // Allow for tiny rounding errors
            submitButton.disabled = isInvalid;
        }

        window.toggleSelectAll = function() {
            const masterCheckbox = document.getElementById('select-all-invoices');
            document.querySelectorAll('.invoice-checkbox').forEach(cb => {
                cb.checked = masterCheckbox.checked;
            });
            updateSelectedInvoices();
        }

        window.toggleTdsField = function() {
            const tdsField = document.getElementById('tds_amount_field');
            const tdsCheckbox = document.getElementById('deduct_tds');
            tdsField.style.display = tdsCheckbox.checked ? 'block' : 'none';
            if (!tdsCheckbox.checked) {
                document.getElementById('tds_amount').value = 0;
            }
            updateSelectedInvoices();
        }

        // Function to show payment details
        window.showPaymentDetails = function(invoiceId, invoiceNumber) {
            const modal = new bootstrap.Modal(document.getElementById('paymentDetailsModal'));
            const contentDiv = document.getElementById('paymentDetailsContent');
            const title = document.getElementById('paymentDetailsModalLabel');
            
            title.textContent = `Payment Details - ${invoiceNumber}`;
            contentDiv.innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading payment details...</p></div>';
            
            modal.show();
            
            // Fetch payment details via AJAX
            fetch(`/receivables/${invoiceId}/payments`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = `
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Invoice Number:</strong> ${data.invoice.invoice_number || 'N/A'}<br>
                                    <strong>Customer:</strong> ${data.invoice.customer.name || 'N/A'}<br>
                                    <strong>Total Amount:</strong> ₹${parseFloat(data.invoice.total).toFixed(2)}
                                </div>
                                <div class="col-md-6">
                                    <strong>Amount Paid:</strong> ₹${parseFloat(data.invoice.amount_paid).toFixed(2)}<br>
                                    <strong>Remaining:</strong> ₹${parseFloat(data.invoice.remaining_amount).toFixed(2)}<br>
                                    <strong>Payment Count:</strong> ${data.invoice.payment_count}
                                </div>
                            </div>
                            <hr>
                            <h6>Payment Breakdown:</h6>
                        `;
                        
                        if (data.payments && data.payments.length > 0) {
                            html += `
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>TDS</th>
                                                <th>Bank/Method</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;
                            
                            data.payments.forEach(payment => {
                                html += `
                                    <tr>
                                        <td>${new Date(payment.payment_date).toLocaleDateString()}</td>
                                        <td class="text-end">₹${parseFloat(payment.amount).toFixed(2)}</td>
                                        <td class="text-end">₹${parseFloat(payment.tds_amount || 0).toFixed(2)}</td>
                                        <td>${payment.bank_name || 'N/A'}</td>
                                        <td>${payment.notes || 'N/A'}</td>
                                    </tr>
                                `;
                            });
                            
                            html += `
                                        </tbody>
                                    </table>
                                </div>
                            `;
                        } else {
                            html += '<p class="text-muted">No payments recorded yet.</p>';
                        }
                        
                        contentDiv.innerHTML = html;
                    } else {
                        contentDiv.innerHTML = '<div class="alert alert-danger">Error loading payment details.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = '<div class="alert alert-danger">Error loading payment details.</div>';
                });
        };

        // Function to record payment for a specific invoice
        window.recordPayment = function(invoiceId, customerName, remainingAmount) {
            // Set the customer and amount in the payment modal
            document.getElementById('modal_customer_id').value = customerName;
            document.getElementById('modal_amount').value = remainingAmount;
            
            // Trigger the modal
            const modal = new bootstrap.Modal(document.getElementById('receivablePaymentModal'));
            modal.show();
            
            // Fetch invoices for this customer
            fetchInvoices();
        };
    });
</script>
@include('layout.footer')