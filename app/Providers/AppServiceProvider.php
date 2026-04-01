<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\DailyLog;
use App\Models\Message;
use App\Models\Post;
use App\Models\SelfAssessment;
use App\Models\VaultItem;
use App\Policies\CommentPolicy;
use App\Policies\DailyLogPolicy;
use App\Policies\MessagePolicy;
use App\Policies\PostPolicy;
use App\Policies\SelfAssessmentPolicy;
use App\Policies\VaultItemPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
     * Inicializa serviços globais: HTTPS, políticas de password e rate limiting.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Política de passwords forte aplicada globalmente via Password::defaults().
        // Garante consistência entre registo, reset e alteração de password
        // sem depender de cada controller definir regras individualmente.
        Password::defaults(function () {
            return Password::min(8)->mixedCase()->numbers()->symbols();
        });

        $this->configureRateLimiting();
        $this->configurePolicies();
    }

    /**
     * Registo explícito de policies para os models com dados sensíveis.
     * Embora o Laravel 12 faça auto-discovery, o registo explícito documenta
     * a intenção e previne regressões silenciosas em refactors futuros.
     */
    protected function configurePolicies(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
        Gate::policy(DailyLog::class, DailyLogPolicy::class);
        Gate::policy(VaultItem::class, VaultItemPolicy::class);
        Gate::policy(SelfAssessment::class, SelfAssessmentPolicy::class);
    }

    /**
     * Rate limiters calibrados por tipo de ação:
     * - Conteúdo: moderado (uso normal sem spam).
     * - Operações sensíveis: restrito (reset de password, denúncias).
     * - Privacidade: muito restrito (exportação GDPR).
     */
    protected function configureRateLimiting(): void
    {
        // Limiter padrão para todas as rotas API autenticadas.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Recuperação de password — 1 pedido/minuto para mitigar enumeração de emails.
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(1)->by($request->input('email', $request->ip()));
        });

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

        // Pedidos à API de IA (OpenAI) — limite conservador por utilizador para
        // evitar custos descontrolados e abuso de prompt injection.
        RateLimiter::for('ai-actions', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
