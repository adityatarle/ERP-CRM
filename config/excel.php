<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Excel Export Settings
    |--------------------------------------------------------------------------
    */
    'exports' => [
        'extension' => 'xlsx',
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => false,
            'include_separator_line' => false,
            'excel_compatibility' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Excel Import Settings
    |--------------------------------------------------------------------------
    */
    'imports' => [
        'read_only' => true,
        'heading_row' => [
            'formatter' => 'slug',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary File Path
    |--------------------------------------------------------------------------
    */
    'temporary_files' => [
        'local_path' => storage_path('framework/laravel-excel'),
        'remote_disk' => null,
    ],
];