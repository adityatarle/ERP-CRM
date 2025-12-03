<?php

namespace App\Http\Controllers;

use App\Services\ProfitLossService;
use App\Exports\ProfitLossExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProfitLossController extends Controller
{
    protected $profitLossService;

    public function __construct(ProfitLossService $profitLossService)
    {
        $this->profitLossService = $profitLossService;
        $this->middleware('superadmin');
    }

    /**
     * Display the Profit & Loss report
     */
    public function index(Request $request)
    {
        try {
            // Validate request parameters
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'customer_id' => 'nullable|exists:customers,id',
                'category' => 'nullable|string|max:255',
            ]);

            $filters = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'customer_id' => $request->input('customer_id'),
                'category' => $request->input('category'),
            ];

            // Generate P&L report
            $report = $this->profitLossService->generateProfitLossReport($filters);

            // Get filter options
            $customers = $this->profitLossService->getAvailableCustomers();
            $categories = $this->profitLossService->getAvailableCategories();

            return view('reports.profit_loss', compact('report', 'customers', 'categories', 'filters'));

        } catch (\Exception $e) {
            Log::error('Error generating P&L report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating Profit & Loss report. Please try again.');
        }
    }

    /**
     * Export P&L report to Excel
     */
    public function export(Request $request)
    {
        try {
            // Validate request parameters
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'customer_id' => 'nullable|exists:customers,id',
                'category' => 'nullable|string|max:255',
            ]);

            $filters = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'customer_id' => $request->input('customer_id'),
                'category' => $request->input('category'),
            ];

            // Generate report data
            $report = $this->profitLossService->generateProfitLossReport($filters);

            // Generate filename
            $startDate = $filters['start_date'] ? date('Y-m-d', strtotime($filters['start_date'])) : 'all';
            $endDate = $filters['end_date'] ? date('Y-m-d', strtotime($filters['end_date'])) : 'all';
            $filename = "profit_loss_report_{$startDate}_to_{$endDate}.xlsx";

            return Excel::download(new ProfitLossExport($report), $filename);

        } catch (\Exception $e) {
            Log::error('Error exporting P&L report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error exporting Profit & Loss report. Please try again.');
        }
    }

    /**
     * Get P&L data as JSON (for AJAX requests)
     */
    public function getData(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'customer_id' => 'nullable|exists:customers,id',
                'category' => 'nullable|string|max:255',
            ]);

            $filters = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'customer_id' => $request->input('customer_id'),
                'category' => $request->input('category'),
            ];

            $report = $this->profitLossService->generateProfitLossReport($filters);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting P&L data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating Profit & Loss data.'
            ], 500);
        }
    }

    /**
     * Get summary statistics for dashboard
     */
    public function getSummary(Request $request)
    {
        try {
            $filters = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ];

            $report = $this->profitLossService->generateProfitLossReport($filters);
            $summary = $report['summary'];

            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => $summary['total_revenue'],
                    'total_cogs' => $summary['total_cogs'],
                    'gross_profit' => $summary['gross_profit'],
                    'gross_profit_margin' => round($summary['gross_profit_margin'], 2),
                    'operating_expenses' => $summary['operating_expenses'],
                    'other_income' => $summary['other_income'],
                    'other_expenses' => $summary['other_expenses'],
                    'net_profit' => $summary['net_profit'],
                    'net_profit_margin' => round($summary['net_profit_margin'], 2),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting P&L summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating summary data.'
            ], 500);
        }
    }
}
