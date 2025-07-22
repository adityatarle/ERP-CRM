<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Receipt Note - {{ $receiptNote->receipt_number }}</title>
    <style>
        /* CSS is unchanged and correct */
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

        /* Apply to the items (products) table only */
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

        /* Keep top and bottom border for the header row only */
        .items-table thead th {
            border-top: 1px solid grey;
            border-bottom: 1px solid grey;
        }
    </style>
</head>

<body>

    <!-- ================== FIXED HEADER ================== -->
    <header class="header">
        <div class="header-title">Receipt Note</div>

        {{-- Company Info + Logo --}}
        <table class="no-border" style="margin-top: 5px; width: 100%;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div style="margin: 0;">
                        <p style="padding-left: 5px; font-size: 12px; font-weight: bold; margin:0;">{{ $company['name'] }}</p>
                        <p style="padding-left: 5px; margin:0;">{{ $company['address'] }}</p>
                        <p style="padding-left: 5px; margin:0;">Mob: {{ $company['contact'] }}</p>
                        <p style="padding-left: 5px; margin:0;">GSTIN/UIN: {{ $company['gstin'] }}</p>
                        <p style="padding-left: 5px; margin:0;">State Name: {{ $company['state'] }}</p>
                        <p style="padding-left: 5px; margin:0;">E-Mail: {{ $company['email'] }}</p>
                    </div>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: center;">
                    {{-- Place your logo here --}}
                    <img src="{{ public_path('assets/img/mauli-logo.jpeg') }}" alt="Logo" style="height: 100px;">
                </td>
            </tr>
        </table>

        {{-- Divider Line --}}
        <hr style="margin: 5px 0; border: 1px solid #000;">

        {{-- Supplier Info + Receipt Meta --}}

    </header>
    <table class="no-border" style="width: 100%; margin-top: 2px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <p style="padding-left: 5px; font-size: 10px; margin:0;">Supplier (Bill from)</p>
                <p style="padding-left: 5px; font-size: 12px; font-weight: bold; margin:0;">{{ $receiptNote->party->name }}</p>
                <p style="padding-left: 5px; margin:0;">{{ $receiptNote->party->address }}</p>
                <p style="padding-left: 5px; margin:0;">GSTIN/UIN: {{ $receiptNote->party->gst_in }}</p>
                <p style="padding-left: 5px; margin:0;">State Name: Maharashtra, Code: 27</p>
            </td>

            <td style="width: 50%; vertical-align: top;">
                <table style="width: 100%;">
                    <tr>
                        <td><strong>Receipt Note No.</strong><br>{{ $receiptNote->receipt_number }}</td>
                        <td><strong>Dated</strong><br>{{ \Carbon\Carbon::parse($receiptNote->receipt_date)->format('d-M-y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Reference No. & Date.</strong><br></td>
                        <td><strong>Contact Person:</strong><br>{{ $receiptNote->contact_person ?? 'N/A' }}</td>
                    </tr>
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
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">Sl No.</th>
                    <th class="text-center" style="width: 55%;">Description of Goods</th>
                    <th class="text-center" style="width: 10%;">Quantity</th>
                    <th class="text-center" style="width: 10%;">Rate</th>
                    <th class="text-center" style="width: 10%;">Disc. %</th>
                    <th class="text-center" style="width: 10%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receiptNote->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">{{ $item->product->name }}</td>
                    <td class="text-center">{{ $item->quantity }} NOS</td>
                    {{-- KEY FIX: POPULATE THE FINANCIAL DATA FOR EACH ITEM --}}
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $receiptNote->discount ? number_format($receiptNote->discount, 2) : '0.00' }}</td>
                    <td class="text-right">{{ number_format($item->quantity * $item->unit_price * (1 - ($receiptNote->discount ?? 0) / 100), 2) }}</td>
                </tr>
                @endforeach

                @for ($i = $receiptNote->items->count(); $i < 10; $i++)
                    <tr>
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

        <!-- KEY FIX: ADD THE TOTALS SECTION -->
        <table style="margin-top: 10px;">
            <tr>
                <td colspan="5" rowspan="4" style="border-bottom: none; border-left: none; border-top: none; width: 60%;">
                    <span class="font-bold">Amount Chargeable (in words)</span><br>
                    <p class="font-bold">INR {{ $amountInWords }}</p>
                </td>
                <td class="text-left font-bold" style="width: 20%;">Subtotal</td>
                <td class="text-right font-bold" style="width: 20%;">{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if ($totalCgst > 0 || $totalSgst > 0)
            <tr>
                <td class="text-left">CGST @ {{ $receiptNote->items->first()->cgst_rate ?? 9 }}%</td>
                <td class="text-right">{{ number_format($totalCgst, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left">SGST @ {{ $receiptNote->items->first()->sgst_rate ?? 9 }}%</td>
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
                <td class="text-left">IGST @ {{ $receiptNote->items->first()->igst_rate ?? 18 }}%</td>
                <td class="text-right">{{ number_format($totalIgst, 2) }}</td>
            </tr>
            @endif

        </table>

        <table style="margin-top: 0px;">
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