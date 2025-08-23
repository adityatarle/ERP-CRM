{{-- This is the header content partial --}}
<div style="position: relative;">
    <div style="position: absolute; top: 0; right: 5px; font-weight: bold; border: 1px solid black; padding: 3px;">
        {{ $label }}
    </div>
</div>

<p class="text-center fw-bold" style="font-size: 26px;">Tax Invoice</p>
<div class="details-box">
    <div class="details-box-left" >
        <address class="from-text">
            <strong>{{ $company['name'] }}</strong><br>
            {{ $company['address'] }}<br>
            GSTIN/UIN: {{ $company['gstin'] }} | State: {{ $company['state'] }}<br>
            Phone: {{ $company['contact'] }} | Email: {{ $company['email'] }}<br>
            UDYAM: {{ $company['udyam'] }}
        </address>
    </div>
    <div class="details-box-right" style="vertical-align: top; text-align: right;">
        <img src="{{ public_path('assets/img/mauli-logo.jpeg') }}" alt="Logo" style="height: 70px;">
    </div>
</div>
<hr>
<div class="details-box">
    <div class="details-box-left ">
        <p><b>Bill To</b></p>
        <address class="from-text">
            <strong>{{ $invoice->customer->name }}</strong><br>
            {{ $invoice->customer->address }}<br>
            GST Number: {{ $invoice->customer->gst_number }}<br>
            Phone: {{ $invoice->customer->phone }}
        </address>
    </div>
    <div class="details-box-right from-text" style="vertical-align: middle;">
    <table class="no-border">
        <tr><td><strong>Invoice No:</strong></td><td class="invoice-td">{{ $invoice->invoice_number }}</td></tr>
        <tr><td><strong>Date:</strong></td><td class="invoice-td">{{ $invoice->created_at->format('d-m-Y') }}</td></tr>

        {{-- ADD THIS LINE --}}
        <tr><td><strong>Reference No:</strong></td><td class="invoice-td">{{ optional($invoice->sales->first())->ref_no }}</td></tr>
        
        <tr><td><strong>Buyer Order No:</strong></td><td class="invoice-td">{{ $invoice->purchase_number }}</td></tr>
        <tr><td><strong>Purchase Date:</strong></td><td class="invoice-td">{{ $invoice->purchase_date }}</td></tr>
    </table>
</div>
</div>