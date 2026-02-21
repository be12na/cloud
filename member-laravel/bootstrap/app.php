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
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        // Redirect unauthenticated users to member login
        $middleware->redirectGuestsTo('/member/login');
        // Redirect authenticated users away from guest-only routes
        $middleware->redirectUsersTo('/member/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
