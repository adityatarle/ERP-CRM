<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateFileUpload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if request has file uploads
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Validate file size (max 10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'File size too large. Maximum allowed size is 10MB.'
                ], 422);
            }
            
            // Validate file type
            $allowedMimes = [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv',
                'application/csv',
                'text/plain'
            ];
            
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file type. Only Excel and CSV files are allowed.'
                ], 422);
            }
            
            // Validate file extension
            $allowedExtensions = ['xlsx', 'xls', 'csv'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file extension. Only .xlsx, .xls, and .csv files are allowed.'
                ], 422);
            }
        }

        return $next($request);
    }
}
