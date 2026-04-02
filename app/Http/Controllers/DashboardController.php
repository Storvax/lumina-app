<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Services\Gamification\GamificationService;
use App\Services\Recommendation\RecommendationService;
use App\Models\DailyLog;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\Comment;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Renderiza o dashboard principal do utilizador.
     *
     * Recolhe dados contextuais (progresso, estado emocional, marcos)
     * para que a view possa apresentar uma experiência personalizada
     * e terapeuticamente relevante.
     */
    public function index(GamificationService $gamification)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // Garantir que as missões diárias estão atribuidas
        $gamification->assignDailyMissions($user);

        // Missões de hoje com progresso do utilizador (cache 10 min por user/dia)
        $dailyMissions = Cache::remember(
            "user:{$user->id}:missions:{$today}",
            600,
            fn () => $user->missions()->wherePivot('assigned_date', $today)->get()
        );

        // Verificar se o utilizador já fez o registo emocional de hoje (cache 5 min)
        $todayLog = Cache::remember(
            "user:{$user->id}:log:{$today}",
            300,
            fn () => DailyLog::where('user_id', $user->id)->where('log_date', $today)->first()
        );

        // Dados de progresso pessoal para o painel resumo
        $progressData = [
            'flames'         => $user->flames ?? 0,
            'streak'         => $user->current_streak ?? 0,
            'level'          => $user->bonfire_level ?? 'spark',
            'todayLogged'    => !is_null($todayLog),
            'todayMoodLevel' => $todayLog?->mood_level,
        ];

        // Saudacao contextual baseada na hora do dia
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour >= 5 && $hour < 12  => 'Bom dia',
            $hour >= 12 && $hour < 18 => 'Boa tarde',
            default                   => 'Boa noite',
        };

        // Marco pendente real (streak atingiu um marco celebravel)
        $pendingMilestone = $this->detectPendingMilestone($user);

        // Tags emocionais actuais para adaptar sugestoes contextuais
        $emotionalTags = $user->emotional_tags ?? [];

        // Frases de encorajamento rotativas e nao repetitivas
        $encouragement = $this->getEncouragementPhrase($user, $progressData);

        // Calendário emocional: mensagem contextual se a data de hoje for relevante
        $emotionalDate = config('emotional-calendar.' . now()->format('m-d'));

        // Recomendações contextuais (GAP-18)
        $recommendations = app(RecommendationService::class)->getRecommendations($user);

        $communityStats = Cache::remember('community:stats', 900, function () {
            $weekStart = now()->subDays(7);
            $current = Comment::where('created_at', '>=', $weekStart)->count()
                     + PostReaction::where('created_at', '>=', $weekStart)->count();
            $target = 1000;

            return [
                'current' => $current,
                'target' => $target,
                'percentage' => min(100, round(($current / max($target, 1)) * 100)),
            ];
        });

        $globalImpact = Cache::remember('community:impact', 3600, function () {
            $currentFlames = (int) User::sum('flames');
            $targetFlames = 10000;

            return [
                'current_flames' => $currentFlames,
                'target_flames' => $targetFlames,
                'percentage' => min(100, round(($currentFlames / max($targetFlames, 1)) * 100)),
                'goal_title' => 'Plantar 1 Árvore na Serra da Estrela',
                'ngo_name' => 'Associação Plantar Uma Árvore',
                'participants_count' => User::where('flames', '>', 0)->count(),
                'message' => 'Quando atingirmos a meta coletiva, faremos uma doação em nome de todos.',
            ];
        });

        // Insight IA personalizado — só disponível com >= 3 registos nos últimos 7 dias
        // para garantir contexto emocional suficiente para uma análise relevante
        $aiInsight = $this->getAiInsight($user);

        return view('dashboard', compact(
            'dailyMissions',
            'progressData',
            'greeting',
            'pendingMilestone',
            'emotionalTags',
            'encouragement',
            'todayLog',
            'emotionalDate',
            'recommendations',
            'communityStats',
            'globalImpact',
            'aiInsight'
        ));
    }

    /**
     * Detecta se o utilizador atingiu um marco celebravel recente
     * que ainda nao foi partilhado na fogueira.
     *
     * Marcos: 3, 7, 14, 30, 60, 100 dias de streak.
     * Retorna null se nao houver nenhum marco pendente.
     */
    private function detectPendingMilestone($user): ?array
    {
        $streak = $user->current_streak ?? 0;
        $milestoneThresholds = [
            3   => ['title' => '3 Dias de Cuidado',          'emoji' => '🌱', 'description' => 'Três dias seguidos a cuidar de ti. Isso é bonito.'],
            7   => ['title' => '7 Dias de Reflexão',          'emoji' => '🔥', 'description' => 'Uma semana inteira. A tua consistência é inspiradora.'],
            14  => ['title' => '2 Semanas de Presença',       'emoji' => '🌿', 'description' => 'Duas semanas de presença. Estás a construir algo real.'],
            30  => ['title' => 'Um Mês de Luz',               'emoji' => '✨', 'description' => 'Um mês. Não subestimes o poder desta constância.'],
            60  => ['title' => '60 Dias de Caminho',          'emoji' => '🏔️', 'description' => 'Dois meses de jornada interior. É para te orgulhares.'],
            100 => ['title' => '100 Dias de Transformação',   'emoji' => '🌟', 'description' => 'Cem dias. Já não és a mesma pessoa que começou.'],
        ];

        // Verificar se o streak actual corresponde exactamente a um marco
        if (!array_key_exists($streak, $milestoneThresholds)) {
            return null;
        }

        return $milestoneThresholds[$streak];
    }

    /**
     * Retorna uma frase de encorajamento contextual baseada no estado
     * actual do utilizador (streak, hora do dia, progresso).
     *
     * As frases sao desenhadas para validar sem pressionar,
     * seguindo principios de comunicacao terapeutica nao-directiva.
     */
    private function getEncouragementPhrase($user, array $progressData): string
    {
        $streak = $progressData['streak'];
        $hour = (int) now()->format('H');

        // Frases para quem esta a comecar ou recomecar (sem culpa)
        $restartPhrases = [
            'Cada dia é uma página em branco. Estás aqui, e isso já conta.',
            'Sem pressa, sem pressão. O importante é estares presente.',
            'Recomeçar não é falhar — é escolher cuidar de ti outra vez.',
            'Não precisas de ser perfeito. Precisas de ser gentil contigo.',
        ];

        // Frases para quem tem streak activo (reforco positivo)
        $streakPhrases = [
            'A tua consistência fala por ti. Continua ao teu ritmo.',
            'Dia após dia, estás a construir algo bonito.',
            'Pequenos passos, grandes mudanças. Estás no caminho certo.',
            'A tua presença aqui já é um acto de coragem.',
        ];

        // Frases nocturnas (mais suaves e introspectivas)
        $nightPhrases = [
            'O dia está a terminar. Que bom que passaste por aqui.',
            'Respira fundo. Amanhã é uma nova oportunidade.',
            'A noite é para descansar. Já fizeste o suficiente hoje.',
        ];

        if ($hour >= 21 || $hour < 6) {
            $pool = $nightPhrases;
        } elseif ($streak <= 1) {
            $pool = $restartPhrases;
        } else {
            $pool = $streakPhrases;
        }

        // Seleccao deterministica por dia para evitar mudanca em cada reload
        $dayIndex = (int) now()->format('z');
        return $pool[$dayIndex % count($pool)];
    }

    /**
     * Gera um insight IA personalizado baseado nos registos emocionais recentes.
     * Exige >= 3 entradas em 7 dias para garantir contexto clínico minimamente fiável.
     * Cache de 24h para evitar chamadas redundantes à API.
     *
     * @return array|null Null se dados insuficientes ou falha na API
     */
    private function getAiInsight($user): ?array
    {
        $weekLogs = DailyLog::where('user_id', $user->id)
            ->where('log_date', '>=', now()->subDays(7)->toDateString())
            ->orderBy('log_date')
            ->get(['mood_level', 'tags', 'log_date']);

        if ($weekLogs->count() < 3) {
            return null;
        }

        return Cache::remember("ai_insight_{$user->id}", 86400, function () use ($weekLogs, $user) {
            // Sumarizar os dados emocionais para o prompt sem enviar notas pessoais
            $logSummary = $weekLogs->map(fn ($log) => sprintf(
                '%s: humor %d/5, tags: %s',
                $log->log_date->format('d/m'),
                $log->mood_level,
                implode(', ', $log->tags ?? ['sem tags'])
            ))->implode('; ');

            try {
                $response = Http::withToken(config('services.openai.api_key'))
                    ->timeout(15)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => config('services.openai.model', 'gpt-4o-mini'),
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'És um companheiro emocional empático. Analisa os dados emocionais do utilizador e devolve UM JSON com 3 campos: "message" (1-2 frases empáticas sobre a tendência emocional, em PT-PT), "action_text" (texto curto para um botão de acção sugerida), "action_url" (uma das rotas: /zona-calma, /diario, /mural, /zona-calma/reflexao). Nunca dês diagnósticos clínicos. Responde APENAS com JSON válido.',
                            ],
                            [
                                'role' => 'user',
                                'content' => "Registos da semana de {$user->name}: {$logSummary}",
                            ],
                        ],
                        'max_tokens' => 150,
                        'temperature' => 0.7,
                    ]);

                if ($response->failed()) {
                    return null;
                }

                $content = $response->json('choices.0.message.content');
                $parsed = json_decode($content, true);

                // Validar estrutura mínima do JSON devolvido pela IA
                if (!$parsed || !isset($parsed['message'], $parsed['action_text'], $parsed['action_url'])) {
                    return null;
                }

                return $parsed;
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return null;
            }
        });
    }
}
