@include('layout.header')

<div class="container">
    <h1>Payables</h1>
    <a href="{{ route('payments.create') }}" class="btn btn-primary" data-toggle="modal" data-target="#paymentModal">Record Payment</a>
    <a href="{{ route('payments.list') }}" class="btn btn-secondary">View Payment History</a>
    <table class="table">
        <thead>
            <tr>
                <th>Purchase Number</th>
                <th>Party</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payables as $payable)
            <tr>
                <td>{{ $payable->purchaseEntry->purchase_number }}</td>
                <td>{{ $payable->party->name }}</td>
                <td>{{ $payable->amount }}</td>
                <td>{{ $payable->is_paid ? 'Paid' : 'Pending' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Record Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('payments.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Purchase Entry</label>
                        <select name="purchase_entry_id" class="form-control" required>
                            @foreach($unpaidPurchaseEntries as $entry)
                            <option value="{{ $entry->id }}">{{ $entry->purchase_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Party</label>
                        <select name="party_id" class="form-control" required>
                            @foreach($parties as $party)
                            <option value="{{ $party->id }}">{{ $party->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Payment</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

@include('layout.footer')