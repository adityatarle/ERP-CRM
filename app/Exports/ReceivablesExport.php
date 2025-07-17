<?php

namespace App\Exports;

use App\Models\Receivable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReceivablesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;
    protected $tdsFilter;

    public function __construct($startDate = null, $endDate = null, $tdsFilter = 'all')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->tdsFilter = $tdsFilter;

        Log::info('ReceivablesExport initialized with date range and TDS filter', [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'tds_filter' => $this->tdsFilter,
        ]);
    }

    public function collection()
    {
        $query = Receivable::with('sale', 'customer')
            ->where('is_paid', false);

        // Apply TDS filter
        if ($this->tdsFilter !== 'all') {
            if ($this->tdsFilter === 'with_tds') {
                $query->whereHas('sale', function ($q) {
                    $q->whereNotNull('tds')->where('tds', '>', 0);
                });
            } elseif ($this->tdsFilter === 'without_tds') {
                $query->whereHas('sale', function ($q) {
                    $q->where(function ($q) {
                        $q->whereNull('tds')->orWhere('tds', 0);
                    });
                });
            }
        }

        // Apply date range if provided
        if ($this->startDate && $this->endDate) {
            $query->whereBetween(\DB::raw('DATE(created_at)'), [$this->startDate, $this->endDate]);
        } else {
            Log::warning('ReceivablesExport: No date range provided, exporting records based on TDS filter only');
        }

        $receivables = $query->get();

        Log::info('Exporting receivables to Excel', [
            'count' => $receivables->count(),
            'tds_filter' => $this->tdsFilter,
            'receivables' => $receivables->map(function ($receivable) {
                return [
                    'id' => $receivable->id,
                    'sale_id' => $receivable->sale_id,
                    'created_at' => $receivable->created_at,
                    'credit_days' => $receivable->credit_days,
                ];
            })->toArray(),
        ]);

        return $receivables;
    }

    public function headings(): array
    {
        return [
            'Reference Number',
            'Customer',
            'Amount',
            'Credit Days',
            'Due Days',
            'Status',
        ];
    }

    public function map($receivable): array
    {
        return [
            $receivable->sale->ref_no ?? 'N/A',
            $receivable->customer->name ?? 'N/A',
            $receivable->amount,
            $receivable->credit_days ?? 'N/A',
            $receivable->due_days,
            $receivable->is_paid ? 'Paid' : 'Pending',
        ];
    }
}