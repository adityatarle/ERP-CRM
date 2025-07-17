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
