<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // ADD THIS ONE LINE TO FIX THE REDIRECTION
        $middleware->redirectUsersTo('/');

        // Global middleware - Temporarily using NoCSPHeaders to fix CSP issues
        $middleware->append(\App\Http\Middleware\NoCSPHeaders::class);
        $middleware->append(\App\Http\Middleware\LogSensitiveData::class);

        // Your existing middleware alias
        $middleware->alias([
            'superadmin' => \App\Http\Middleware\Superadmin::class,
            'validate.file.upload' => \App\Http\Middleware\ValidateFileUpload::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();