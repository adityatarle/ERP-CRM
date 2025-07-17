<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url("{{ storage_path('fonts/DejaVuSans.ttf') }}") format('truetype');
        }


        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 9px;
            color: #000;
        }

        .page-wrapper {
            border: 2px solid grey;
            padding: 10px;
        }

        h4 {
            text-align: center;
            margin: 5px 0 10px 0;
            font-size: 13px;
        }

        address {
            font-style: normal;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 0.5px solid grey;
            padding: 2px;
            word-wrap: break-word;
            vertical-align: top;
        }

        .details-box {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .details-box-left,
        .details-box-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 5px;
        }

        .no-border td,
        .no-border th {
            border: none;
            padding: 2px 5px;
        }

        .text-end {
            text-align: right;
        }

        .text-start {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .from-text {
            font-size: 9px;
            font-weight: 300;
        }

        @page {
            size: A4;
            margin: 15mm;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="page-wrapper" style="min-height: 1050px; position: relative;">
        <h4 class="text-center" style="margin-top: 5px;">Tax Invoice</h4>

        <div class="details-box">
            <div class="details-box-left">
                <address class="from-text">
                    <strong>{{ $invoice->company->name ?? 'Mauli Solutions' }}</strong><br>
                    {{ $company['address'] }}<br>
                    GSTIN/UIN: {{ $company['gstin'] }}<br>
                    Phone: {{ $company['contact'] }}<br>
                    Email: {{ $company['email'] }}
                </address>

                <br>
                <p><b>Buyer (Bill To):</b></p>
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
                        <td><b>Invoice No:</b></td>
                        <td>{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td><b>Date:</b></td>
                        <td>{{ $invoice->created_at->format('d-M-y') }}</td>
                    </tr>
                    <tr>
                        <td><b>Buyer's Order No:</b></td>
                        <td>{{ $invoice->purchase_number }}</td>
                    </tr>
                    <tr>
                        <td><b>Purchase Date:</b></td>
                        <td>{{ $invoice->purchase_date }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div style="margin-top: 10px;">
            <table style="width:100%; border-collapse: collapse; font-size: 9px;">
                <thead>
                    <tr style="border-bottom: 1px solid #000;">
                        <th style="width: 5%;">No.</th>
                        <th style="width: 40%;">Description of Goods</th>
                        <th style="width: 12%;">HSN/SAC</th>
                        <th style="width: 10%;">Qty</th>
                        <th style="width: 12%;">Rate</th>
                        <th style="width: 8%;">Disc%</th>
                        <th style="width: 13%;" class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $allItems = $invoice->sales->flatMap->saleItems;
                    $totalQuantity = 0;
                    @endphp
                    @foreach($allItems as $index => $item)
                    @php $totalQuantity += $item->quantity; @endphp
                    <tr style="height: 25px;">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->name }}<br><small>{{ $item->itemcode }}</small></td>
                        <td>{{ $item->product->hsn }}</td>
                        <td>{{ $item->quantity }} NOS</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->discount, 2) }}</td>
                        <td class="text-end">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                    {{-- Empty rows to make total 10 --}}
                    @for ($i = $allItems->count(); $i < 10; $i++)
                        <tr style="height: 25px;">
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        </tr>
                        @endfor
                        <tr>
                            <td colspan="3" style="border: none;"></td>
                            <td><b>Total:</b><br>{{ $totalQuantity }} NOS</td>
                            <td colspan="2" class="text-end"><b>Subtotal</b></td>
                            <td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->gst_type == 'IGST')
                        <tr>
                            <td colspan="6" class="text-end">IGST @{{ $invoice->igst }}%</td>
                            <td class="text-end">{{ number_format($invoice->tax, 2) }}</td>
                        </tr>
                        @else
                        <tr>
                            <td colspan="6" class="text-end">CGST @{{ $invoice->cgst }}%</td>
                            <td class="text-end">{{ number_format($invoice->subtotal * ($invoice->cgst / 100), 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end">SGST @{{ $invoice->sgst }}%</td>
                            <td class="text-end">{{ number_format($invoice->subtotal * ($invoice->sgst / 100), 2) }}</td>
                        </tr>
                        @endif
                        <tr style="border-top: 1px solid black;">
                            <td colspan="6" class="text-end"><b>Grand Total</b></td>
                            <td class="text-end"><b>{{ number_format($invoice->total, 2) }}</b></td>
                        </tr>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 5px;">
            <p><b>Amount Chargeable (in words):</b> INR {{ $amount_in_words }} Only</p>
        </div>

        <div style="margin-top: 15px;">
            <table style="width: 100%; font-size: 9px;">
                <thead>
                    <tr>
                        <th>HSN/SAC</th>
                        <th>Taxable Value</th>
                        <th>CGST</th>
                        <th>SGST</th>
                        <th>Total Tax</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $hsn = $item->product->hsn;
                    $taxable = $invoice->subtotal;
                    $cgstAmount = $taxable * ($invoice->cgst / 100);
                    $sgstAmount = $taxable * ($invoice->sgst / 100);
                    @endphp
                    <tr>
                        <td>{{ $hsn }}</td>
                        <td>{{ number_format($taxable, 2) }}</td>
                        <td>{{ number_format($cgstAmount, 2) }}</td>
                        <td>{{ number_format($sgstAmount, 2) }}</td>
                        <td>{{ number_format($invoice->tax, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 5px;">
            <p><b>Tax Amount (in words):</b> {{ $tax_amount_in_words }}</p>
            <p><b>Company's PAN:</b> {{ $company['pan'] ?? '' }}</p>
        </div>

        <div style="position: absolute; bottom: 30px; width: 100%;">
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 50%;">
                    <p><b>Declaration:</b></p>
                    <p>We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</p>
                </div>
                <div style="width: 45%; text-align: right;">
                    <p>For {{ $company['name'] ?? 'Mauli Solutions' }}</p>
                    <br><br>
                    <p><b>Authorized Signatory</b></p>
                </div>
            </div>
        </div>
    </div>
</body>






</html>