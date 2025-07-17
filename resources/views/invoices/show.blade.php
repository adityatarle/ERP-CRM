@include('layout.header')

<body class="act-invoice">
    <div class="main-content-area">
        <div class="container p-3 mx-auto">
            <div class="card shadow-sm w-100">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h1 class="mb-0">Invoice {{ $invoice->invoice_number }}</h1>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Customer Details</h5>
                            <p><strong>Name:</strong> {{ $invoice->customer->name }}</p>
                            <p><strong>Email:</strong> {{ $invoice->customer->email }}</p>
                            <p><strong>Phone:</strong> {{ $invoice->customer->phone }}</p>
                            <p><strong>Address:</strong> {{ $invoice->customer->address }}</p>
                            <p><strong>GST Number:</strong> {{ $invoice->customer->gst_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Invoice Details</h5>
                            <p><strong>Date:</strong> {{ $invoice->created_at->toDateString() }}</p>
                            <p><strong>Puchase Number:</strong> {{ $invoice->purchase_number }}</p>
                            <p><strong>Puchase Date:</strong> {{ $invoice->purchase_date }}</p>
                            <p><strong>Status:</strong> {{ $invoice->status }}</p>
                        </div>
                    </div>
                    <h5 class="mb-3">Order Summary</h5>
                    <table class="table table-hover mb-5">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Discount</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->sales as $sale)
                                @foreach ($sale->saleItems as $saleItem)
                                    <tr>
                                        <td>{{ $saleItem->product->name }}</td>
                                        <td>{{ $saleItem->quantity }}</td>
                                        <td>${{ number_format($saleItem->unit_price, 2) }}</td>
                                        <td>{{ $saleItem->discount }}%</td>
                                        <td>${{ number_format($saleItem->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @if ($invoice->sales->isEmpty() || $invoice->sales->flatMap->saleItems->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center">No products found for this invoice.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    <div class="text-right">
                        <p class="mt-5"><strong>Subtotal:</strong> {{ $sale->total_price }}</p>
                        <p><strong>GST:</strong> {{ $invoice->tax }}</p>
                        <p><strong>Total:</strong> {{ $invoice->total }}</p>
                    </div>
                    <div class="text-center">
                                    @if (in_array($invoice->status, ['approved', 'paid']))
                        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-success mt-4">Download PDF</a>
                        @elseif (in_array($invoice->status, ['pending', 'on_hold']))
                                        <button type="button" class="btn btn-sm btn-secondary showRestrictedPdfAlert">
                                            <i class="fa fa-file-pdf"></i> Download PDF
                                        </button>
                                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
@include('layout.footer')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('DOM loaded, found buttons:', document.querySelectorAll('.showRestrictedPdfAlert').length);
        document.querySelectorAll('.showRestrictedPdfAlert').forEach(button => {
            button.addEventListener('click', function () {
                console.log('Showing alert');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Invoice must be approved before downloading.',
                    confirmButtonText: 'OK'
                });
            });
        });
    });
</script>