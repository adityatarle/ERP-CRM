<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Exception;

class ErrorHandlingService
{
    /**
     * Handle and log exceptions consistently
     */
    public function handleException(Exception $exception, string $context = 'application'): array
    {
        $errorData = [
            'context' => $context,
            'exception_type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ];

        // Log based on exception type
        $this->logException($exception, $errorData);

        // Return user-friendly error response
        return $this->formatErrorResponse($exception);
    }

    /**
     * Log exception with appropriate level
     */
    private function logException(Exception $exception, array $errorData): void
    {
        $logLevel = $this->getLogLevel($exception);
        
        Log::{$logLevel}("Exception in {$errorData['context']}: {$exception->getMessage()}", $errorData);
    }

    /**
     * Determine log level based on exception type
     */
    private function getLogLevel(Exception $exception): string
    {
        if ($exception instanceof ValidationException) {
            return 'warning';
        }
        
        if ($exception instanceof AuthenticationException || 
            $exception instanceof AuthorizationException) {
            return 'warning';
        }
        
        if ($exception instanceof NotFoundHttpException || 
            $exception instanceof MethodNotAllowedHttpException) {
            return 'info';
        }
        
        if ($exception instanceof QueryException) {
            return 'error';
        }
        
        return 'error';
    }

    /**
     * Format error response for user
     */
    private function formatErrorResponse(Exception $exception): array
    {
        if ($exception instanceof ValidationException) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
                'status_code' => 422
            ];
        }
        
        if ($exception instanceof AuthenticationException) {
            return [
                'success' => false,
                'message' => 'Authentication required',
                'status_code' => 401
            ];
        }
        
        if ($exception instanceof AuthorizationException) {
            return [
                'success' => false,
                'message' => 'Access denied',
                'status_code' => 403
            ];
        }
        
        if ($exception instanceof NotFoundHttpException) {
            return [
                'success' => false,
                'message' => 'Resource not found',
                'status_code' => 404
            ];
        }
        
        if ($exception instanceof MethodNotAllowedHttpException) {
            return [
                'success' => false,
                'message' => 'Method not allowed',
                'status_code' => 405
            ];
        }
        
        if ($exception instanceof QueryException) {
            return [
                'success' => false,
                'message' => 'Database error occurred',
                'status_code' => 500
            ];
        }
        
        // Generic error
        return [
            'success' => false,
            'message' => 'An unexpected error occurred',
            'status_code' => 500
        ];
    }

    /**
     * Handle validation errors
     */
    public function handleValidationError(ValidationException $exception): array
    {
        $errors = $exception->errors();
        $formattedErrors = [];
        
        foreach ($errors as $field => $messages) {
            $formattedErrors[$field] = is_array($messages) ? $messages[0] : $messages;
        }
        
        return [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $formattedErrors,
            'status_code' => 422
        ];
    }

    /**
     * Handle database errors
     */
    public function handleDatabaseError(QueryException $exception): array
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();
        
        // Handle specific database errors
        switch ($errorCode) {
            case 23000: // Integrity constraint violation
                return [
                    'success' => false,
                    'message' => 'Data integrity violation. The record cannot be saved.',
                    'status_code' => 409
                ];
                
            case 42000: // General database error
                if (strpos($errorMessage, 'Table') !== false) {
                    return [
                        'success' => false,
                        'message' => 'Database table not found',
                        'status_code' => 500
                    ];
                }
                
                if (strpos($errorMessage, 'Column') !== false) {
                    return [
                        'success' => false,
                        'message' => 'Database column not found',
                        'status_code' => 500
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Database error occurred',
                    'status_code' => 500
                ];
                
            default:
                return [
                    'success' => false,
                    'message' => 'Database error occurred',
                    'status_code' => 500
                ];
        }
    }

    /**
     * Handle file upload errors
     */
    public function handleFileUploadError(Exception $exception): array
    {
        if (strpos($exception->getMessage(), 'file too large') !== false) {
            return [
                'success' => false,
                'message' => 'File is too large. Please choose a smaller file.',
                'status_code' => 413
            ];
        }
        
        if (strpos($exception->getMessage(), 'invalid file type') !== false) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Please choose a valid file.',
                'status_code' => 415
            ];
        }
        
        return [
            'success' => false,
            'message' => 'File upload failed',
            'status_code' => 500
        ];
    }

    /**
     * Handle business logic errors
     */
    public function handleBusinessLogicError(string $message, array $context = []): array
    {
        Log::warning('Business logic error', array_merge([
            'message' => $message,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ], $context));
        
        return [
            'success' => false,
            'message' => $message,
            'status_code' => 400
        ];
    }

    /**
     * Handle stock validation errors
     */
    public function handleStockError(string $productName, int $available, int $required): array
    {
        return [
            'success' => false,
            'message' => "Insufficient stock for {$productName}",
            'errors' => [
                'stock' => "Available: {$available}, Required: {$required}"
            ],
            'status_code' => 422
        ];
    }

    /**
     * Handle permission errors
     */
    public function handlePermissionError(string $action = 'perform this action'): array
    {
        return [
            'success' => false,
            'message' => "You don't have permission to {$action}",
            'status_code' => 403
        ];
    }

    /**
     * Handle rate limiting errors
     */
    public function handleRateLimitError(int $retryAfter = 60): array
    {
        return [
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => $retryAfter,
            'status_code' => 429
        ];
    }

    /**
     * Create a standardized error response
     */
    public function createErrorResponse(string $message, int $statusCode = 400, array $errors = []): array
    {
        $response = [
            'success' => false,
            'message' => $message,
            'status_code' => $statusCode
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return $response;
    }

    /**
     * Log error for monitoring
     */
    public function logError(string $message, array $context = [], string $level = 'error'): void
    {
        $logData = array_merge([
            'message' => $message,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ], $context);
        
        Log::{$level}($message, $logData);
    }
}
