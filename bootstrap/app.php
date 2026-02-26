<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Proxies confiáveis — em produção o fly.toml define TRUSTED_PROXIES='*'
        // Headers explícitos para o Fly.io detectar HTTPS correctamente
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '127.0.0.1'),
            headers: Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_HOST |
                     Request::HEADER_X_FORWARDED_PORT |
                     Request::HEADER_X_FORWARDED_PROTO |
                     Request::HEADER_X_FORWARDED_PREFIX,
        );

        $middleware->web(append: [
            \App\Http\Middleware\CheckBanned::class,
        ]);

        $middleware->alias([
            'onboarding' => \App\Http\Middleware\EnsureOnboardingCompleted::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
