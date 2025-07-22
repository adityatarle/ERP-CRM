@php
    $minRows = 8;
@endphp

<table class="items-table" style="margin-top: 10px;">
    <thead>
        <tr>
            <th class="text-uppercase">Sr.<br />No</th>
            <th style="width: 40%;">Product Name</th>
            <th class="text-end">HSN/SAC</th>
            <th class="text-end">Qty</th>
            <th class="text-end">Rate</th>
            <th class="text-end">Disc %</th>
            <th class="text-end">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $saleItem)
            <tr>
                <td class="text-center">{{ $startIndex + $index + 1 }}</td>
                <td class="text-start">
                    <b>{{ $saleItem->product->name ?? '-' }}</b>
                    @if($saleItem->itemcode)<br /><small>{{ $saleItem->itemcode }}</small>@endif
                    @if($saleItem->secondary_itemcode)<small>{{ $saleItem->secondary_itemcode }}</small>@endif
                </td>
                <td class="text-end">{{ $saleItem->product->hsn ?? '-' }}</td>
                <td class="text-end">{{ $saleItem->quantity ?? 0 }} NOS</td>
                <td class="text-end">{{ number_format($saleItem->unit_price ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($saleItem->discount ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($saleItem->total_price ?? 0, 2) }}</td>
            </tr>
        @endforeach

        {{-- Empty filler rows --}}
        @for ($i = $items->count(); $i < $minRows; $i++)
            <tr>
                <td colspan="7">&nbsp;</td>
            </tr>
        @endfor
    </tbody>
</table>
