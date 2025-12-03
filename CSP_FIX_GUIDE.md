# Content Security Policy (CSP) Fix Guide

## Issue Description
The application was getting CSP errors because the security headers middleware was blocking external CDN resources that your application depends on.

## Errors Fixed
1. **Font loading errors**: `font-src 'self'` was too restrictive
2. **jQuery loading errors**: `script-src` didn't include `https://code.jquery.com`
3. **Bootstrap source map errors**: `connect-src` was too restrictive
4. **jQuery dependencies**: Various jQuery plugins couldn't load

## Solutions Implemented

### 1. Updated CSP Directives
The Content Security Policy has been updated to allow the necessary external resources:

```php
// Updated CSP in FlexibleSecurityHeaders.php
"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com https://stackpath.bootstrapcdn.com"
"style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://stackpath.bootstrapcdn.com"
"font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com"
"connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com"
```

### 2. Flexible CSP Configuration
Created `FlexibleSecurityHeaders` middleware that:
- Allows CSP to be disabled in development
- Uses configuration-based CSP rules
- Provides sensible defaults for common CDNs

### 3. Development Override
You can temporarily disable CSP during development by adding to your `.env` file:

```env
DISABLE_CSP=true
```

**Note**: Only works when `APP_DEBUG=true`

## Files Modified

1. **`app/Http/Middleware/SecurityHeaders.php`** - Updated CSP directives
2. **`app/Http/Middleware/FlexibleSecurityHeaders.php`** - New flexible CSP middleware
3. **`bootstrap/app.php`** - Updated to use flexible middleware
4. **`config/security.php`** - Updated CSP configuration

## Testing the Fix

### 1. Clear Application Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 2. Test the Application
- Load the payables page
- Check browser console for CSP errors
- Verify jQuery and other scripts load properly

### 3. If Issues Persist
Temporarily disable CSP by adding to `.env`:
```env
DISABLE_CSP=true
```

## Production Considerations

### 1. Review External Dependencies
Consider moving critical JavaScript libraries to local files for better security:

```bash
# Download jQuery locally
wget https://code.jquery.com/jquery-3.4.1.min.js -O public/assets/js/jquery.min.js
```

### 2. Update CSP for Local Files
If you move to local files, update the CSP:

```php
"script-src 'self' 'unsafe-inline' 'unsafe-eval'"
```

### 3. Monitor CSP Violations
Add CSP reporting to monitor violations:

```php
"report-uri /csp-report"
```

## Alternative: Disable CSP Temporarily

If you need to disable CSP completely for now, you can:

### Option 1: Environment Variable
Add to `.env`:
```env
DISABLE_CSP=true
```

### Option 2: Comment Out Middleware
In `bootstrap/app.php`, comment out the security headers middleware:

```php
// $middleware->append(\App\Http\Middleware\FlexibleSecurityHeaders::class);
```

### Option 3: Remove CSP from Middleware
In `FlexibleSecurityHeaders.php`, comment out the CSP line:

```php
// $response->headers->set('Content-Security-Policy', $csp);
```

## Recommended Next Steps

1. **Test the application** - Verify all functionality works
2. **Review external dependencies** - Consider localizing critical libraries
3. **Monitor CSP violations** - Check browser console for any remaining issues
4. **Gradually tighten CSP** - Remove unnecessary permissions over time

## Security Note

While the updated CSP is more permissive, it still provides significant security benefits:
- Prevents inline script execution (except where explicitly allowed)
- Blocks unauthorized external resources
- Protects against clickjacking
- Prevents MIME type sniffing

The current configuration balances security with functionality for your application's needs.
