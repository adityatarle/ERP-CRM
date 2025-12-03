<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FlexibleSecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if CSP should be disabled (for development)
        $disableCSP = config('app.debug') && env('DISABLE_CSP', false);

        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Content Security Policy - Only apply if not disabled
        if (!$disableCSP) {
            $csp = $this->buildCSP();
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }

    /**
     * Build Content Security Policy based on configuration
     */
    private function buildCSP(): string
    {
        $cspConfig = config('security.headers.content_security_policy', []);
        
        if (empty($cspConfig)) {
            // Default CSP that works with common CDNs - More permissive for fonts
            return "default-src 'self'; " .
                   "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com https://stackpath.bootstrapcdn.com; " .
                   "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://stackpath.bootstrapcdn.com; " .
                   "font-src 'self' data: https:; " .
                   "img-src 'self' data: https:; " .
                   "connect-src 'self' https:; " .
                   "frame-ancestors 'none';";
        }

        return implode('; ', $cspConfig);
    }
}
