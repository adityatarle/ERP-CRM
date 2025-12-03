<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class FileUploadService
{
    /**
     * Allowed file types for different operations
     */
    private const ALLOWED_TYPES = [
        'import' => [
            'mimes' => ['xlsx', 'xls', 'csv'],
            'max_size' => 10 * 1024 * 1024, // 10MB
        ],
        'document' => [
            'mimes' => ['pdf', 'doc', 'docx'],
            'max_size' => 5 * 1024 * 1024, // 5MB
        ],
        'image' => [
            'mimes' => ['jpg', 'jpeg', 'png', 'gif'],
            'max_size' => 2 * 1024 * 1024, // 2MB
        ],
    ];

    /**
     * Upload a file securely
     */
    public function upload(UploadedFile $file, string $type = 'import', string $directory = 'uploads'): array
    {
        $this->validateFile($file, $type);
        
        // Generate secure filename
        $extension = $file->getClientOriginalExtension();
        $filename = $this->generateSecureFilename($extension);
        
        // Store file in private directory
        $path = $file->storeAs($directory, $filename, 'private');
        
        return [
            'filename' => $filename,
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    /**
     * Validate file before upload
     */
    private function validateFile(UploadedFile $file, string $type): void
    {
        if (!isset(self::ALLOWED_TYPES[$type])) {
            throw new Exception("Invalid file type: {$type}");
        }

        $allowedTypes = self::ALLOWED_TYPES[$type];
        
        // Check file size
        if ($file->getSize() > $allowedTypes['max_size']) {
            throw new Exception("File size exceeds maximum allowed size of " . $this->formatBytes($allowedTypes['max_size']));
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedTypes['mimes'])) {
            throw new Exception("Invalid file type. Allowed types: " . implode(', ', $allowedTypes['mimes']));
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        $allowedMimes = $this->getAllowedMimes($allowedTypes['mimes']);
        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception("Invalid file MIME type: {$mimeType}");
        }

        // Additional security checks
        $this->performSecurityChecks($file);
    }

    /**
     * Generate secure filename
     */
    private function generateSecureFilename(string $extension): string
    {
        return Str::random(40) . '.' . $extension;
    }

    /**
     * Get allowed MIME types for file extensions
     */
    private function getAllowedMimes(array $extensions): array
    {
        $mimeMap = [
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'csv' => ['text/csv', 'application/csv', 'text/plain'],
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];

        $mimes = [];
        foreach ($extensions as $ext) {
            if (isset($mimeMap[$ext])) {
                $mime = $mimeMap[$ext];
                if (is_array($mime)) {
                    $mimes = array_merge($mimes, $mime);
                } else {
                    $mimes[] = $mime;
                }
            }
        }

        return array_unique($mimes);
    }

    /**
     * Perform additional security checks
     */
    private function performSecurityChecks(UploadedFile $file): void
    {
        // Check for malicious file content
        $content = file_get_contents($file->getPathname());
        
        // Check for PHP tags
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            throw new Exception("File contains potentially malicious content");
        }

        // Check for script tags
        if (preg_match('/<script[^>]*>/i', $content)) {
            throw new Exception("File contains script tags");
        }

        // Check file header for consistency
        $this->validateFileHeader($file, $content);
    }

    /**
     * Validate file header
     */
    private function validateFileHeader(UploadedFile $file, string $content): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $header = substr($content, 0, 10);
        
        $validHeaders = [
            'xlsx' => "\x50\x4B\x03\x04", // ZIP header (XLSX is a ZIP file)
            'xls' => "\xD0\xCF\x11\xE0", // OLE header
            'csv' => '', // CSV doesn't have a specific header
            'pdf' => "%PDF-",
            'jpg' => "\xFF\xD8\xFF",
            'jpeg' => "\xFF\xD8\xFF",
            'png' => "\x89\x50\x4E\x47",
            'gif' => "GIF87a" or "GIF89a",
        ];

        if (isset($validHeaders[$extension]) && $validHeaders[$extension] !== '') {
            if (strpos($header, $validHeaders[$extension]) !== 0) {
                throw new Exception("File header doesn't match expected format for {$extension} files");
            }
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Delete uploaded file
     */
    public function delete(string $path): bool
    {
        return Storage::disk('private')->delete($path);
    }

    /**
     * Get file URL for download
     */
    public function getDownloadUrl(string $path): string
    {
        return Storage::disk('private')->url($path);
    }
}
