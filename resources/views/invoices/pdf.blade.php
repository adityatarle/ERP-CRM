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
    </style>
</head>

<body>
    <div class="invoice-wrapper">
        <div class="invoice-body">
            <!-- HEADER SECTION -->
            @include('invoices.partials.header')

            <!-- PRODUCT TABLE SECTION -->
            @include('invoices.partials.items')

            <!-- HSN SUMMARY SECTION -->
            @include('invoices.partials.hsn')
        </div>

        <!-- FOOTER SECTION -->
        <div class="invoice-footer">
            @include('invoices.partials.footer')
        </div>
    </div>
</body>

</html>