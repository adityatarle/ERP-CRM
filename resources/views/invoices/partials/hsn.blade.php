{{-- This is the summary partial: resources/views/invoices/pdf/_hsn_summary.blade.php --}}
<table class="items-table" style="margin-top: 10px;">
    <tbody>
        <tr>
            <td colspan="6" class="text-end font-bold">Subtotal</td>
            <td class="text-end font-bold">{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if ($invoice->gst_type === 'IGST')
            <tr>
                <td colspan="6" class="text-end">Add: IGST @ {{ $invoice->igst }}%</td>
                <td class="text-end">{{ number_format($invoice->tax, 2) }}</td>
            </tr>
        @else
            <tr>
                <td colspan="6" class="text-end">Add: CGST @ {{ $invoice->cgst }}%</td>
                <td class="text-end">{{ number_format($invoice->subtotal * ($invoice->cgst / 100), 2) }}</td>
            </tr>
            <tr>
                <td colspan="6" class="text-end">Add: SGST @ {{ $invoice->sgst }}%</td>
                <td class="text-end">{{ number_format($invoice->subtotal * ($invoice->sgst / 100), 2) }}</td>
            </tr>
        @endif
        <tr>
            <th colspan="3" class="text-uppercase text-end">Grand Total</th>
            <th class="text-center">{{ $totalQuantity }} NOS</th>
            <th colspan="2"></th>
            <th class="text-end">{{ number_format($invoice->total, 2) }}/-</th>
        </tr>
        <tr>
            <td colspan="6">Amount (In Words): <b>INR {{ $amount_in_words ?? '' }} Rupees Only</b></td>
            <td class="text-end">E. & OE.</td>
        </tr>
    </tbody>
</table>

@if(!empty($hsnSummary))
    <table class="table" style="margin-top: 15px;">
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
                <th class="text-center">Rate</th><th class="text-end">Amount</th>
                <th class="text-center">Rate</th><th class="text-end">Amount</th>
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