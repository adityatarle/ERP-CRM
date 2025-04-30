@include('layout.header')


<div class="container">
    <h1>Purchase Orders</h1>
    <a href="{{ route('purchase_orders.create') }}">Create Purchase Order</a>
    <table class="table">
        <thead>
            <tr>
                <th>PO Number</th>
                <th>Party</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrders as $po)
            <tr>
                <td>{{ $po->purchase_order_number }}</td>
                <td>{{ $po->party->name }}</td>
                <td>{{ $po->order_date }}</td>
                <td>{{ $po->status }}</td>
                <td>
                    @if($po->status == 'pending' && auth()->user()->role == 'superadmin')
                    <form action="{{ route('purchase_orders.approve', $po->id) }}" method="POST">
                        @csrf
                        @method('POST')
                        <button type="submit">Approve</button>
                    </form>
                    @endif
                </td>
                <td>
                    <a href="{{ route('purchase_orders.download_pdf', $po->id) }}" class="btn btn-primary btn-sm">Download PDF</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>



@include('layout.footer')