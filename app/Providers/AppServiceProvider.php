<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        $this->configureRateLimiting();
    }

    /**
     * Define os rate limiters da aplicação.
     *
     * Cada limiter é ajustado ao tipo de ação que protege:
     * - Criação de conteúdo: limite moderado para uso normal, evita spam.
     * - Ações sensíveis: limite baixo para operações de alto impacto.
     * - Denúncias: limite muito baixo para evitar abuso do sistema de moderação.
     */
    protected function configureRateLimiting(): void
    {
        // Criação de conteúdo no fórum (posts e comentários).
        RateLimiter::for('content-creation', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Ações do sistema de buddy (pedidos, candidaturas).
        RateLimiter::for('buddy-actions', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip());
        });

        // Envio de desafios e interações de gamificação.
        RateLimiter::for('gamification', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Denúncias de conteúdo — limite restrito para evitar abuso.
        RateLimiter::for('reports', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip());
        });

        // Sugestões e votações na biblioteca e zona calma.
        RateLimiter::for('suggestions', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Exportação de dados e ações de privacidade pesadas.
        RateLimiter::for('privacy-actions', function (Request $request) {
            return Limit::perHour(3)->by($request->user()?->id ?: $request->ip());
        });
    }
}
