<div class="row mb-4">
    <div class="col-6">
        <strong>From:</strong><br>
        {{ $company['name'] }}<br>
        {{ $company['address'] }}<br>
        GSTIN: {{ $company['gstin'] }}<br>
        State: {{ $company['state'] }}<br>
        Contact: {{ $company['contact'] }}
    </div>
    <div class="col-6 text-end">
        <strong>To:</strong><br>
        {{ $invoice->customer->name }}<br>
        {{ $invoice->customer->address }}<br>
        GSTIN: {{ $invoice->customer->gstin ?? 'N/A' }}<br>
        State: {{ $invoice->customer->state ?? 'N/A' }}
    </div>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Description of Goods</th>
            <th>HSN</th>
            <th>Qty</th>
            <th>Rate</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
        @foreach($invoice->sales as $sale)
            @foreach($sale->saleItems as $item)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->product->hsn ?? '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->rate, 2) }}</td>
                    <td>{{ number_format($item->quantity * $item->rate, 2) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>HSN</th>
            <th>Taxable Value</th>
            <th>CGST</th>
            <th>SGST</th>
            <th>Total GST</th>
        </tr>
    </thead>
    <tbody>
        @php
            $taxable = $invoice->subtotal;
            $cgst = $invoice->cgst ?? 0;
            $sgst = $invoice->sgst ?? 0;
            $gstAmount = $invoice->tax;
        @endphp
        <tr>
            <td>---</td>
            <td>{{ number_format($taxable, 2) }}</td>
            <td>{{ $cgst }}%</td>
            <td>{{ $sgst }}%</td>
            <td>{{ number_format($gstAmount, 2) }}</td>
        </tr>
    </tbody>
</table>

