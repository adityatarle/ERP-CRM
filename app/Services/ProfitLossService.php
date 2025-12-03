<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PurchaseEntry;
use App\Models\PurchaseEntryItem;
use App\Models\Payment;
use App\Models\Quantra;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfitLossService
{
    /**
     * Generate comprehensive Profit & Loss report
     */
    public function generateProfitLossReport(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $customerId = $filters['customer_id'] ?? null;
        $category = $filters['category'] ?? null;

        // Build date range
        $dateRange = $this->buildDateRange($startDate, $endDate);

        return [
            'revenue' => $this->calculateRevenue($dateRange, $customerId, $category),
            'cost_of_goods_sold' => $this->calculateCostOfGoodsSold($dateRange, $customerId, $category),
            'gross_profit' => $this->calculateGrossProfit($dateRange, $customerId, $category),
            'operating_expenses' => $this->calculateOperatingExpenses($dateRange),
            'other_income' => $this->calculateOtherIncome($dateRange),
            'other_expenses' => $this->calculateOtherExpenses($dateRange),
            'net_profit' => $this->calculateNetProfit($dateRange, $customerId, $category),
            'summary' => $this->generateSummary($dateRange, $customerId, $category),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Calculate total revenue from sales
     */
    private function calculateRevenue(array $dateRange, ?int $customerId = null, ?string $category = null): array
    {
        $query = Sale::query();

        // Apply date filter
        if ($dateRange['start'] && $dateRange['end']) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }

        // Apply customer filter
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        // Apply category filter
        if ($category) {
            $query->whereHas('saleItems.product', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        $sales = $query->with(['saleItems.product', 'customer'])->get();

        $totalRevenue = $sales->sum('total_price');
        $totalInvoices = $sales->count();
        $averageInvoiceValue = $totalInvoices > 0 ? $totalRevenue / $totalInvoices : 0;

        // Calculate revenue by customer
        $revenueByCustomer = $sales->groupBy('customer_id')->map(function ($customerSales) {
            return [
                'customer_name' => $customerSales->first()->customer->name ?? 'Unknown',
                'total_revenue' => $customerSales->sum('total_price'),
                'invoice_count' => $customerSales->count(),
                'average_invoice' => $customerSales->count() > 0 ? $customerSales->sum('total_price') / $customerSales->count() : 0
            ];
        })->sortByDesc('total_revenue');

        // Calculate revenue by category
        $revenueByCategory = $this->calculateRevenueByCategory($sales);

        return [
            'total_revenue' => $totalRevenue,
            'total_invoices' => $totalInvoices,
            'average_invoice_value' => $averageInvoiceValue,
            'revenue_by_customer' => $revenueByCustomer,
            'revenue_by_category' => $revenueByCategory,
            'sales_data' => $sales
        ];
    }

    /**
     * Calculate Cost of Goods Sold (COGS)
     */
    private function calculateCostOfGoodsSold(array $dateRange, ?int $customerId = null, ?string $category = null): array
    {
        // Get all purchase entries for the period
        $purchaseQuery = PurchaseEntry::query();
        
        if ($dateRange['start'] && $dateRange['end']) {
            $purchaseQuery->whereBetween('purchase_date', [$dateRange['start'], $dateRange['end']]);
        }

        $purchaseEntries = $purchaseQuery->with(['items.product', 'party'])->get();

        // Calculate total purchase cost
        $totalPurchaseCost = 0;
        $purchaseDetails = [];

        foreach ($purchaseEntries as $entry) {
            $entryCost = 0;
            foreach ($entry->items as $item) {
                if ($item->status === 'received') {
                    $itemCost = ($item->unit_price * (1 - ($item->discount ?? 0) / 100)) * $item->quantity;
                    $entryCost += $itemCost;
                }
            }
            $totalPurchaseCost += $entryCost;

            $purchaseDetails[] = [
                'purchase_number' => $entry->purchase_number,
                'purchase_date' => $entry->purchase_date,
                'party_name' => $entry->party->name ?? 'Unknown',
                'total_cost' => $entryCost,
                'gst_amount' => $entry->gst_amount ?? 0,
                'cash_discount' => $entry->cash_discount ?? 0
            ];
        }

        // Calculate COGS by category
        $cogsByCategory = $this->calculateCOGSByCategory($purchaseEntries);

        return [
            'total_cogs' => $totalPurchaseCost,
            'purchase_entries_count' => $purchaseEntries->count(),
            'cogs_by_category' => $cogsByCategory,
            'purchase_details' => $purchaseDetails
        ];
    }

    /**
     * Calculate Gross Profit
     */
    private function calculateGrossProfit(array $dateRange, ?int $customerId = null, ?string $category = null): array
    {
        $revenue = $this->calculateRevenue($dateRange, $customerId, $category);
        $cogs = $this->calculateCostOfGoodsSold($dateRange, $customerId, $category);

        $grossProfit = $revenue['total_revenue'] - $cogs['total_cogs'];
        $grossProfitMargin = $revenue['total_revenue'] > 0 ? ($grossProfit / $revenue['total_revenue']) * 100 : 0;

        return [
            'gross_profit' => $grossProfit,
            'gross_profit_margin' => $grossProfitMargin,
            'revenue' => $revenue['total_revenue'],
            'cogs' => $cogs['total_cogs']
        ];
    }

    /**
     * Calculate Operating Expenses (Quantra expenses)
     */
    private function calculateOperatingExpenses(array $dateRange): array
    {
        $query = Quantra::query();

        if ($dateRange['start'] && $dateRange['end']) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }

        $quantraExpenses = $query->get();

        $totalOperatingExpenses = $quantraExpenses->sum('amount');

        return [
            'total_operating_expenses' => $totalOperatingExpenses,
            'quantra_expenses' => $quantraExpenses->map(function ($expense) {
                return [
                    'amount' => $expense->amount,
                    'note' => $expense->note,
                    'date' => $expense->created_at->format('Y-m-d')
                ];
            }),
            'expense_count' => $quantraExpenses->count()
        ];
    }

    /**
     * Calculate Other Income (from receivable payments)
     */
    private function calculateOtherIncome(array $dateRange): array
    {
        $query = Payment::where('type', 'receivable');

        if ($dateRange['start'] && $dateRange['end']) {
            $query->whereBetween('payment_date', [$dateRange['start'], $dateRange['end']]);
        }

        $receivablePayments = $query->with(['customer', 'invoice'])->get();

        $totalOtherIncome = $receivablePayments->sum('amount');
        $totalTDS = $receivablePayments->sum('tds_amount');

        return [
            'total_other_income' => $totalOtherIncome,
            'total_tds' => $totalTDS,
            'net_other_income' => $totalOtherIncome - $totalTDS,
            'payment_count' => $receivablePayments->count(),
            'payments_by_customer' => $receivablePayments->groupBy('customer_id')->map(function ($payments) {
                return [
                    'customer_name' => $payments->first()->customer->name ?? 'Unknown',
                    'total_payments' => $payments->sum('amount'),
                    'total_tds' => $payments->sum('tds_amount'),
                    'payment_count' => $payments->count()
                ];
            })
        ];
    }

    /**
     * Calculate Other Expenses (from payable payments)
     */
    private function calculateOtherExpenses(array $dateRange): array
    {
        $query = Payment::where('type', 'payable');

        if ($dateRange['start'] && $dateRange['end']) {
            $query->whereBetween('payment_date', [$dateRange['start'], $dateRange['end']]);
        }

        $payablePayments = $query->with(['party', 'purchaseEntry'])->get();

        $totalOtherExpenses = $payablePayments->sum('amount');

        return [
            'total_other_expenses' => $totalOtherExpenses,
            'payment_count' => $payablePayments->count(),
            'payments_by_party' => $payablePayments->groupBy('party_id')->map(function ($payments) {
                return [
                    'party_name' => $payments->first()->party->name ?? 'Unknown',
                    'total_payments' => $payments->sum('amount'),
                    'payment_count' => $payments->count()
                ];
            })
        ];
    }

    /**
     * Calculate Net Profit
     */
    private function calculateNetProfit(array $dateRange, ?int $customerId = null, ?string $category = null): array
    {
        $grossProfit = $this->calculateGrossProfit($dateRange, $customerId, $category);
        $operatingExpenses = $this->calculateOperatingExpenses($dateRange);
        $otherIncome = $this->calculateOtherIncome($dateRange);
        $otherExpenses = $this->calculateOtherExpenses($dateRange);

        $netProfit = $grossProfit['gross_profit'] 
                    - $operatingExpenses['total_operating_expenses']
                    + $otherIncome['net_other_income']
                    - $otherExpenses['total_other_expenses'];

        $netProfitMargin = $grossProfit['revenue'] > 0 ? ($netProfit / $grossProfit['revenue']) * 100 : 0;

        return [
            'net_profit' => $netProfit,
            'net_profit_margin' => $netProfitMargin,
            'gross_profit' => $grossProfit['gross_profit'],
            'operating_expenses' => $operatingExpenses['total_operating_expenses'],
            'other_income' => $otherIncome['net_other_income'],
            'other_expenses' => $otherExpenses['total_other_expenses']
        ];
    }

    /**
     * Generate summary statistics
     */
    private function generateSummary(array $dateRange, ?int $customerId = null, ?string $category = null): array
    {
        $revenue = $this->calculateRevenue($dateRange, $customerId, $category);
        $cogs = $this->calculateCostOfGoodsSold($dateRange, $customerId, $category);
        $grossProfit = $this->calculateGrossProfit($dateRange, $customerId, $category);
        $operatingExpenses = $this->calculateOperatingExpenses($dateRange);
        $otherIncome = $this->calculateOtherIncome($dateRange);
        $otherExpenses = $this->calculateOtherExpenses($dateRange);
        $netProfit = $this->calculateNetProfit($dateRange, $customerId, $category);

        return [
            'total_revenue' => $revenue['total_revenue'],
            'total_cogs' => $cogs['total_cogs'],
            'gross_profit' => $grossProfit['gross_profit'],
            'gross_profit_margin' => $grossProfit['gross_profit_margin'],
            'operating_expenses' => $operatingExpenses['total_operating_expenses'],
            'other_income' => $otherIncome['net_other_income'],
            'other_expenses' => $otherExpenses['total_other_expenses'],
            'net_profit' => $netProfit['net_profit'],
            'net_profit_margin' => $netProfit['net_profit_margin'],
            'total_invoices' => $revenue['total_invoices'],
            'total_purchases' => $cogs['purchase_entries_count'],
            'total_quantra_expenses' => $operatingExpenses['expense_count'],
            'total_receivable_payments' => $otherIncome['payment_count'],
            'total_payable_payments' => $otherExpenses['payment_count']
        ];
    }

    /**
     * Calculate revenue by category
     */
    private function calculateRevenueByCategory($sales): array
    {
        $categoryRevenue = [];

        foreach ($sales as $sale) {
            foreach ($sale->saleItems as $item) {
                if ($item->product && $item->product->category) {
                    $category = $item->product->category;
                    if (!isset($categoryRevenue[$category])) {
                        $categoryRevenue[$category] = [
                            'category' => $category,
                            'total_revenue' => 0,
                            'total_quantity' => 0,
                            'product_count' => 0
                        ];
                    }
                    $categoryRevenue[$category]['total_revenue'] += $item->total_price;
                    $categoryRevenue[$category]['total_quantity'] += $item->quantity;
                    $categoryRevenue[$category]['product_count']++;
                }
            }
        }

        return collect($categoryRevenue)->sortByDesc('total_revenue')->values()->toArray();
    }

    /**
     * Calculate COGS by category
     */
    private function calculateCOGSByCategory($purchaseEntries): array
    {
        $categoryCOGS = [];

        foreach ($purchaseEntries as $entry) {
            foreach ($entry->items as $item) {
                if ($item->status === 'received' && $item->product && $item->product->category) {
                    $category = $item->product->category;
                    if (!isset($categoryCOGS[$category])) {
                        $categoryCOGS[$category] = [
                            'category' => $category,
                            'total_cost' => 0,
                            'total_quantity' => 0,
                            'product_count' => 0
                        ];
                    }
                    $itemCost = ($item->unit_price * (1 - ($item->discount ?? 0) / 100)) * $item->quantity;
                    $categoryCOGS[$category]['total_cost'] += $itemCost;
                    $categoryCOGS[$category]['total_quantity'] += $item->quantity;
                    $categoryCOGS[$category]['product_count']++;
                }
            }
        }

        return collect($categoryCOGS)->sortByDesc('total_cost')->values()->toArray();
    }

    /**
     * Build date range for filtering
     */
    private function buildDateRange(?string $startDate, ?string $endDate): array
    {
        return [
            'start' => $startDate ? Carbon::parse($startDate)->startOfDay() : null,
            'end' => $endDate ? Carbon::parse($endDate)->endOfDay() : null
        ];
    }

    /**
     * Get available categories for filtering
     */
    public function getAvailableCategories(): array
    {
        return DB::table('products')
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get available customers for filtering
     */
    public function getAvailableCustomers(): array
    {
        return DB::table('customers')
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
