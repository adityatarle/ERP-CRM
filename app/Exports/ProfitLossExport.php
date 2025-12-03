<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProfitLossExport implements WithMultipleSheets
{
    protected $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function sheets(): array
    {
        return [
            'Summary' => new ProfitLossSummarySheet($this->report),
            'Revenue Details' => new ProfitLossRevenueSheet($this->report),
            'COGS Details' => new ProfitLossCOGSSheet($this->report),
            'Operating Expenses' => new ProfitLossOperatingExpensesSheet($this->report),
            'Other Income' => new ProfitLossOtherIncomeSheet($this->report),
            'Other Expenses' => new ProfitLossOtherExpensesSheet($this->report),
        ];
    }
}

class ProfitLossSummarySheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function array(): array
    {
        $summary = $this->report['summary'];
        
        return [
            ['Profit & Loss Report Summary'],
            [''],
            ['Period', $this->report['period']['start_date'] . ' to ' . $this->report['period']['end_date']],
            ['Generated At', $this->report['period']['generated_at']],
            [''],
            ['REVENUE'],
            ['Total Revenue', '₹' . number_format($summary['total_revenue'], 2)],
            ['Total Invoices', $summary['total_invoices']],
            [''],
            ['COST OF GOODS SOLD'],
            ['Total COGS', '₹' . number_format($summary['total_cogs'], 2)],
            ['Total Purchases', $summary['total_purchases']],
            [''],
            ['GROSS PROFIT'],
            ['Gross Profit', '₹' . number_format($summary['gross_profit'], 2)],
            ['Gross Profit Margin', number_format($summary['gross_profit_margin'], 2) . '%'],
            [''],
            ['OPERATING EXPENSES'],
            ['Total Operating Expenses', '₹' . number_format($summary['operating_expenses'], 2)],
            ['Quantra Expenses Count', $summary['total_quantra_expenses']],
            [''],
            ['OTHER INCOME'],
            ['Total Other Income', '₹' . number_format($summary['other_income'], 2)],
            ['Receivable Payments Count', $summary['total_receivable_payments']],
            [''],
            ['OTHER EXPENSES'],
            ['Total Other Expenses', '₹' . number_format($summary['other_expenses'], 2)],
            ['Payable Payments Count', $summary['total_payable_payments']],
            [''],
            ['NET PROFIT'],
            ['Net Profit', '₹' . number_format($summary['net_profit'], 2)],
            ['Net Profit Margin', number_format($summary['net_profit_margin'], 2) . '%'],
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function headings(): array
    {
        return ['Item', 'Value'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            6 => ['font' => ['bold' => true]],
            10 => ['font' => ['bold' => true]],
            13 => ['font' => ['bold' => true]],
            16 => ['font' => ['bold' => true]],
            19 => ['font' => ['bold' => true]],
            22 => ['font' => ['bold' => true]],
            25 => ['font' => ['bold' => true]],
            28 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
        ];
    }
}

class ProfitLossRevenueSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function array(): array
    {
        $data = [];
        $revenue = $this->report['revenue'];

        // Revenue by Customer
        $data[] = ['REVENUE BY CUSTOMER'];
        $data[] = ['Customer Name', 'Total Revenue', 'Invoice Count', 'Average Invoice'];
        $data[] = [];

        foreach ($revenue['revenue_by_customer'] as $customer) {
            $data[] = [
                $customer['customer_name'],
                '₹' . number_format($customer['total_revenue'], 2),
                $customer['invoice_count'],
                '₹' . number_format($customer['average_invoice'], 2)
            ];
        }

        $data[] = [];
        $data[] = ['REVENUE BY CATEGORY'];
        $data[] = ['Category', 'Total Revenue', 'Total Quantity', 'Product Count'];
        $data[] = [];

        foreach ($revenue['revenue_by_category'] as $category) {
            $data[] = [
                $category['category'],
                '₹' . number_format($category['total_revenue'], 2),
                $category['total_quantity'],
                $category['product_count']
            ];
        }

        return $data;
    }

    public function title(): string
    {
        return 'Revenue Details';
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true]],
            9 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 15,
            'D' => 20,
        ];
    }
}

class ProfitLossCOGSSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function array(): array
    {
        $data = [];
        $cogs = $this->report['cost_of_goods_sold'];

        // COGS by Category
        $data[] = ['COGS BY CATEGORY'];
        $data[] = ['Category', 'Total Cost', 'Total Quantity', 'Product Count'];
        $data[] = [];

        foreach ($cogs['cogs_by_category'] as $category) {
            $data[] = [
                $category['category'],
                '₹' . number_format($category['total_cost'], 2),
                $category['total_quantity'],
                $category['product_count']
            ];
        }

        $data[] = [];
        $data[] = ['PURCHASE DETAILS'];
        $data[] = ['Purchase Number', 'Purchase Date', 'Party Name', 'Total Cost', 'GST Amount', 'Cash Discount'];
        $data[] = [];

        foreach ($cogs['purchase_details'] as $purchase) {
            $data[] = [
                $purchase['purchase_number'],
                $purchase['purchase_date'],
                $purchase['party_name'],
                '₹' . number_format($purchase['total_cost'], 2),
                '₹' . number_format($purchase['gst_amount'], 2),
                '₹' . number_format($purchase['cash_discount'], 2)
            ];
        }

        return $data;
    }

    public function title(): string
    {
        return 'COGS Details';
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true]],
            9 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 15,
            'C' => 25,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }
}

class ProfitLossOperatingExpensesSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function array(): array
    {
        $data = [];
        $expenses = $this->report['operating_expenses'];

        $data[] = ['OPERATING EXPENSES (QUANTRA)'];
        $data[] = ['Amount', 'Note', 'Date'];
        $data[] = [];

        foreach ($expenses['quantra_expenses'] as $expense) {
            $data[] = [
                '₹' . number_format($expense['amount'], 2),
                $expense['note'],
                $expense['date']
            ];
        }

        $data[] = [];
        $data[] = ['Total Operating Expenses', '₹' . number_format($expenses['total_operating_expenses'], 2)];
        $data[] = ['Expense Count', $expenses['expense_count']];

        return $data;
    }

    public function title(): string
    {
        return 'Operating Expenses';
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
            7 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 40,
            'C' => 15,
        ];
    }
}

class ProfitLossOtherIncomeSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function array(): array
    {
        $data = [];
        $income = $this->report['other_income'];

        $data[] = ['OTHER INCOME (RECEIVABLE PAYMENTS)'];
        $data[] = ['Customer Name', 'Total Payments', 'Total TDS', 'Payment Count'];
        $data[] = [];

        foreach ($income['payments_by_customer'] as $customer) {
            $data[] = [
                $customer['customer_name'],
                '₹' . number_format($customer['total_payments'], 2),
                '₹' . number_format($customer['total_tds'], 2),
                $customer['payment_count']
            ];
        }

        $data[] = [];
        $data[] = ['Total Other Income', '₹' . number_format($income['total_other_income'], 2)];
        $data[] = ['Total TDS', '₹' . number_format($income['total_tds'], 2)];
        $data[] = ['Net Other Income', '₹' . number_format($income['net_other_income'], 2)];
        $data[] = ['Payment Count', $income['payment_count']];

        return $data;
    }

    public function title(): string
    {
        return 'Other Income';
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
            7 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true]],
            9 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 15,
            'D' => 15,
        ];
    }
}

class ProfitLossOtherExpensesSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function array(): array
    {
        $data = [];
        $expenses = $this->report['other_expenses'];

        $data[] = ['OTHER EXPENSES (PAYABLE PAYMENTS)'];
        $data[] = ['Party Name', 'Total Payments', 'Payment Count'];
        $data[] = [];

        foreach ($expenses['payments_by_party'] as $party) {
            $data[] = [
                $party['party_name'],
                '₹' . number_format($party['total_payments'], 2),
                $party['payment_count']
            ];
        }

        $data[] = [];
        $data[] = ['Total Other Expenses', '₹' . number_format($expenses['total_other_expenses'], 2)];
        $data[] = ['Payment Count', $expenses['payment_count']];

        return $data;
    }

    public function title(): string
    {
        return 'Other Expenses';
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
            7 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 15,
        ];
    }
}
