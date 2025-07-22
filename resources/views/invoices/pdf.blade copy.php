<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path(' fonts/DejaVuSans.ttf') }}') format('truetype');
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 9px;
            color: #000;
        }

        .invoice-wrapper {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 600px;
            /* A4 height approx for DomPDF */
            padding: 10px;
            border: 2px solid grey;
        }

        h2,
        h4 {
            text-transform: uppercase;
            text-align: center;
            margin: 5px 0;
        }

        h2 {
            font-size: 16px;
            border-bottom: 1px solid grey;
            padding-bottom: 5px;
        }

        h4 {
            font-size: 13px;
        }

        address {
            font-style: normal;
            line-height: 1.3;
        }

        hr {
            margin: 5px 0;
            border: 0;
            border-top: 1px solid grey;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 0.5px solid grey;
            padding: 2px;
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

        p {
            margin: 3px 0;
        }

        .footer-section td {
            border: none;
            vertical-align: top;
        }

        .from-text {
            font-size: 9px;
            font-weight: 300;
        }

        .invoice-body {
            flex-grow: 1;
        }

        .invoice-footer {
            margin-top: auto;
        }

        .items-table-wrapper {
            min-height: 475px;
            /* Adjust as needed */
        }


        /* Apply to the items/product table */
        .items-table {
            border-collapse: collapse;
            width: 100%;
            border: 0.5px solid grey;
            /* outer box */
        }

        .items-table th,
        .items-table td {
            border-left: 0.5px solid grey;
            border-right: 0.5px solid grey;
            /* remove row separators */
            border-top: none;
            border-bottom: none;
            padding: 4px;
        }

        /* Top border only on the header */
        .items-table thead th {
            border-top: 0.5px solid grey;
            border-bottom: 0.5px solid grey;
            font-weight: bold;
        }

        /* Optional: Add bottom border on the LAST row only */
        .items-table tbody tr:last-child td {
            border-bottom: 0.5px solid grey;
        }
    </style>
</head>

<body>
    <div class="invoice-wrapper">
        <div class="invoice-body">
            <!-- HEADER SECTION -->
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


            <!-- PRODUCT TABLE SECTION -->
            <div class="items-table-wrapper">
                <table class="items-table" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th class="text-uppercase">Sr.<br />no</th>
                            <th class="text-uppercase" style="width: 40%;">Product Name</th>
                            <th class="text-uppercase text-end">HSN/SAC</th>
                            <th class="text-uppercase text-end">Qty</th>
                            <th class="text-uppercase text-end">Rate</th>
                            {{-- <th class="text-uppercase text-center">Per</th> --}} <!-- REMOVED Per Column Header -->
                            <th class="text-uppercase text-end">Disc %</th>
                            <th class="text-uppercase text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $allItems = $invoice->sales->flatMap->saleItems;
                        $totalQuantity = $allItems->sum('quantity');
                        $minRows = 10;
                        @endphp

                        @foreach($allItems as $index => $saleItem)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-start">
                                <b>{{ $saleItem->product->name }}</b>
                                @if($saleItem->itemcode)<br /><small>{{ $saleItem->itemcode }}</small>@endif
                                @if($saleItem->secondary_itemcode)<small>{{ $saleItem->secondary_itemcode }}</small>@endif
                            </td>
                            <td class="text-end">{{ $saleItem->product->hsn }}</td>
                            <td class="text-end">{{ $saleItem->quantity }} NOS</td>
                            <td class="text-end">{{ number_format($saleItem->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($saleItem->discount, 2) }}</td>
                            <td class="text-end">{{ number_format($saleItem->total_price, 2) }}</td>
                        </tr>
                        @endforeach

                        {{-- Fill to 10 rows --}}
                        @for ($i = $allItems->count(); $i < $minRows; $i++)
                            <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            </tr>
                            @endfor


                            {{-- Spacer row before subtotal --}}
                            <tr>
                                <!-- CHANGED colspan from 8 to 7 -->
                                <td colspan="7" style="height: 15px; border: none;"></td>
                            </tr>

                            <tr>
                                <!-- CHANGED colspan from 7 to 6 -->
                                <td colspan="6" class="text-end font-bold">Subtotal</td>
                                <td class="text-end font-bold">{{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>

                            @if ($invoice->gst_type === 'IGST')
                            <tr>
                                <!-- CHANGED colspan from 7 to 6 -->
                                <td colspan="6" class="text-end">Add: IGST @ {{ $invoice->igst }}%</td>
                                <td class="text-end">{{ number_format($invoice->tax, 2) }}</td>
                            </tr>
                            @else
                            <tr>
                                <!-- CHANGED colspan from 7 to 6 -->
                                <td colspan="6" class="text-end">Add: CGST @ {{ $invoice->cgst }}%</td>
                                <td class="text-end">{{ number_format($invoice->subtotal * ($invoice->cgst / 100), 2) }}</td>
                            </tr>
                            <tr>
                                <!-- CHANGED colspan from 7 to 6 -->
                                <td colspan="6" class="text-end">Add: SGST @ {{ $invoice->sgst }}%</td>
                                <td class="text-end">{{ number_format($invoice->subtotal * ($invoice->sgst / 100), 2) }}</td>
                            </tr>
                            @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-uppercase text-end">Total</th>
                            <th class="text-center">{{ $totalQuantity }} NOS</th>
                            <!-- CHANGED colspan from 3 to 2 -->
                            <th colspan="2"></th>
                            <th class="text-end">{{ number_format($invoice->total, 2) }}/-</th>
                        </tr>
                        <tr>
                            <!-- CHANGED colspan from 7 to 6 -->
                            <td colspan="6">Amount (In Words): <b>INR {{ $amount_in_words ?? '' }} Rupees Only</b></td>
                            <td class="text-end">E. & OE.</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- HSN SUMMARY SECTION -->
            @php
            $hsnSummary = [];
            foreach ($invoice->sales->flatMap->saleItems as $item) {
            $hsn = $item->product->hsn;
            if (!isset($hsnSummary[$hsn])) {
            $hsnSummary[$hsn] = [
            'taxable_value' => 0,
            'cgst_rate' => $invoice->cgst,
            'sgst_rate' => $invoice->sgst,
            'igst_rate' => $invoice->igst
            ];
            }
            $hsnSummary[$hsn]['taxable_value'] += $item->total_price;
            }
            @endphp

            @if(!empty($hsnSummary))
            <table class="table" style="margin-top: 5px;">
                <thead>
                    @if ($invoice->gst_type === 'IGST')
                    <tr>
                        <th rowspan="2">HSN/SAC</th>
                        <th rowspan="2" class="text-end">Taxable Value</th>
                        <th colspan="2" class="text-center">IGST</th>
                        <th rowspan="2" class="text-end">Total Tax Amount</th>
                    </tr>
                    <tr>
                        <th class="text-center">Rate</th>
                        <th class="text-end">Amount</th>
                    </tr>
                    @else
                    <tr>
                        <th rowspan="2">HSN/SAC</th>
                        <th rowspan="2" class="text-end">Taxable Value</th>
                        <th colspan="2" class="text-center">CGST</th>
                        <th colspan="2" class="text-center">SGST</th>
                        <th rowspan="2" class="text-end">Total Tax Amount</th>
                    </tr>
                    <tr>
                        <th class="text-center">Rate</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Rate</th>
                        <th class="text-end">Amount</th>
                    </tr>
                    @endif
                </thead>
                <tbody>
                    @foreach($hsnSummary as $hsn => $summary)
                    <tr>
                        <td>{{ $hsn }}</td>
                        <td class="text-end">{{ number_format($summary['taxable_value'], 2) }}</td>

                        @if ($invoice->gst_type === 'IGST')
                        <td class="text-center">{{ number_format($summary['igst_rate'], 2) }}%</td>
                        <td class="text-end">{{ number_format($summary['taxable_value'] * ($summary['igst_rate'] / 100), 2) }}</td>
                        <td class="text-end">{{ number_format($summary['taxable_value'] * ($summary['igst_rate'] / 100), 2) }}</td>
                        @else
                        <td class="text-center">{{ number_format($summary['cgst_rate'], 2) }}%</td>
                        <td class="text-end">{{ number_format($summary['taxable_value'] * ($summary['cgst_rate'] / 100), 2) }}</td>
                        <td class="text-center">{{ number_format($summary['sgst_rate'], 2) }}%</td>
                        <td class="text-end">{{ number_format($summary['taxable_value'] * ($summary['sgst_rate'] / 100), 2) }}</td>
                        <td class="text-end">{{ number_format($summary['taxable_value'] * (($summary['cgst_rate'] + $summary['sgst_rate']) / 100), 2) }}</td>
                        @endif
                    </tr>
                    @endforeach

                    <tr class="font-bold">
                        <td>Total</td>
                        <td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td>

                        @if ($invoice->gst_type === 'IGST')
                        <td></td>
                        <td class="text-end">{{ number_format($invoice->tax, 2) }}</td>
                        <td class="text-end">{{ number_format($invoice->tax, 2) }}</td>
                        @else
                        <td></td>
                        <td class="text-end">{{ number_format($invoice->subtotal * ($invoice->cgst / 100), 2) }}</td>
                        <td></td>
                        <td class="text-end">{{ number_format($invoice->subtotal * ($invoice->sgst / 100), 2) }}</td>
                        <td class="text-end">{{ number_format($invoice->tax, 2) }}</td>
                        @endif
                    </tr>
                </tbody>
            </table>
            @endif

        </div>

        <!-- FOOTER SECTION -->
        <div class="invoice-footer">
            <p style="margin-top: 10px;">Tax amount (in words): <b>{{ $tax_amount_in_words ?? '' }} Rupees Only</b></p>
            <p>Company PAN: <b>{{ $company['pan'] ?? '' }}</b></p>

            <!-- Footer Layout -->
            <table class="footer-section" style="margin-top:10px;">
                <tr>
                    <td style="width: 50%;">
                        {{-- Bank Details Section --}}
                        <p class="font-bold">Bank Details:</p>
                        <p>A/c Holder Name: <b>{{ $company['account_holder'] ?? '' }}</b></p>
                        <p>Bank Name: <b>{{ $company['bank_name'] ?? '' }}</b></p>
                        <p>A/c NO.: <b>{{ $company['account_no'] ?? '' }}</b></p>
                        <p>Branch & IFS Code: <b>{{ $company['ifsc_code'] ?? '' }}</b></p>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        {{-- Signature Section --}}
                        <div style="margin-top: 20px;">
                            <p>For {{ $company['name'] ?? 'Mauli Solutions' }}</p>
                            <p style="margin-top: 50px;"><b>Authorized Signatory</b></p>
                        </div>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 10px;">
                <p class="font-bold">Declaration:</p>
                <p style="font-size:10px;">
                    We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
                </p>
            </div>

        </div>
    </div>
</body>

</html>