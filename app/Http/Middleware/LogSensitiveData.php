<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogSensitiveData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Remove sensitive data from logs
        $requestData = $request->all();
        
        // Fields to mask in logs
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'gst_number',
            'phone',
            'email',
            'credit_card',
            'ssn',
            'api_key',
            'token'
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($requestData[$field])) {
                $requestData[$field] = '[REDACTED]';
            }
        }
        
        // Log request without sensitive data
        Log::info('Request received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data' => $requestData
        ]);

        return $next($request);
    }
}
