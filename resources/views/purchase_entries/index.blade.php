@include('layout.header')


<div class="container">
        <h1>Purchase Entries</h1>
        <a href="{{ route('purchase_entries.create') }}">Create Purchase Entry</a>
        <a href="{{ route('payments.index') }}" class="btn btn-info">View Payables</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Purchase Number</th>
                    <th>Purchase Date</th>
                    <th>Invoice Number</th>
                    <th>Invoice Date</th>
                    <th>Party</th>
                    <th>Note</th>
                    <th>Total with GST</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseEntries as $entry)
                    <tr>
                        <td>{{ $entry->purchase_number }}</td>
                        <td>{{ $entry->purchase_date }}</td>
                        <td>{{ $entry->invoice_number }}</td>
                        <td>{{ $entry->invoice_date }}</td>
                        <td>{{ $entry->party->name }}</td>
                        <td>{{ $entry->note ?? 'N/A' }}</td>
                        <td>{{ $entry->items->sum('total_price') }}</td>
                        <td>
                            <a href="{{ route('purchase_entries.edit', $entry->id) }}">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@include('layout.footer')