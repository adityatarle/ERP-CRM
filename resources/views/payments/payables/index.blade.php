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
</style>

<body class="act-payments">
    <div class="main-content-area">
        <div class="container p-3 p-md-4 mx-auto">
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please correct the following errors:
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card shadow-sm w-100 border-0">
                <div class="card-header bg-primary d-flex flex-column flex-md-row justify-content-between align-items-md-center text-white p-3">
                    <h1 class="mb-2 mb-md-0 h5 text-white">
                        <i class="fa fa-money-bill-wave me-2"></i> Accounts Payable
                    </h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('payments.payables.create') }}" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#payablePaymentModal">
                            <i class="fa fa-plus me-1"></i> Record Payment
                        </a>
                        <a href="{{ route('payments.payables.list') }}" class="btn btn-light btn-sm">
                            <i class="fa fa-history me-1"></i> View History
                        </a>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <!-- Filter Form -->
                    <div class="filter-section p-3 mb-4">
                        <form method="GET" action="{{ route('payables') }}" id="filterForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-2">
                                    <label for="party_search" class="form-label small">Party Name</label>
                                    <input type="text" name="party_search" id="party_search" class="form-control form-control-sm" value="{{ $party_search ?? '' }}" placeholder="Enter party name">
                                </div>
                                <div class="col-md-2">
                                    <label for="invoice_search" class="form-label small">Invoice Number</label>
                                    <input type="text" name="invoice_search" id="invoice_search" class="form-control form-control-sm" value="{{ $invoice_search ?? '' }}" placeholder="Enter invoice number">
                                </div>
                                <div class="col-md-2">
                                    <label for="start_date" class="form-label small">Purchase Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="end_date" class="form-label small">Purchase End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="invoice_date_from" class="form-label small">Invoice Start Date</label>
                                    <input type="date" name="invoice_date_from" id="invoice_date_from" class="form-control form-control-sm" value="{{ $invoice_date_from ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="invoice_date_to" class="form-label small">Invoice End Date</label>
                                    <input type="date" name="invoice_date_to" id="invoice_date_to" class="form-control form-control-sm" value="{{ $invoice_date_to ?? '' }}">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12 d-flex justify-content-start align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                                    <a href="{{ route('payables') }}" class="btn btn-secondary btn-sm"><i class="fa fa-times"></i> Clear</a>
                                    <button type="button" class="btn btn-success btn-sm" onclick="exportToExcel()"><i class="fa fa-file-excel"></i> Export</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Payables Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-3">Purchase #</th>
                                    <th>Party</th>
                                    <th>Purchase Date</th>
                                    <th>Invoice #</th>
                                    <th>Invoice Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payables as $payable)
                                <tr>
                                    {{-- THE ONLY CHANGE IS HERE --}}
                                    <td class="ps-3 fw-bold text-primary">
                                        @if($payable->purchaseEntry)
                                        <a href="{{ route('purchase_entries.show', $payable->purchaseEntry->id) }}" title="View Purchase Entry Details">
                                            {{ $payable->purchaseEntry->purchase_number }}
                                        </a>
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td>{{ $payable->party->name ?? 'N/A' }}</td>
                                    <td>{{ $payable->purchaseEntry ? \Carbon\Carbon::parse($payable->purchaseEntry->purchase_date)->format('d M, Y') : 'N/A' }}</td>
                                    <td>{{ $payable->invoice_number ?? '-' }}</td>
                                    <td>{{ $payable->invoice_date ? \Carbon\Carbon::parse($payable->invoice_date)->format('d M, Y') : '-' }}</td>
                                    <td class="text-end">₹{{ number_format($payable->amount, 2) }}</td>
                                    <td class="text-center">
                                        @if($payable->is_paid)
                                        <span class="status-badge status-paid">Paid</span>
                                        @else
                                        <span class="status-badge status-pending">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center p-4">No payables found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($payables->hasPages())
                <div class="card-footer bg-light border-top">
                    {{ $payables->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div class="modal fade" id="payablePaymentModal" tabindex="-1" aria-labelledby="payablePaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payablePaymentModalLabel">Record Payment to Party</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('payments.payables.store') }}" method="POST" id="payablePaymentForm">
                        @csrf
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0.01" required oninput="validateAmount()">
                            </div>
                            <small class="text-muted" id="amount-to-be-paid"></small>
                            @error('amount')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="modal_party_id" class="form-label">Party</label>
                            <select name="party_id" id="modal_party_id" class="form-select" required onchange="fetchPurchaseEntries()">
                                <option value="">Select a Party</option>
                                @foreach($parties as $party)
                                <option value="{{ $party->id }}">{{ $party->name }}</option>
                                @endforeach
                            </select>
                            @error('party_id')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="purchase-entries-list" class="form-label fw-bold">Unpaid Purchase Entries</label>

                            <div class="mb-2">
                                <input type="text" id="payable_search_input" class="form-control form-control-sm"
                                    placeholder="Search by Purchase #"
                                    oninput="filterPayableEntries()">
                            </div>

                            <div id="purchase-entries-list" class="border rounded p-2" style="max-height: 250px; overflow-y: auto; background-color: #f8f9fa;">
                                <p class="text-muted p-2">Select a party to view unpaid purchase entries.</p>
                            </div>

                            <input type="hidden" name="purchase_entry_ids" id="purchase_entry_ids" value="[]">
                            @error('purchase_entry_ids')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="invoice_date" class="form-label">Invoice Date</label>
                            <input type="date" name="invoice_date" id="invoice_date" class="form-control">
                            @error('invoice_date')
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
                                <option value="">Select Payment Method</option>
                                <option value="Cash">Cash</option>
                                <option value="ICICI">ICICI Bank</option>
                                <option value="Union Bank">Union Bank</option>
                            </select>
                            @error('bank_name')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control"></textarea>
                            @error('notes')
                            <div class="text-danger">{{ $message }}</div>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Global variables to hold the state within the modal
        let purchaseEntriesData = [];
        let enteredAmount = 0;

        /**
         * Resets the form when the main "Amount" is changed.
         */
        window.validateAmount = function() {
            enteredAmount = parseFloat(document.getElementById('amount').value) || 0;
            const amountToBePaidSpan = document.getElementById('amount-to-be-paid');
            const purchaseEntriesList = document.getElementById('purchase-entries-list');
            const partyDropdown = document.getElementById('modal_party_id');
            
            // Clear the search input if it exists
            const searchInput = document.getElementById('payable_search_input');
            if (searchInput) {
                searchInput.value = '';
            }

            // Reset the form to its initial state
            purchaseEntriesList.innerHTML = '<p class="text-muted p-2">Select a party to view unpaid purchase entries.</p>';
            partyDropdown.value = '';
            document.getElementById('purchase_entry_ids').value = '[]';
            document.getElementById('submit-payment-btn').disabled = true;

            amountToBePaidSpan.textContent = enteredAmount > 0 ? `Entered Amount: ₹${enteredAmount.toFixed(2)}` : 'Please enter a valid amount greater than 0.';
        }

        /**
         * Fetches unpaid entries for the selected party and orchestrates the UI build.
         */
        window.fetchPurchaseEntries = function() {
            const partyId = document.getElementById('modal_party_id').value;
            const purchaseEntriesList = document.getElementById('purchase-entries-list');
            
            // Clear search on new party selection
            const searchInput = document.getElementById('payable_search_input');
            if (searchInput) {
                searchInput.value = '';
            }

            purchaseEntriesList.innerHTML = '<p class="text-muted p-2">Loading...</p>';
            document.getElementById('submit-payment-btn').disabled = true;
            purchaseEntriesData = [];

            if (!partyId || enteredAmount <= 0) {
                purchaseEntriesList.innerHTML = '<p class="text-muted p-2">Please enter an amount and select a party.</p>';
                return;
            }

            fetch(`{{ route("payables.getPurchaseEntriesByParty") }}?party_id=${partyId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            })
            .then(response => response.json())
            .then(data => {
                purchaseEntriesData = data;
                
                if (data.length === 0) {
                    purchaseEntriesList.innerHTML = '<p class="text-muted fw-bold p-2">No unpaid purchase entries found for this party.</p>';
                    updateSelectedEntries(); // Allow payment on account by updating totals
                    return;
                }

                // --- This is the correct, robust sequence ---
                // 1. Build the HTML table structure.
                buildPayableEntriesTable(data);
                // 2. Perform the one-time initial auto-allocation of the entered amount.
                autoAllocatePayment();
                // 3. Update the summary text and button state based on the initial allocation.
                updateSelectedEntries();
            })
            .catch(error => {
                console.error('Error fetching purchase entries:', error);
                purchaseEntriesList.innerHTML = '<p class="text-danger p-2">Error loading entries.</p>';
            });
        }

        /**
         * Builds the HTML table structure for the unpaid entries.
         * It adds the 'purchase-entry-row' class for the search filter.
         */
        function buildPayableEntriesTable(data) {
            const purchaseEntriesList = document.getElementById('purchase-entries-list');
            purchaseEntriesList.innerHTML = '';
            const table = document.createElement('table');
            table.className = 'table table-sm table-bordered';
            table.innerHTML = `
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="select-all-entries" onchange="toggleSelectAll()"></th>
                        <th>Purchase #</th>
                        <th>Date</th>
                        <th class="text-end">Outstanding</th>
                        <th class="text-end">Payment</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            const tbody = table.querySelector('tbody');

            data.forEach(entry => {
                const row = document.createElement('tr');
                row.className = 'purchase-entry-row'; // The hook for the search filter
                row.innerHTML = `
                    <td><input type="checkbox" class="entry-checkbox" data-id="${entry.id}" data-amount="${parseFloat(entry.amount).toFixed(2)}" onchange="updateSelectedEntries()"></td>
                    <td>${entry.purchase_number ?? 'N/A'}</td>
                    <td>${entry.purchase_date ? new Date(entry.purchase_date).toLocaleDateString('en-GB') : 'N/A'}</td>
                    <td class="text-end">₹${parseFloat(entry.amount).toFixed(2)}</td>
                    <td><input type="number" class="form-control form-control-sm payment-amount text-end" data-id="${entry.id}" step="0.01" min="0" value="0.00" oninput="updateSelectedEntries()"></td>
                `;
                tbody.appendChild(row);
            });
            purchaseEntriesList.appendChild(table);
        }

        /**
         * Performs the initial, one-time auto-allocation of the payment amount
         * across the unpaid entries after they have been loaded.
         */
        function autoAllocatePayment() {
            let amountToAllocate = enteredAmount;
            document.querySelectorAll('.purchase-entry-row').forEach(row => {
                if (amountToAllocate <= 0) return;

                const checkbox = row.querySelector('.entry-checkbox');
                const paymentInput = row.querySelector('.payment-amount');
                const outstanding = parseFloat(checkbox.dataset.amount);

                const paymentAmount = Math.min(outstanding, amountToAllocate);
                
                if (paymentAmount > 0) {
                    checkbox.checked = true;
                    paymentInput.value = paymentAmount.toFixed(2);
                    amountToAllocate -= paymentAmount;
                }
            });
        }

        /**
         * Filters the list of purchase entries based on the search input.
         * This function is now completely independent of the payment logic.
         */
        window.filterPayableEntries = function() {
            const searchTerm = document.getElementById('payable_search_input').value.toLowerCase();
            document.querySelectorAll('.purchase-entry-row').forEach(row => {
                const purchaseNumberCell = row.cells[1]; // The cell with the Purchase #
                if (purchaseNumberCell) {
                    const purchaseNumber = purchaseNumberCell.textContent.toLowerCase();
                    row.style.display = purchaseNumber.includes(searchTerm) ? '' : 'none';
                }
            });
        }

        /**
         * Toggles all *visible* checkboxes.
         */
        window.toggleSelectAll = function() {
            const selectAllCheckbox = document.getElementById('select-all-entries');
            document.querySelectorAll('.entry-checkbox').forEach(checkbox => {
                // Only affect rows that are not hidden by the filter
                if(checkbox.closest('tr').style.display !== 'none') {
                    checkbox.checked = selectAllCheckbox.checked;
                }
            });
            updateSelectedEntries();
        }

        /**
         * Recalculates all totals based on the current state of checkboxes and inputs.
         * This is called on any interaction (checkbox change, payment input change).
         */
        window.updateSelectedEntries = function() {
            const hiddenInput = document.getElementById('purchase_entry_ids');
            const amountToBePaidSpan = document.getElementById('amount-to-be-paid');
            const submitButton = document.getElementById('submit-payment-btn');

            let totalAllocated = 0;
            const selectedEntries = [];

            // Loop through all checkboxes to see which are checked
            document.querySelectorAll('.entry-checkbox').forEach(checkbox => {
                const paymentInput = checkbox.closest('tr').querySelector('.payment-amount');
                if (checkbox.checked) {
                    const paymentAmount = parseFloat(paymentInput.value) || 0;
                    totalAllocated += paymentAmount;
                    if (paymentAmount > 0) {
                        selectedEntries.push({ id: checkbox.dataset.id, amount: paymentAmount });
                    }
                } else {
                     // If a user unchecks a box, reset its payment amount to zero
                     if (document.activeElement !== paymentInput) {
                        paymentInput.value = '0.00';
                     }
                }
            });

            hiddenInput.value = JSON.stringify(selectedEntries);
            const balance = enteredAmount - totalAllocated;

            amountToBePaidSpan.innerHTML = `Entered: <b class="text-dark">₹${enteredAmount.toFixed(2)}</b> | Allocated: <b class="text-primary">₹${totalAllocated.toFixed(2)}</b> | Balance: <b class="${Math.abs(balance) > 0.01 ? 'text-danger' : 'text-success'}">₹${balance.toFixed(2)}</b>`;
            
            // Enable button if amount is entered AND (it's fully allocated OR there are no entries to allocate against)
            const isFullyAllocated = Math.abs(balance) < 0.01;
            const canPayOnAccount = purchaseEntriesData.length === 0 && enteredAmount > 0;
            submitButton.disabled = !(isFullyAllocated || canPayOnAccount);
        }

        window.exportToExcel = function() {
            // Your existing export logic remains unchanged
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const partySearch = document.getElementById('party_search').value;
            const invoiceSearch = document.getElementById('invoice_search').value;
            const invoiceDateFrom = document.getElementById('invoice_date_from').value;
            const invoiceDateTo = document.getElementById('invoice_date_to').value;
            let exportUrl = `{{ route("payables.export") }}`;
            const params = new URLSearchParams();
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            if (partySearch) params.append('party_search', partySearch);
            if (invoiceSearch) params.append('invoice_search', invoiceSearch);
            if (invoiceDateFrom) params.append('invoice_date_from', invoiceDateFrom);
            if (invoiceDateTo) params.append('invoice_date_to', invoiceDateTo);
            if (!params.toString()) {
                alert('Please provide at least one filter to export.');
                return;
            }
            window.location.href = `${exportUrl}?${params.toString()}`;
        }
    });
</script>
</body>
@include('layout.footer')