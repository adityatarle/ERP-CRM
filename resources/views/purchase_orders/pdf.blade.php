<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Purchase Order - {{ $purchaseOrder->purchase_order_number }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path('fonts/DejaVuSans-Bold.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        @page {
            margin: 120px 50px 150px 50px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            font-size: 11px;
            margin: 0;
        }

        .header,
        .footer {
            position: fixed;
            left: 0px;
            right: 0px;
            width: 100%;
        }

        .header {
            top: -120px;
        }

        .footer {
            bottom: -150px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid grey;
            padding: 5px;
            word-wrap: break-word;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        .header-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            padding-bottom: 5px;
            border-bottom: 2px solid grey;
        }

        .no-border td,
        .no-border th {
            border: none;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .w-50 {
            width: 50%;
        }

        .v-align-top {
            vertical-align: top;
        }

        .items-table {
            border-top: 1px solid grey;
            border-bottom: 1px solid grey;
            border-left: none;
            border-right: none;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            border-top: none;
            border-bottom: none;
            border-left: 1px solid grey;
            border-right: 1px solid grey;
        }

        .items-table thead th {
            border-top: 1px solid grey;
            border-bottom: 1px solid grey;
        }
    </style>
</head>

<body>
    <header class="header">
        <p class="header-title">PURCHASE ORDER</p>
        <table class="no-border" style="width: 100%;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <p style="padding-left: 5px; font-size: 12px; font-weight: bold; margin:0;">{{ $company['name'] }}</p>
                    <p style="padding-left: 5px; margin:0;">{{ $company['address'] }}</p>
                    <p style="padding-left: 5px; margin:0;">Mob: {{ $company['contact'] }}</p>
                    <p style="padding-left: 5px; margin:0;">GSTIN/UIN: {{ $company['gstin'] }}</p>
                    <p style="padding-left: 5px; margin:0;">State Name: {{ $company['state'] }}</p>
                    <p style="padding-left: 5px; margin:0;">E-Mail: {{ $company['email'] }}</p>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: center;">
                    <img src="{{ public_path('assets/img/mauli-logo.jpeg') }}" alt="Logo" style="height: 80px;">
                </td>
            </tr>
        </table>
        <hr style="margin: 5px 0; border: 1px solid #000;">
    </header>

    <footer class="footer">
        <table class="no-border">
            <tr>
                <td class="w-50 v-align-top">
                    <span class="font-bold">Company's PAN:</span> {{ $company['pan'] }}
                </td>
                <td class="w-50 text-right v-align-top">
                    for <span class="font-bold">{{ $company['name'] }}</span>
                    <div style="margin-top: 80px;">Authorised Signatory</div>
                </td>
            </tr>
        </table>
    </footer>

    <!-- FIXED: Push content lower from the header -->
    <main style="margin-top: 90px;">
        <table class="no-border" style="width: 100%; margin-top: 30px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <p style="padding-left: 5px; font-size: 10px; margin:0;">Supplier (Bill From)</p>
                    <p style="padding-left: 5px; font-size: 12px; font-weight: bold; margin:0;">{{ $purchaseOrder->party->name }}</p>
                    <p style="padding-left: 5px; margin:0;">{{ $purchaseOrder->party->address }}</p>
                    <p style="padding-left: 5px; margin:0;">GSTIN/UIN: {{ $purchaseOrder->party->gst_in }}</p>
                    <p style="padding-left: 5px; margin:0;">State Name: {{ $purchaseOrder->party->state ?? 'N/A' }}, Code: {{ $purchaseOrder->party->state_code ?? 'N/A' }}</p>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <table style="width: 100%;">
                        <tr>
                            <td><strong>Purchase Order No.</strong><br>{{ $purchaseOrder->purchase_order_number }}</td>
                            <td><strong>Dated</strong><br>{{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d-M-y') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="items-table" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">Sl No.</th>
                    <th class="text-center" style="width: 35%;">Description of Goods</th>
                    <th class="text-center" style="width: 15%;">Buyer Name</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-center" style="width: 10%;">Rate</th>
                    <th class="text-center" style="width: 5%;">Disc. %</th>
                    <th class="text-center" style="width: 10%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">{{ $item->product->name }}</td>
                    <td>{{ $item->buyer_name }}</td>
                    <td class="text-center">{{ $item->quantity }} NOS</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center">{{ number_format($item->discount ?? 0, 2) }}</td>
                    <td class="text-right">
                        {{ number_format($item->quantity * $item->unit_price * (1 - ($item->discount ?? 0) / 100), 2) }}
                    </td>
                </tr>
                @endforeach

                @for ($i = $purchaseOrder->items->count(); $i < 7; $i++)
                <tr>
                    <td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td>
                </tr>
                @endfor
            </tbody>
        </table>

        <table style="margin-top: 10px;">
            <tr>
                <td colspan="5" rowspan="4" style="border-bottom: none; border-left: none; border-top: none; width: 60%;">
                    <span class="font-bold">Amount Chargeable (in words)</span><br>
                    <p class="font-bold">INR {{ $amountInWords ?? 'Zero' }} Rupees Only</p>
                </td>
                <td class="text-left font-bold" style="width: 20%;">Subtotal</td>
                <td class="text-right font-bold" style="width: 20%;">{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if ($totalCgst > 0 || $totalSgst > 0)
            <tr>
                <td class="text-left">CGST @ {{ $purchaseOrder->items->first()->cgst ?? 0 }}%</td>
                <td class="text-right">{{ number_format($totalCgst, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left">SGST @ {{ $purchaseOrder->items->first()->sgst ?? 0 }}%</td>
                <td class="text-right">{{ number_format($totalSgst, 2) }}</td>
            </tr>
            @endif
            @if ($totalIgst > 0)
            <tr>
                <td class="text-left">IGST @ {{ $purchaseOrder->items->first()->igst ?? 0 }}%</td>
                <td class="text-right">{{ number_format($totalIgst, 2) }}</td>
            </tr>
            @endif
        </table>

        <p style="text-align: right;"> E. & O.E</p>

        <table style="margin-top: 10px;">
            <thead>
                <tr>
                    <th class="text-center">HSN/SAC</th>
                    <th class="text-center">Taxable Value</th>
                    <th class="text-center">CGST</th>
                    <th class="text-center">SGST</th>
                    <th class="text-center">IGST</th>
                    <th class="text-center">Total Tax</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $hsnGroups = $purchaseOrder->items->groupBy('product.hsn');
                @endphp
                @foreach($hsnGroups as $hsn => $items)
                    @php
                        $taxable = $items->sum(fn($i) => $i->quantity * $i->unit_price * (1 - ($i->discount ?? 0) / 100));
                        $cgst = $taxable * ($items->first()->cgst / 100);
                        $sgst = $taxable * ($items->first()->sgst / 100);
                        $igst = $taxable * ($items->first()->igst / 100);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $hsn ?? 'N/A' }}</td>
                        <td class="text-right">{{ number_format($taxable, 2) }}</td>
                        <td class="text-right">{{ number_format($cgst, 2) }}</td>
                        <td class="text-right">{{ number_format($sgst, 2) }}</td>
                        <td class="text-right">{{ number_format($igst, 2) }}</td>
                        <td class="text-right">{{ number_format($cgst + $sgst + $igst, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table style="margin-top: 0px;">
            <tfoot>
                <tr>
                    <td colspan="3" class="font-bold">Total</td>
                    <td class="text-center font-bold" style="width: 10%;">{{ $totalQuantity }} NOS</td>
                    <td colspan="3"></td>
                    <td class="text-right font-bold">₹ {{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </main>
</body>

</html>
