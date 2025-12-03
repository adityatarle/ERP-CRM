<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

abstract class BaseController extends Controller
{
    /**
     * Handle API responses consistently
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Handle error responses consistently
     */
    protected function errorResponse(string $message = 'An error occurred', $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Handle validation errors consistently
     */
    protected function validationErrorResponse(ValidationException $e): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            $e->errors(),
            422
        );
    }

    /**
     * Handle database transaction with error handling
     */
    protected function executeInTransaction(callable $callback, string $operation = 'operation')
    {
        DB::beginTransaction();
        
        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Database transaction failed for {$operation}: " . $e->getMessage(), [
                'operation' => $operation,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle AJAX requests consistently
     */
    protected function handleAjaxRequest(Request $request, callable $callback)
    {
        try {
            $result = $callback();
            
            if ($request->ajax() || $request->wantsJson()) {
                return $this->successResponse($result);
            }
            
            return $result;
        } catch (ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return $this->validationErrorResponse($e);
            }
            throw $e;
        } catch (Exception $e) {
            Log::error('AJAX request failed: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return $this->errorResponse('An error occurred while processing your request');
            }
            
            throw $e;
        }
    }

    /**
     * Log operation for audit trail
     */
    protected function logOperation(string $operation, array $data = [], string $level = 'info'): void
    {
        $logData = array_merge([
            'operation' => $operation,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ], $data);

        Log::{$level}("Operation: {$operation}", $logData);
    }

    /**
     * Get pagination parameters from request
     */
    protected function getPaginationParams(Request $request): array
    {
        return [
            'per_page' => $request->get('per_page', 15),
            'page' => $request->get('page', 1)
        ];
    }

    /**
     * Get filter parameters from request
     */
    protected function getFilterParams(Request $request, array $allowedFilters = []): array
    {
        $filters = [];
        
        foreach ($allowedFilters as $filter) {
            if ($request->has($filter)) {
                $filters[$filter] = $request->get($filter);
            }
        }
        
        return $filters;
    }

    /**
     * Handle file upload with validation
     */
    protected function handleFileUpload(Request $request, string $fieldName, array $allowedTypes = [], int $maxSize = 10240): array
    {
        $request->validate([
            $fieldName => [
                'required',
                'file',
                'max:' . $maxSize,
                function ($attribute, $value, $fail) use ($allowedTypes) {
                    if (!empty($allowedTypes) && !in_array($value->getClientOriginalExtension(), $allowedTypes)) {
                        $fail('The ' . $attribute . ' must be one of: ' . implode(', ', $allowedTypes));
                    }
                }
            ]
        ]);

        $file = $request->file($fieldName);
        
        return [
            'file' => $file,
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension()
        ];
    }

    /**
     * Format currency for display
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
     * Validate date range
     */
    protected function validateDateRange(Request $request): array
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        return [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date')
        ];
    }

    /**
     * Get search parameters
     */
    protected function getSearchParams(Request $request): array
    {
        return [
            'search' => $request->get('search', ''),
            'sort_by' => $request->get('sort_by', 'created_at'),
            'sort_direction' => $request->get('sort_direction', 'desc')
        ];
    }
}
