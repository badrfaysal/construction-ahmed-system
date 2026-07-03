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
    ->withMiddleware(function (Middleware $middleware): void {
        // Register the 'role' alias so routes can use ->middleware('role:admin'),
        // and 'no.viewer' to block read-only accounts from any write request.
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'no.viewer' => \App\Http\Middleware\BlockViewerWrites::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
