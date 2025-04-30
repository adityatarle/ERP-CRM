<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        h2 { text-transform: uppercase; text-align: center; margin: 10px 0; font-size: 18px; }
        .container { max-width: 800px; margin: 0 auto; }
        .row { display: table; width: 100%; table-layout: fixed; margin-bottom: 10px; }
        .col-6 { display: table-cell; width: 50%; padding: 10px; vertical-align: top; }
        .col-12 { width: 100%; padding: 10px; }
        address { margin: 0; font-style: normal; line-height: 1.4; }
        hr { margin: 10px 0; border: 1px solid black; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid black; padding: 5px; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .text-center { text-align: center; }
        .text-uppercase { text-transform: uppercase; }
        .d-flex { display: table; width: 100%; }
        .justify-content-between { display: table; width: 100%; }
        .w-50 { display: table-cell; width: 50%; padding-right: 10px; }
        .d-block { display: block; }
        h4 { margin: 0 0 5px 0; font-size: 14px; }
        p { margin: 5px 0; }
        .table thead th { font-weight: bold; }
        .table tbody td { vertical-align: middle; }
        .invoice-details-table td { font-size: 11px; }
    </style>
</head>
<body>
    <section>
        <div class="container">
            <h2>Tax Invoice</h2>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-6">
                        <div>
                            <h4>From</h4>
                            <address>
                                <strong>{{ $invoice->company->name ?? 'BootstrapBrain' }}</strong><br>
                                {{ $invoice->company->address ?? '875 N Coast Hwy' }}<br>
                                {{ $invoice->company->city ?? 'Laguna Beach' }}, {{ $invoice->company->state ?? 'California' }}, {{ $invoice->company->zip ?? '92651' }}<br>
                                {{ $invoice->company->country ?? 'United States' }}<br>
                                Phone: {{ $invoice->company->phone ?? '(949) 494-7695' }}<br>
                                Email: {{ $invoice->company->email ?? 'email@domain.com' }}
                            </address>
                        </div>
                        <hr>
                        <div>
                            <h4>Bill To</h4>
                            <address>
                                <strong>{{ $invoice->customer->name}}</strong><br>
                                {{ $invoice->customer->address }}<br>
                                {{ $invoice->customer->city ?? 'Kansas City' }}, {{ $invoice->customer->state ?? 'Mississippi' }}, {{ $invoice->customer->zip ?? '64151' }}<br>
                                {{ $invoice->customer->country ?? 'United States' }}<br>
                                Phone: {{ $invoice->customer->phone }}<br>
                                Email: {{ $invoice->customer->email }}
                            </address>
                        </div>
                    </div>
                    <div class="col-6">
                        <table class="invoice-details-table">
                            <tbody>
                                <tr>
                                    <td>Invoice #</td>
                                    <td class="text-end">{{ $invoice->invoice_number ?? 'INV-680767801312a' }}</td>
                                </tr>
                                <tr>
                                    <td>Delivery Note</td>
                                    <td class="text-end">{{ $invoice->delivery_note ?? '786-54984' }}</td>
                                </tr>
                                <tr>
                                    <td>Mode/Term of Payment</td>
                                    <td class="text-end">{{ $invoice->payment_mode ?? 'Cash' }}</td>
                                </tr>
                                <tr>
                                    <td>Buyer Order No.</td>
                                    <td class="text-end">{{ $invoice->buyer_order_no ?? '123456' }}</td>
                                </tr>
                                <tr>
                                    <td>Dated</td>
                                    <td class="text-end">{{ $invoice->created_at->format('d/m/Y') ?? '22/04/2025' }}</td>
                                </tr>
                                <tr>
                                    <td>Dispatch Doc. no.</td>
                                    <td class="text-end">{{ $invoice->dispatch_doc_no ?? '123465' }}</td>
                                </tr>
                                <tr>
                                    <td>Destination</td>
                                    <td class="text-end">{{ $invoice->destination ?? 'Mumbai' }}</td>
                                </tr>
                                <tr>
                                    <td>Terms of Delivery</td>
                                    <td class="text-end">{{ $invoice->terms_of_delivery ?? 'No Terms' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase">Sr.no</th>
                                        <th class="text-uppercase">Product Name</th>
                                        <th class="text-uppercase text-end">HSN/SAC</th>
                                        <th class="text-uppercase text-end">Qty</th>
                                        <th class="text-uppercase">Rate</th>
                                        <th class="text-uppercase">Per</th>
                                        <th class="text-uppercase text-end">Disc %</th>
                                        <th class="text-uppercase text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($invoice->sales as $sale)
                                    @foreach($sale->saleItems as $index => $saleItem)
                                        <tr>
                                            <th>{{ $index + 1 }}</th>
                                            <td class="text-start">{{ $saleItem->product->name }}</td>
                                            <td class="text-end">{{ $saleItem->product->hsn_sac ?? '123456798' }}</td>
                                            <td class="text-end">{{ $saleItem->quantity }} NOS</td>
                                            <td class="text-end">{{ number_format($saleItem->product->price, 2) }}</td>
                                            <td class="text-end">NOS</td>
                                            <td class="text-end">{{ $saleItem->discount ?? '0' }}</td>
                                            <td class="text-end">{{ number_format($saleItem->amount ?? $saleItem->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                    <tr>
                                        <th>-</th>
                                        <td class="text-end">CGST @9</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">9</td>
                                        <td class="text-end">%</td>
                                        <td class="text-end">0</td>
                                        <td class="text-end">{{ number_format($invoice->cgst_amount ?? 24416.92, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-uppercase text-end">Total</th>
                                        <td class="text-end">{{ $invoice->total_quantity ?? '74' }} NOS</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">{{ number_format($invoice->total_amount ?? 12301, 2) }}/-</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7">INR <br>Amount In Words: {{ $invoice->amount_in_words ?? 'Twelve Thousand Three Hundred One' }}</td>
                                        <td class="text-end">E. & OE.</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th rowspan="2">HSN</th>
                                        <th rowspan="2">Taxable Value</th>
                                        <th colspan="2">CGST</th>
                                        <th colspan="2">SGST</th>
                                        <th rowspan="2">Total Amount</th>
                                    </tr>
                                    <tr>
                                        <th>Rate</th>
                                        <th>Amount</th>
                                        <th>Rate</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $invoice->hsn ?? '1234' }}</td>
                                        <td>{{ number_format($invoice->taxable_value ?? 1000, 2) }}</td>
                                        <td>{{ $invoice->cgst_rate ?? '9' }}%</td>
                                        <td>{{ number_format($invoice->cgst_amount ?? 90, 2) }}</td>
                                        <td>{{ $invoice->sgst_rate ?? '9' }}%</td>
                                        <td>{{ number_format($invoice->sgst_amount ?? 90, 2) }}</td>
                                        <td>{{ number_format($invoice->total_tax_amount ?? 1180, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <p>Tax amount (In words): <b>{{ $invoice->tax_amount_in_words ?? 'One Thousand Only' }}</b></p>
                <p>Company PAN: <b>{{ $invoice->company->pan ?? 'ABCDEF' }}</b></p>
                <p>Declaration:</p>
                <div class="d-flex justify-content-between">
                    <p class="w-50">We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct</p>
                    <div class="d-block">
                        <p>For {{ $invoice->company->name ?? 'Mauli Solutions' }}</p>
                        <p><b>Authorized Signatory</b></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>