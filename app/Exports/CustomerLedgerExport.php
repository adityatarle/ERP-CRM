<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon; // Make sure Carbon is imported

class CustomerLedgerExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $customer;
    protected $ledgerData;
    protected $runningBalance = 0; // Initialize running balance property

    public function __construct(Customer $customer)
    {
        $this->customer = $customer->load(['invoices', 'payments']);

        // Combine invoices and payments
        $invoices = $this->customer->invoices->map(function ($invoice) {
            return [
                'date'        => $invoice->issue_date,
                'description' => 'Invoice #' . $invoice->invoice_number,
                'debit'       => $invoice->total,
                'credit'      => null,
            ];
        });

        $payments = $this->customer->payments->map(function ($payment) {
            $description = 'Payment Received';
            if ($payment->tds_amount > 0) {
                $description .= ' (incl. TDS ₹' . number_format($payment->tds_amount, 2) . ')';
            }
            return [
                'date'        => $payment->payment_date,
                'description' => $description,
                'debit'       => null,
                'credit'      => $payment->amount + $payment->tds_amount,
            ];
        });

        // Merge and sort by date
        $this->ledgerData = $invoices->concat($payments)->sortBy('date');
    }

    public function collection()
    {
        return $this->ledgerData;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Description',
            'Debit (Invoiced)',
            'Credit (Received)',
            'Balance',
        ];
    }

    public function map($row): array
    {
        $debit = $row['debit'] ?? 0;
        $credit = $row['credit'] ?? 0;
        $this->runningBalance += $debit - $credit;

        return [
            $row['date'] instanceof Carbon ? $row['date']->format('d-m-Y') : '',
            $row['description'],
            $row['debit'],
            $row['credit'],
            $this->runningBalance,
        ];
    }

    public function title(): string
    {
        return 'Customer Ledger';
    }

    public function columnFormats(): array
    {
        return [
            'C' => '#,##0.00',
            'D' => '#,##0.00',
            'E' => '#,##0.00',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'STATEMENT FOR: ' . strtoupper($this->customer->name));
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $headerRange = 'A3:E3';
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E2E2');
                $sheet->getStyle('C:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $lastRow = $sheet->getHighestRow();
                $dataRange = 'A3:E' . $lastRow;
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $summaryStartRow = $lastRow + 2;

                $totalInvoiced = $this->customer->invoices->sum('total');
                $totalReceived = $this->customer->payments->sum(fn($p) => $p->amount + $p->tds_amount);
                $balanceDue = $totalInvoiced - $totalReceived;

                $sheet->setCellValue('D' . $summaryStartRow, 'Total Invoiced:');
                $sheet->setCellValue('D' . ($summaryStartRow + 1), 'Total Received:');
                $sheet->setCellValue('D' . ($summaryStartRow + 2), 'Balance Due:');
                
                $sheet->setCellValue('E' . $summaryStartRow, $totalInvoiced);
                $sheet->setCellValue('E' . ($summaryStartRow + 1), $totalReceived);
                $sheet->setCellValue('E' . ($summaryStartRow + 2), $balanceDue);
                
                $summaryLabelRange = 'D' . $summaryStartRow . ':D' . ($summaryStartRow + 2);
                $summaryValueRange = 'E' . $summaryStartRow . ':E' . ($summaryStartRow + 2);
                
                $sheet->getStyle($summaryLabelRange)->getFont()->setBold(true);
                $sheet->getStyle($summaryLabelRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle($summaryValueRange)->getFont()->setBold(true);
            },
        ];
    }
}