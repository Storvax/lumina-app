<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\AnalyticsEvent;
use Illuminate\Support\Facades\Auth;

/**
 * Serviço de analytics emocional para rastrear o funil de conversão.
 *
 * Regista eventos-chave: visualização de crise, início de respiração,
 * conclusão de respiração, saída, e outros marcos do percurso terapêutico.
 */
class AnalyticsService
{
    // Eventos do funil emocional
    public const CRISIS_VIEW = 'crisis_view';
    public const BREATHE_START = 'breathe_start';
    public const BREATHE_COMPLETE = 'breathe_complete';
    public const GROUNDING_START = 'grounding_start';
    public const GROUNDING_COMPLETE = 'grounding_complete';
    public const DIARY_ENTRY = 'diary_entry';
    public const BUDDY_REQUEST = 'buddy_request';
    public const FORUM_POST = 'forum_post';
    public const SAFETY_PLAN_VIEW = 'safety_plan_view';
    public const SESSION_START = 'session_start';
    public const PAGE_LEAVE = 'page_leave';

    /**
     * Regista um evento no funil analítico.
     */
    public function track(string $event, array $metadata = [], ?int $userId = null): void
    {
        AnalyticsEvent::create([
            'user_id' => $userId ?? Auth::id(),
            'event' => $event,
            'metadata' => !empty($metadata) ? $metadata : null,
            'created_at' => now(),
        ]);
    }

    /**
     * Calcula a taxa de conversão entre dois eventos num período.
     */
    public function conversionRate(string $fromEvent, string $toEvent, int $days = 7): float
    {
        $since = now()->subDays($days);

        $fromCount = AnalyticsEvent::where('event', $fromEvent)
            ->where('created_at', '>=', $since)
            ->distinct('user_id')
            ->count('user_id');

        if ($fromCount === 0) {
            return 0;
        }

        $toCount = AnalyticsEvent::where('event', $toEvent)
            ->where('created_at', '>=', $since)
            ->whereIn('user_id', function ($query) use ($fromEvent, $since) {
                $query->select('user_id')
                    ->from('analytics_events')
                    ->where('event', $fromEvent)
                    ->where('created_at', '>=', $since);
            })
            ->distinct('user_id')
            ->count('user_id');

        return round(($toCount / $fromCount) * 100, 1);
    }

    /**
     * Retorna as contagens de eventos agrupados por tipo para o período.
     */
    public function eventCounts(int $days = 7): array
    {
        return AnalyticsEvent::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('event, COUNT(*) as total')
            ->groupBy('event')
            ->pluck('total', 'event')
            ->toArray();
    }
}
