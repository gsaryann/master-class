<?php

use App\Http\Middleware\EnsureSessionGuest;
use App\Http\Middleware\EnsureSessionMaster;
use App\Http\Middleware\EnsureSessionUser;
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
            'session.guest' => EnsureSessionGuest::class,
            'session.user' => EnsureSessionUser::class,
            'session.master' => EnsureSessionMaster::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
