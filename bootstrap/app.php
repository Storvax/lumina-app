<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
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
        // Converter exceções em JSON para requisições de API
        $exceptions->shouldRenderJsonWhen(function ($request) {
            return $request->is('api/*');
        });

        // Registar um handler customizado para API
        $exceptions->renderable(function (\Throwable $exception, $request) {
            if ($request->is('api/*')) {
                // Validação — 422
                if ($exception instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'message' => 'Erros de validação.',
                        'errors' => $exception->errors(),
                    ], 422);
                }

                // Não autenticado — 401
                if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'message' => 'Não autenticado. Por favor, faça login.',
                    ], 401);
                }

                // Autorização — 403
                if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return response()->json([
                        'message' => 'Não tem permissão para aceder a este recurso.',
                    ], 403);
                }

                // Recurso não encontrado — 404
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->json([
                        'message' => 'Recurso não encontrado.',
                    ], 404);
                }
            }
        });
    })->create();
