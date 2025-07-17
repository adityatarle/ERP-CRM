<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Purchase Order - {{ $purchaseOrder->purchase_order_number }}</title>
    <style>
        /* This CSS is identical to your working Receipt Note PDF */
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path(' fonts/DejaVuSans.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path(' fonts/DejaVuSans-Bold.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }


        @page {
            margin: 220px 50px 150px 50px;
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
            top: -180px;
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
            border-bottom: 2px solid #000;
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
    </style>
</head>

<body>

    <!-- ================== FIXED HEADER ================== -->
    <header class="header">
        <div class="header-title">PURCHASE ORDER</div>
        <table class="no-border" style="margin-top: 5px;">
            <tr>
                <td class="w-50 v-align-top">
                    <div class="w-100" style="border: 1px solid grey;">
                        <h3 style="border: none; padding-left: 5px; font-size: 12px; margin:0 !important" class="font-bold">{{ $company['name'] }}</h3>
                        <p style="border: none; padding-left: 5px;margin:0 !important;">{{ $company['address'] }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">Mob: {{ $company['contact'] }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">GSTIN/UIN: {{ $company['gstin'] }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">State Name: {{ $company['state'] }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">E-Mail: {{ $company['email'] }}</p>
                    </div>
                </td>
                <td class="w-50 v-align-top">
                    <table class="w-100" style="border-collapse: collapse;">
                        <tr>
                            <td><span class="font-bold">Voucher No.</span><br>{{ $purchaseOrder->id }}</td>
                            <td><span class="font-bold">Dated</span><br>{{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d-M-y') }}</td>
                        </tr>
                        <tr>
                            <td><span class="font-bold">Reference No. & Date.</span><br>{{ $purchaseOrder->purchase_order_number }}</td>

                        </tr>
                        @if($purchaseOrder->customer_name)
                        <tr>
                            <td><strong>End Customer:</strong></td>
                            <td class="text-end">{{ $purchaseOrder->customer_name }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

    </header>
    <!-- ========================================================================= -->
    <table class="no-border">
        <tr>
            <td class="w-100 v-align-top">
                <table class="w-100" style="border: 1px solid grey;">
                    <p style="border: none; padding-left: 5px; font-size: 10px; margin:0 !important;">Supplier (Bill from)</p>
                    <p style="border: none; padding-left: 5px; font-size: 12px; margin:0 !important;" class="font-bold">{{ $purchaseOrder->party->name }}</p>
                    <p style="border: none; padding-left: 5px; margin:0 !important;">{{ $purchaseOrder->party->address }}</p>
                    <p style="border: none; padding-left: 5px; margin:0 !important;">GSTIN/UIN: {{ $purchaseOrder->party->gst_in }}</p>
                    <p style="border: none; padding-left: 5px; margin:0 !important;">State Name: Maharashtra, Code: 27</p>
                </table>
            </td>
        </tr>
    </table>
    <!-- ================== FIXED FOOTER ================== -->
    <footer class="footer">
        <table class="no-border">
            <tr>
                <td class="w-50 v-align-top">
                    <span class="font-bold">Buyer's PAN:</span> {{ $company['pan'] }}
                </td>
                <td class="w-50 text-right v-align-top">
                    for <span class="font-bold">{{ $company['name'] }}</span>
                    <div style="margin-top: 80px;">Authorised Signatory</div>
                </td>
            </tr>
        </table>
    </footer>
    <!-- ========================================================================= -->

    <!-- ================== FLOWING CONTENT ================== -->
    <main style="margin-top: 2%;">
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">Sl No.</th>
                    <th class="text-center" style="width: 55%;">Description of Goods</th>
                    <th class="text-center" style="width: 10%;">Quantity</th>
                    <th class="text-center" style="width: 10%;">Rate</th>
                    <th class="text-center" style="width: 10%;">per</th>
                    <th class="text-center" style="width: 10%;">Disc. %</th>
                    <th class="text-center" style="width: 10%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">{{ $item->product->name }}</td>
                    <td class="text-center">{{ $item->quantity }} NOS</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center">NOS</td>
                    <td class="text-right">{{ $item->discount ?? '0' }}</td>
                    <td class="text-right">{{ number_format($item->quantity * $item->unit_price * (1 - ($item->discount ?? 0) / 100), 2) }}</td>
                </tr>
                @endforeach

                @for ($i = $purchaseOrder->items->count(); $i < 5; $i++)
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

        <!-- Totals Section -->
        <table>
            <tr>
                <td colspan="5" rowspan="4" style="border-bottom: none; border-left: none; border-top: none; width: 60%;">
                    <span class="font-bold">Amount Chargeable (in words)</span><br>
                    <b>INR {{ $amountInWords }} Rupees Only</b>
                </td>
                <td class="text-left font-bold" style="width: 20%;">Subtotal</td>
                <td class="text-right font-bold" style="width: 20%;">{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if ($totalCgst > 0 || $totalSgst > 0)
            <tr>
                <td class="text-left">CGST @ {{ $purchaseOrder->items->first()->cgst ?? 9 }}%</td>
                <td class="text-right">{{ number_format($totalCgst, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left">SGST @ {{ $purchaseOrder->items->first()->sgst ?? 9 }}%</td>
                <td class="text-right">{{ number_format($totalSgst, 2) }}</td>
            </tr>
            @else
            <tr>
                <td> </td>
                <td> </td>
            </tr>
            <tr>
                <td> </td>
                <td> </td>
            </tr>
            @endif
            @if ($totalIgst > 0)
            <tr>
                <td class="text-left">IGST @ {{ $purchaseOrder->items->first()->igst ?? 18 }}%</td>
                <td class="text-right">{{ number_format($totalIgst, 2) }}</td>
            </tr>
            @endif

        </table>

        <table style="margin-top: 12px;">
            <tfoot>
                <tr>
                    <td colspan="2" class="font-bold">Total</td>
                    <td class="text-center font-bold" style="width: 10%;">{{ $totalQuantity }} NOS</td>
                    <td colspan="3"></td>
                    <td class="text-right font-bold">₹ {{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        <p style="text-align: right;"> E. & O.E</p>
    </main>
    <!-- ========================================================================= -->

</body>

</html>