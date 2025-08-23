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

        @page {
            /* Reduced top margin since header is not fixed anymore */
            margin: 25px 25px 150px 25px;
        }

        /* Footer is the only fixed element now */
        footer {
            position: fixed;
            bottom: -100px;
            left: 0px;
            right: 0px;
            height: 130px;
        }

        /* --- STYLES FOR THE ITEMS TABLE (No Changes Needed) --- */
        .items-table {
            border: 1px solid grey;
            border-collapse: collapse;
        }

        .items-table thead th {
            border: 1px solid grey;
            font-weight: bold;
        }

        .items-table td,
        .items-table th {
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            border-left: 1px solid grey;
            border-right: 1px solid grey;
            border-top: none;
            border-bottom: none;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 1px solid grey;
        }

        .items-table .description {
            text-align: left;
        }

        /* General Styles (No Changes Needed) */
        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
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

        .text-uppercase {
            text-transform: uppercase;
        }

        p {
            margin: 3px 0;
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

        .from-text {
            font-size: 9px;
            font-weight: 300;
        }

        .invoice-td {
            font-size: 12px;
        }
    </style>
</head>

<body>
    <!-- Repeating Footer Content (Remains the same) -->
    <footer>
        <p>Company PAN: <b>{{ $company['pan'] }}</b></p>
        <table style="width: 100%; border: none; margin-top:10px;">
            <tr style="border: none;">
                <td style="width: 50%; border: none; vertical-align: top;">
                    <p class="font-bold">Bank Details:</p>
                    <p>A/c Holder Name: <b>{{ $company['account_holder'] }}</b></p>
                    <p>Bank Name: <b>{{ $company['bank_name'] }}</b></p>
                    <p>A/c NO.: <b>{{ $company['account_no'] }}</b></p>
                    <p>Branch & IFS Code: <b>{{ $company['ifsc_code'] }}</b></p>
                </td>
                <td style="width: 50%; text-align: right; border: none; vertical-align: bottom;">
                    <p>For {{ $company['name'] }}</p>
                    <p style="margin-top: 50px;"><b>Authorized Signatory</b></p>
                </td>
            </tr>
        </table>
        <div style="margin-top: 10px;">
            <p class="font-bold">Declaration:</p>
            <p style="font-size:10px;">
                We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
            </p>
        </div>
    </footer>

    <!-- Main Content -->
    <main>
        @php
        // Define how many items fit on the first page and subsequent pages
        $itemsOnFirstPage = 10;
        $itemsOnNextPages = 15; // Adjust this based on your layout
        $totalItems = count($allItems);
        $itemsPrinted = 0;
        @endphp

        <!-- First Page Content -->
        <div>
            <!-- Header for the first page -->
            @include('invoices.partials.header')

            <table class="items-table" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th class="text-uppercase" style="width: 5%;">Sr.<br />no</th>
                        <th class="text-uppercase description" style="width: 40%;">Description of Goods</th>
                        <th class="text-uppercase" style="width: 10%;">HSN/SAC</th>
                        <th class="text-uppercase" style="width: 10%;">Quantity</th>
                        <th class="text-uppercase" style="width: 10%;">Rate</th>
                        <th class="text-uppercase" style="width: 5%;">Disc %</th>
                        <th class="text-uppercase" style="width: 10%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @while($itemsPrinted < $totalItems && $itemsPrinted < $itemsOnFirstPage)
                        @php $item=$allItems[$itemsPrinted]; @endphp
                        <tr>
                        <td>{{ $itemsPrinted + 1 }}</td>
                        <td class="description">
                            <b>{{ $item->product->name }}</b><br>
                            <small>Material Code-{{ $item->itemcode ?? 'N/A' }}</small><br>
                            <small>Line No-{{ $item->secondary_itemcode ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $item->product->hsn }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->discount, 2) }}</td>
                        <td>{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @php $itemsPrinted++; @endphp
                        @endwhile
                        @for ($i = $itemsPrinted; $i < $itemsOnFirstPage; $i++)
                            <tr>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            </tr>
                            @endfor
                </tbody>
            </table>
        </div>

        <!-- Loop for subsequent pages -->
        @while ($itemsPrinted < $totalItems)
            <div style="page-break-before: always;">
            <!-- Header for subsequent pages -->
            @include('invoices.partials.header')

            <table class="items-table" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th class="text-uppercase" style="width: 5%;">Sr.<br />no</th>
                        <th class="text-uppercase description" style="width: 40%;">Description of Goods</th>
                        <th class="text-uppercase" style="width: 10%;">HSN/SAC</th>
                        <th class="text-uppercase" style="width: 10%;">Quantity</th>
                        <th class="text-uppercase" style="width: 10%;">Rate</th>
                        <th class="text-uppercase" style="width: 5%;">Disc %</th>
                        <th class="text-uppercase" style="width: 10%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php $itemsOnThisPage = 0; @endphp
                    @while($itemsPrinted < $totalItems && $itemsOnThisPage < $itemsOnNextPages)
                        @php $item=$allItems[$itemsPrinted]; @endphp
                        <tr>
                        <td>{{ $itemsPrinted + 1 }}</td>
                        <td class="description">
                            <b>{{ $item->product->name }}</b><br>
                            <small>Material Code-{{ $item->itemcode ?? 'N/A' }}</small><br>
                            <small>Line No-{{ $item->secondary_itemcode ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $item->product->hsn }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->discount, 2) }}</td>
                        <td>{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @php $itemsPrinted++; $itemsOnThisPage++; @endphp
                        @endwhile
                        @for ($i = $itemsOnThisPage; $i < $itemsOnNextPages; $i++)
                            <tr>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            <td> </td>
                            </tr>
                            @endfor
                </tbody>
            </table>
            </div>
            @endwhile

            <!-- Grand Total and HSN Summary Tables -->
            <div style="page-break-inside: avoid;">

                {{-- FIX #1: Added class="items-table" to the summary table --}}
                <table class="items-table" style="margin-top: 10px;">
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-end font-bold">Subtotal</td>
                            <td class="text-end font-bold">{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if ($invoice->gst_type === 'IGST')
                        <tr>
                            <td colspan="7" class="text-end">Add: IGST @ {{ $invoice->igst }}%</td>
                            <td class="text-end">{{ number_format($invoice->tax, 2) }}</td>
                        </tr>
                        @else
                        <tr>
                            <td colspan="7" class="text-end">Add: CGST @ {{ $invoice->cgst }}%</td>
                            <td class="text-end">{{ number_format($invoice->subtotal * ($invoice->cgst / 100), 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="7" class="text-end">Add: SGST @ {{ $invoice->sgst }}%</td>
                            <td class="text-end">{{ number_format($invoice->subtotal * ($invoice->sgst / 100), 2) }}</td>
                        </tr>
                        @endif
                        <tr class="font-bold">
                            <th colspan="4" class="text-uppercase text-end">Grand Total</th>
                            <th class="text-center">{{ $totalQuantity }} NOS</th>
                            <th colspan="2"></th>
                            <th class="text-end">{{ number_format($invoice->total, 2) }}/-</th>
                        </tr>
                        <tr>
                            <td colspan="8">Amount (In Words): <b>INR {{ $amount_in_words ?? '' }} Rupees Only</b></td>
                        </tr>
                    </tbody>
                </table>

                @if(!empty($hsnSummary))
                {{-- FIX #2: Added class="items-table" to the HSN table --}}
                <table class="items-table" style="margin-top: 15px;">
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
                            <td class="text-end">{{ number_format($tax = $summary['taxable_value'] * ($summary['igst_rate'] / 100), 2) }}</td>
                            <td class="text-end">{{ number_format($tax, 2) }}</td>
                            @else
                            <td class="text-center">{{ number_format($summary['cgst_rate'], 2) }}%</td>
                            <td class="text-end">{{ number_format($cgst = $summary['taxable_value'] * ($summary['cgst_rate'] / 100), 2) }}</td>
                            <td class="text-center">{{ number_format($summary['sgst_rate'], 2) }}%</td>
                            <td class="text-end">{{ number_format($sgst = $summary['taxable_value'] * ($summary['sgst_rate'] / 100), 2) }}</td>
                            <td class="text-end">{{ number_format($cgst + $sgst, 2) }}</td>
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
                <p style="margin-top: 10px;">Tax amount (in words): <b>{{ $tax_amount_in_words ?? '' }} Rupees Only</b></p>
            </div>
    </main>

</body>

</html>