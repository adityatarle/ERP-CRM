<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Delivery Note - {{ $deliveryNote->delivery_note_number }}</title>
    <style>
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
            /* Define margins to create space for the fixed header */
            margin: 240px 50px 80px 50px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            font-size: 11px;
            margin: 0;
        }

        .header {
            position: fixed;
            left: 0px;
            right: 0px;
            top: -180px;
            /* Match top margin */
            width: 100%;
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

        .product-name {
            display: block;
            margin-bottom: 3px;
        }

        .product-code {
            display: block;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>

<body>

    <!-- ================== FIXED HEADER ================== -->
    <header class="header">
        <div class="header-title">DELIVERY NOTE</div>

        <table class="no-border" style="margin-top: 5px;">
            <tr>
                <td class="w-50 v-align-top">
                    <div class="w-100" style="border: 1px solid grey;">
                        <p style="border: none; padding-left: 5px; font-size: 12px; margin:0 !important;" class="font-bold">{{ $company['name'] }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">{{ $company['address'] }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">Mob: {{ $company['contact'] }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">GSTIN/UIN: {{ $company['gstin'] }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">State Name: {{ $company['state'] }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">E-Mail: {{ $company['email'] }}</p>
                    </div>
                </td>
                <td class="w-50 v-align-top">
                    <table class="w-100" style="border-collapse: collapse;">
                        <tr style="margin: 0!important;">
                            <td><span class="font-bold">Delivery Note No.</span><br>{{ $deliveryNote->delivery_note_number }}</td>
                            <td><span class="font-bold">Dated</span><br>{{ \Carbon\Carbon::parse($deliveryNote->delivery_date)->format('d-M-y') }}</td>
                        </tr>
                        <tr style="margin: 0!important;">
                            <td><span class="font-bold">Reference No. & Date</span><br>{{ $deliveryNote->ref_no ?? 'N/A' }}</td>
                            <td><span class="font-bold">Mode/Terms of Payment</span><br></td>
                        </tr>
                        <tr style="margin: 0!important;">
                            <td><span class="font-bold">Buyer's Order No.</span><br>{{ $deliveryNote->purchase_number }}</td>
                            <td><span class="font-bold">Other References</span><br></td>
                        </tr>
                        <tr style="margin: 0!important;">
                            <td><span class="font-bold">Dispatch Doc No.</span><br></td>
                            <td><span class="font-bold">Dated</span><br>{{ \Carbon\Carbon::parse($deliveryNote->purchase_date)->format('d-M-y') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </header>
    <!-- ========================================================================= -->

    <table class="no-border" style="margin-top: 40px;">
        <tr>
            <td class="w-100 v-align-top">
                <div class="w-100" style="border: 1px solid grey;">
                        <p style="border: none; padding-left: 5px; font-size: 10px; margin:0 !important;">Buyer (Bill to)</p>
                        <p style="border: none; padding-left: 5px; font-size: 12px; margin:0 !important;" class="font-bold">{{ $deliveryNote->customer->name }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">{{ $deliveryNote->customer->address }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">GSTIN/UIN: {{ $deliveryNote->customer->gst_number }}</p>
                        <p style="border: none; padding-left: 5px; margin:0 !important;">State Name: Maharashtra, Code: 27</p>
                </div>
            </td>
        </tr>
    </table>
    <!-- ================== FLOWING CONTENT (Everything from here down will flow to page 2 if needed) ================== -->
    <main style="margin-top: 2%;">
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">Sl No.</th>
                    <th class="text-center" style="width: 50%;">Description of Goods</th>
                    <th class="text-center" style="width: 15%;">HSN/SAC</th>
                    <th class="text-center" style="width: 10%;">Quantity</th>
                    <th class="text-center" style="width: 10%;">Rate</th>
                    <th class="text-center" style="width: 5%;">per</th>
                    <th class="text-center" style="width: 5%;">Disc. %</th>
                    <th class="text-center" style="width: 10%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryNote->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">
                        <span class="product-name">{{ $item->product->name }}</span>
                        <span class="product-code">{{ $item->itemcode ?? '' }}</span>
                        <span class="product-code">{{ $item->secondary_itemcode ?? '' }}</span>
                    </td>
                    <td class="text-center">{{ $item->product->hsn ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->quantity }} NOS</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-center">NOS</td>
                    <td class="text-right">{{ number_format($item->discount, 2) }}</td>
                    <td class="text-right">{{ number_format($item->quantity * $item->price * (1 - ($item->discount ?? 0) / 100), 2) }}</td>
                </tr>
                @endforeach
                @for ($i = $deliveryNote->items->count(); $i < 3; $i++)
                    <tr>
                    <td> </td>
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

        <table style="margin-top: -1px;">
            <tr>
                <td colspan="6" rowspan="4" style="border-bottom: none; border-left: none; width: 60%;">
                    <span class="font-bold">Tax Amount (in words) :</span> NIL
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
                <td class="text-left">IGST @ {{ $deliveryNote->igst ?? 18 }}%</td>
                <td class="text-right">{{ number_format($totalIgst, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="text-left font-bold">Total</td>
                <td class="text-right font-bold">₹ {{ number_format($grandTotal, 2) }}</td>
            </tr>
        </table>

        <table style="margin-top: 12px;">
            <tfoot>
                <tr>
                    <td class="font-bold" colspan="2">Total</td>
                    <td class="text-center font-bold" style="width: 15%;">{{ $totalQuantity }} NOS</td>
                    <td colspan="4"></td>
                    <td class="text-right font-bold">E. & O.E</td>
                </tr>
                <tr>
                    <td class="font-bold" colspan="2">HSN/SAC</td>
                    <td class="text-center">{{ $deliveryNote->items->map(fn($item) => $item->product->hsn)->unique()->implode(', ') }}</td>
                    <td colspan="3"></td>
                    <td class="font-bold">Taxable Value</td>
                    <td>{{ number_format($subtotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Moved Notes & Description inside the main content flow -->
        @if($deliveryNote->notes || $deliveryNote->description)
        <table style="margin-top: 10px;">
            <tr>
                <td style="border: 1px solid grey;">
                    @if($deliveryNote->notes)
                    <p><span class="font-bold">Notes:</span> {{ $deliveryNote->notes }}</p>
                    @endif
                    @if($deliveryNote->description)
                    <p><span class="font-bold">Description:</span> {{ $deliveryNote->description }}</p>
                    @endif
                </td>
            </tr>
        </table>
        @endif

        <!-- Moved Footer inside the main content flow -->
        <table class="no-border" style="margin-top: 20px;">
            <tr>
                <td class="w-50 v-align-top">
                    <span class="font-bold">Company's PAN:</span> {{ $company['pan'] }}
                    <div style="margin-top: 20px;">Recd. in Good Condition</div>
                    <div style="margin-top: 20px;">This is a Computer Generated Document</div>
                </td>
                <td class="w-50 text-right v-align-top">
                    for <span class="font-bold">{{ $company['name'] }}</span>
                    <div style="margin-top: 40px;">Authorised Signatory</div>
                </td>
            </tr>
        </table>
    </main>
    <!-- ========================================================================= -->
</body>

</html>