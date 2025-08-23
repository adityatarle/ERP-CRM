<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Delivery Note - {{ $deliveryNote->delivery_note_number }}</title>
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
        <div class="header-title">Delivery Note</div>
        <table class="no-border" style="margin-top: 5px; width: 100%;">
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

    <main style="margin-top: 50px;">
        <table class="no-border" style="width: 100%;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <p style="padding-left: 5px; font-size: 10px; margin:0;">Buyer (Bill To)</p>
                    <p style="padding-left: 5px; font-size: 12px; font-weight: bold; margin:0;">{{ $deliveryNote->customer->name }}</p>
                    <p style="padding-left: 5px; margin:0;">{{ $deliveryNote->customer->address }}</p>
                    <p style="padding-left: 5px; margin:0;">GSTIN/UIN: {{ $deliveryNote->customer->gst_number }}</p>
                    <p style="padding-left: 5px; margin:0;">State Name: Maharashtra, Code: 27</p>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <table style="width: 100%;">
                        <tr>
                            <td><strong>Delivery Note No.</strong><br>{{ $deliveryNote->delivery_note_number }}</td>
                            <td><strong>Dated</strong><br>{{ \Carbon\Carbon::parse($deliveryNote->delivery_date)->format('d-M-y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Reference No.</strong><br>{{ $deliveryNote->ref_no ?? 'N/A' }}</td>
                            <td><strong>Contact Person</strong><br>{{ $deliveryNote->contact_person }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="items-table" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">Sl No.</th>
                    <th class="text-center" style="width: 40%;">Description of Goods</th>
                    <th class="text-center" style="width: 10%;">HSN/SAC</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-center" style="width: 10%;">Rate</th>
                    <th class="text-center" style="width: 5%;">Disc. %</th>
                    <th class="text-center" style="width: 10%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryNote->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">
                        {{ $item->product->name }}
                        <div style="font-size: 10px; color: #777;">
                            {{ implode(' | ', array_filter([$item->itemcode, $item->secondary_itemcode])) }}
                        </div>
                    </td>
                    <td class="text-center">{{ $item->product->hsn ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->quantity }} NOS</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-center">{{ number_format($item->discount ?? 0, 2) }}</td>
                    <td class="text-right">
                        {{ number_format($item->quantity * $item->price * (1 - ($item->discount ?? 0) / 100), 2) }}
                    </td>
                </tr>
                @endforeach

                @for ($i = $deliveryNote->items->count(); $i < 7; $i++)
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
                    <p class="font-bold">INR {{ $amountInWords ?? 'Zero' }}</p>
                </td>
                <td class="text-left font-bold" style="width: 20%;">Subtotal</td>
                <td class="text-right font-bold" style="width: 20%;">{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if ($totalCgst > 0 || $totalSgst > 0)
            <tr>
                <td class="text-left">CGST @ {{ $deliveryNote->cgst ?? 9 }}%</td>
                <td class="text-right">{{ number_format($totalCgst, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left">SGST @ {{ $deliveryNote->sgst ?? 9 }}%</td>
                <td class="text-right">{{ number_format($totalSgst, 2) }}</td>
            </tr>
            @endif
            @if ($totalIgst > 0)
            <tr>
                <td class="text-left">IGST @ {{ $deliveryNote->igst ?? 18 }}%</td>
                <td class="text-right">{{ number_format($totalIgst, 2) }}</td>
            </tr>
            @endif
        </table>

        <p style="text-align: right;"> E. & O.E</p>

        <table style="margin-top: 10px;">
            <thead>
                <tr>
                    <th class="text-center">HSN/SAC</th>
                    <th class="text-center">Description</th>
                    <th class="text-center">Total Qty</th>
                    <th class="text-center">Taxable Value</th>
                    <th class="text-center">CGST</th>
                    <th class="text-center">SGST</th>
                    <th class="text-center">IGST</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $hsnGroups = $deliveryNote->items->groupBy('product.hsn');
                @endphp
                @foreach($hsnGroups as $hsn => $items)
                    @php
                        $qty = $items->sum('quantity');
                        $taxable = $items->sum(fn($i) => $i->quantity * $i->price * (1 - ($i->discount ?? 0) / 100));
                        $cgst = $items->sum('cgst_amount');
                        $sgst = $items->sum('sgst_amount');
                        $igst = $items->sum('igst_amount');
                    @endphp
                    <tr>
                        <td class="text-center">{{ $hsn ?? 'N/A' }}</td>
                        <td class="text-center">{{ $items->first()->product->name }}</td>
                        <td class="text-center">{{ $qty }} NOS</td>
                        <td class="text-right">{{ number_format($taxable, 2) }}</td>
                        <td class="text-right">{{ number_format($cgst, 2) }}</td>
                        <td class="text-right">{{ number_format($sgst, 2) }}</td>
                        <td class="text-right">{{ number_format($igst, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table style="margin-top: 0px;">
            <tfoot>
                <tr>
                    <td colspan="2" class="font-bold">Total</td>
                    <td class="text-center font-bold" style="width: 10%;">{{ $totalQuantity }} NOS</td>
                    <td colspan="4"></td>
                    <td class="text-right font-bold">₹ {{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </main>
</body>

</html>
