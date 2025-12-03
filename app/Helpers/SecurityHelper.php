<?php

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Escape output to prevent XSS attacks
     */
    public static function escape($value): string
    {
        if (is_null($value)) {
            return '';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
    }

    /**
     * Escape output for HTML attributes
     */
    public static function escapeAttribute($value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
    }

    /**
     * Escape output for JavaScript
     */
    public static function escapeJs($value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Escape output for CSS
     */
    public static function escapeCss($value): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', $value);
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename($filename): string
    {
        // Remove any character that isn't alphanumeric, dash, underscore, or dot
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
        
        // Remove multiple dots
        $filename = preg_replace('/\.+/', '.', $filename);
        
        // Remove leading/trailing dots
        $filename = trim($filename, '.');
        
        return $filename;
    }

    /**
     * Sanitize HTML content (allow only safe tags)
     */
    public static function sanitizeHtml($html): string
    {
        $allowedTags = '<p><br><strong><em><u><ol><ul><li><h1><h2><h3><h4><h5><h6>';
        
        return strip_tags($html, $allowedTags);
    }

    /**
     * Generate secure random token
     */
    public static function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Validate and sanitize email
     */
    public static function sanitizeEmail($email): ?string
    {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        
        return null;
    }

    /**
     * Validate and sanitize URL
     */
    public static function sanitizeUrl($url): ?string
    {
        $url = filter_var(trim($url), FILTER_SANITIZE_URL);
        
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        
        return null;
    }

    /**
     * Sanitize phone number
     */
    public static function sanitizePhone($phone): string
    {
        // Remove all non-numeric characters except + at the beginning
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Ensure + is only at the beginning
        if (strpos($phone, '+') !== 0 && strpos($phone, '+') !== false) {
            $phone = '+' . preg_replace('/[^0-9]/', '', $phone);
        }
        
        return $phone;
    }

    /**
     * Check if string contains potentially malicious content
     */
    public static function isMalicious($string): bool
    {
        $maliciousPatterns = [
            '/<script[^>]*>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i',
            '/<link[^>]*>/i',
            '/<meta[^>]*>/i',
            '/<style[^>]*>/i',
            '/expression\s*\(/i',
            '/url\s*\(/i',
            '/@import/i',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $string)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log security event
     */
    public static function logSecurityEvent(string $event, array $data = []): void
    {
        \Log::warning('Security Event: ' . $event, array_merge($data, [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
        ]));
    }
}
