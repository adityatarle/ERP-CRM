<?php

namespace App\Repositories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SaleRepository extends BaseRepository
{
    public function __construct(Sale $model)
    {
        parent::__construct($model);
    }

    /**
     * Get sales with customer and items
     */
    public function getSalesWithRelations(): Collection
    {
        return $this->model->with(['customer', 'saleItems.product'])->get();
    }

    /**
     * Get sales by customer
     */
    public function getSalesByCustomer(int $customerId): Collection
    {
        return $this->model->with(['customer', 'saleItems.product'])
            ->where('customer_id', $customerId)
            ->get();
    }

    /**
     * Get sales by status
     */
    public function getSalesByStatus(string $status): Collection
    {
        return $this->model->with(['customer', 'saleItems.product'])
            ->where('status', $status)
            ->get();
    }

    /**
     * Get sales with date range
     */
    public function getSalesByDateRange(?string $startDate = null, ?string $endDate = null): Collection
    {
        return $this->getByDateRange('created_at', $startDate, $endDate);
    }

    /**
     * Get sales with pagination and filters
     */
    public function getSalesPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['customer', 'saleItems.product']);

        // Apply filters
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where('ref_no', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get sales statistics
     */
    public function getSalesStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $query = $this->model->newQuery();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalSales = $query->count();
        $totalRevenue = $query->sum('total_price');
        $averageSale = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Sales by status
        $salesByStatus = $query->groupBy('status')
            ->selectRaw('status, count(*) as count, sum(total_price) as total')
            ->get()
            ->keyBy('status');

        return [
            'total_sales' => $totalSales,
            'total_revenue' => $totalRevenue,
            'average_sale' => $averageSale,
            'sales_by_status' => $salesByStatus
        ];
    }

    /**
     * Get top customers by sales
     */
    public function getTopCustomers(int $limit = 5): Collection
    {
        return $this->model->with('customer')
            ->selectRaw('customer_id, count(*) as sales_count, sum(total_price) as total_revenue')
            ->groupBy('customer_id')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get sales by month
     */
    public function getSalesByMonth(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->model->newQuery();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count, sum(total_price) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get recent sales
     */
    public function getRecentSales(int $limit = 10): Collection
    {
        return $this->model->with(['customer', 'saleItems.product'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search sales
     */
    public function searchSales(string $searchTerm): Collection
    {
        return $this->search($searchTerm, ['ref_no']);
    }

    /**
     * Get sales summary for dashboard
     */
    public function getSalesSummary(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        return [
            'today' => [
                'count' => $this->model->whereDate('created_at', $today)->count(),
                'revenue' => $this->model->whereDate('created_at', $today)->sum('total_price')
            ],
            'this_month' => [
                'count' => $this->model->where('created_at', '>=', $thisMonth)->count(),
                'revenue' => $this->model->where('created_at', '>=', $thisMonth)->sum('total_price')
            ],
            'last_month' => [
                'count' => $this->model->whereBetween('created_at', [$lastMonth, $thisMonth])->count(),
                'revenue' => $this->model->whereBetween('created_at', [$lastMonth, $thisMonth])->sum('total_price')
            ]
        ];
    }
}
