<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        // For API routes that expect JSON, return null (no redirect) when guest
        // For web routes, redirect to login
        $middleware->redirectGuestsTo(fn ($request) => 
            $request->expectsJson() ? null : '/'
        );
        
        // Disable CSRF protection for API routes
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'sanctum/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
