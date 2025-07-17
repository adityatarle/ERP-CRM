<h4 class="text-center">Tax Invoice</h4>
<div class="details-box">
    <div class="details-box-left">
        <address class="from-text">
            <strong>{{ $invoice->company->name ?? 'Mauli Solutions' }}</strong><br>
            {{ $invoice->company->address ?? 'Gate No 627 Pune Nashik Highway, in front of Gabriel Vitthal Muktai Complex, Kuruli, Chakan' }},
            {{ $invoice->company->city ?? 'Pune' }},
            {{ $invoice->company->state ?? 'Maharashtra' }},
            {{ $invoice->company->zip ?? '410501' }},
            {{ $invoice->company->country ?? 'India' }}<br>
            GSTIN/UIN: {{ $invoice->company->gst ?? '27ABIFM9220D1ZC' }}<br>
            Phone: {{ $invoice->company->phone ?? '9356911784' }}<br>
            Email: {{ $invoice->company->email ?? 'shubham.bhangale@maulisolutions.com' }}
        </address>
        <hr>
        <p><b>Bill To</b></p>
        <address class="from-text">
            <strong>{{ $invoice->customer->name }}</strong><br>
            {{ $invoice->customer->address }}<br>
            GST Number: {{ $invoice->customer->gst_number }}<br>
            Phone: {{ $invoice->customer->phone }}
        </address>
    </div>
    <div class="details-box-right from-text">
        <table class="no-border">
            <tr>
                <td><strong>Invoice No:</strong></td>
                <td>{{ $invoice->invoice_number }}</td>
            </tr>
            <tr>
                <td><strong>Date:</strong></td>
                <td>{{ $invoice->created_at->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td><strong>Buyer Order No:</strong></td>
                <td>{{ $invoice->purchase_number }}</td>
            </tr>
            <tr>
                <td><strong>Purchase Date:</strong></td>
                <td>{{ $invoice->purchase_date }}</td>
            </tr>
        </table>
    </div>
</div>
