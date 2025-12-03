<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseService
{
    /**
     * Execute operation in database transaction
     */
    protected function executeInTransaction(callable $callback, string $operation = 'operation')
    {
        DB::beginTransaction();
        
        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Service operation failed: {$operation}", [
                'operation' => $operation,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Log service operation
     */
    protected function logOperation(string $operation, array $data = [], string $level = 'info'): void
    {
        $logData = array_merge([
            'service' => static::class,
            'operation' => $operation,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ], $data);

        Log::{$level}("Service Operation: {$operation}", $logData);
    }

    /**
     * Validate required parameters
     */
    protected function validateRequired(array $data, array $required): void
    {
        $missing = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception('Missing required fields: ' . implode(', ', $missing));
        }
    }

    /**
     * Calculate total with discount
     */
    protected function calculateTotalWithDiscount(float $unitPrice, int $quantity, float $discount = 0): float
    {
        $baseTotal = $unitPrice * $quantity;
        return $baseTotal * (1 - ($discount / 100));
    }

    /**
     * Calculate GST amount
     */
    protected function calculateGST(float $amount, float $gstRate): array
    {
        $gstAmount = ($amount * $gstRate) / 100;
        
        return [
            'gst_amount' => $gstAmount,
            'total_with_gst' => $amount + $gstAmount
        ];
    }

    /**
     * Calculate CGST and SGST
     */
    protected function calculateCGSTSGST(float $amount, float $gstRate): array
    {
        $cgstRate = $gstRate / 2;
        $sgstRate = $gstRate / 2;
        
        $cgstAmount = ($amount * $cgstRate) / 100;
        $sgstAmount = ($amount * $sgstRate) / 100;
        
        return [
            'cgst_rate' => $cgstRate,
            'sgst_rate' => $sgstRate,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'total_with_gst' => $amount + $cgstAmount + $sgstAmount
        ];
    }

    /**
     * Format currency
     */
    protected function formatCurrency(float $amount, string $currency = '₹'): string
    {
        return $currency . number_format($amount, 2);
    }

    /**
     * Calculate percentage
     */
    protected function calculatePercentage(float $part, float $total): float
    {
        return $total > 0 ? ($part / $total) * 100 : 0;
    }

    /**
     * Generate unique reference number
     */
    protected function generateReferenceNumber(string $prefix = 'REF'): string
    {
        return $prefix . '-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Validate date range
     */
    protected function validateDateRange(?string $startDate, ?string $endDate): array
    {
        if ($startDate && $endDate && strtotime($startDate) > strtotime($endDate)) {
            throw new Exception('Start date cannot be greater than end date');
        }

        return [
            'start_date' => $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : null,
            'end_date' => $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : null
        ];
    }

    /**
     * Build search query
     */
    protected function buildSearchQuery($query, string $searchTerm, array $searchFields): void
    {
        if (empty($searchTerm)) {
            return;
        }

        $query->where(function ($q) use ($searchTerm, $searchFields) {
            foreach ($searchFields as $field) {
                $q->orWhere($field, 'like', '%' . $searchTerm . '%');
            }
        });
    }

    /**
     * Apply date filters to query
     */
    protected function applyDateFilters($query, array $dateRange, string $dateField = 'created_at'): void
    {
        if ($dateRange['start_date'] && $dateRange['end_date']) {
            $query->whereBetween($dateField, [$dateRange['start_date'], $dateRange['end_date']]);
        } elseif ($dateRange['start_date']) {
            $query->where($dateField, '>=', $dateRange['start_date']);
        } elseif ($dateRange['end_date']) {
            $query->where($dateField, '<=', $dateRange['end_date']);
        }
    }

    /**
     * Apply sorting to query
     */
    protected function applySorting($query, string $sortBy = 'created_at', string $sortDirection = 'desc'): void
    {
        $allowedSortFields = ['created_at', 'updated_at', 'name', 'date', 'amount'];
        $allowedDirections = ['asc', 'desc'];

        if (in_array($sortBy, $allowedSortFields) && in_array($sortDirection, $allowedDirections)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Handle pagination
     */
    protected function paginateResults($query, int $perPage = 15)
    {
        return $query->paginate($perPage);
    }

    /**
     * Get summary statistics
     */
    protected function getSummaryStats($query, string $amountField = 'amount'): array
    {
        return [
            'total_count' => $query->count(),
            'total_amount' => $query->sum($amountField),
            'average_amount' => $query->avg($amountField),
            'min_amount' => $query->min($amountField),
            'max_amount' => $query->max($amountField)
        ];
    }
}
