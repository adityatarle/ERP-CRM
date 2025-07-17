<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice</title>
    <style>
        .table tbody tr {
            height: 25px;
            page-break-inside: avoid;
        }
        .table {
            page-break-inside: auto;
            width: 100%;
            border-collapse: collapse;
        }
        .table thead {
            display: table-header-group;
        }
        .table tfoot {
            display: table-footer-group;
        }
        td, th {
            border: 1px solid black;
            padding: 5px;
        }
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; }
        .container { padding: 10px 20px; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .text-center { text-align: center; }
        .text-uppercase { text-transform: uppercase; }
        .row { display: flex; flex-wrap: wrap; margin-bottom: 10px; }
        .col-12 { width: 100%; padding: 10px; }
        address { margin: 0; font-style: normal; line-height: 1.4; }
        .invoice-details-table td { font-size: 11px; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Your invoice content without header/footer --}}
        @include('invoices.partials.body', ['invoice' => $invoice])
    </div>
</body>
</html>
