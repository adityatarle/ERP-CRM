<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | application including password policies, session security, and
    | other security measures.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_symbols' => env('PASSWORD_REQUIRE_SYMBOLS', true),
        'max_age_days' => env('PASSWORD_MAX_AGE_DAYS', 90),
        'prevent_reuse_count' => env('PASSWORD_PREVENT_REUSE_COUNT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 480), // 8 hours
        'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', true),
        'encrypt' => env('SESSION_ENCRYPT', true),
        'secure' => env('SESSION_SECURE_COOKIE', false), // Set to true in production with HTTPS
        'http_only' => env('SESSION_HTTP_ONLY', true),
        'same_site' => env('SESSION_SAME_SITE', 'strict'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */
    'file_upload' => [
        'max_size' => env('FILE_UPLOAD_MAX_SIZE', 10240), // 10MB in KB
        'allowed_types' => [
            'import' => ['xlsx', 'xls', 'csv'],
            'document' => ['pdf', 'doc', 'docx'],
            'image' => ['jpg', 'jpeg', 'png', 'gif'],
        ],
        'scan_for_malware' => env('FILE_UPLOAD_SCAN_MALWARE', false),
        'quarantine_suspicious' => env('FILE_UPLOAD_QUARANTINE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'login_attempts' => env('RATE_LIMIT_LOGIN', 5), // per minute
        'api_requests' => env('RATE_LIMIT_API', 60), // per minute
        'file_uploads' => env('RATE_LIMIT_UPLOAD', 10), // per minute
        'password_reset' => env('RATE_LIMIT_PASSWORD_RESET', 3), // per hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'x_content_type_options' => 'nosniff',
        'x_frame_options' => 'DENY',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
        'content_security_policy' => [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com",
            "img-src 'self' data: https:",
            "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "frame-ancestors 'none'",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging and Monitoring
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'log_failed_logins' => env('LOG_FAILED_LOGINS', true),
        'log_suspicious_activity' => env('LOG_SUSPICIOUS_ACTIVITY', true),
        'log_file_uploads' => env('LOG_FILE_UPLOADS', true),
        'log_admin_actions' => env('LOG_ADMIN_ACTIONS', true),
        'retention_days' => env('SECURITY_LOG_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelisting
    |--------------------------------------------------------------------------
    */
    'ip_whitelist' => [
        'enabled' => env('IP_WHITELIST_ENABLED', false),
        'admin_ips' => explode(',', env('ADMIN_IP_WHITELIST', '')),
        'api_ips' => explode(',', env('API_IP_WHITELIST', '')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    */
    'two_factor' => [
        'enabled' => env('TWO_FACTOR_ENABLED', false),
        'required_for_admin' => env('TWO_FACTOR_REQUIRED_ADMIN', true),
        'issuer' => env('TWO_FACTOR_ISSUER', 'Mauli CRM'),
        'window' => env('TWO_FACTOR_WINDOW', 1), // Time window for TOTP
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Encryption
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'encrypt_sensitive_fields' => env('ENCRYPT_SENSITIVE_FIELDS', true),
        'sensitive_fields' => [
            'gst_number',
            'phone',
            'email',
            'address',
            'bank_details',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Security
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'encrypt' => env('BACKUP_ENCRYPT', true),
        'password_protect' => env('BACKUP_PASSWORD_PROTECT', true),
        'secure_storage' => env('BACKUP_SECURE_STORAGE', true),
    ],
];
