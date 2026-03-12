<?php

namespace App\Services;

use App\Models\User;
use App\Models\DailyLog;
use App\Models\Post;

/**
 * Motor de recomendação contextual.
 *
 * Sugere recursos da biblioteca, posts e atividades com base no
 * humor atual, histórico emocional, hora do dia e padrões de uso.
 */
class RecommendationService
{
    /**
     * Retorna até 3 recomendações contextuais para o utilizador.
     */
    public function getRecommendations(User $user): array
    {
        $hour = (int) now()->format('H');
        $tags = $user->emotional_tags ?? [];
        $latestLog = DailyLog::where('user_id', $user->id)->latest('log_date')->first();
        $moodLevel = $latestLog?->mood_level;

        $recommendations = [];

        // 1. Recomendação baseada no humor
        if ($moodLevel !== null) {
            $recommendations[] = $this->moodBasedRecommendation($moodLevel);
        }

        // 2. Recomendação baseada na hora do dia
        $recommendations[] = $this->timeBasedRecommendation($hour);

        // 3. Recomendação baseada nas tags emocionais
        if (!empty($tags)) {
            $tagRec = $this->tagBasedRecommendation($tags);
            if ($tagRec) {
                $recommendations[] = $tagRec;
            }
        }

        // 4. Post inspirador recente (se não temos 3 ainda)
        if (count($recommendations) < 3) {
            $inspiringPost = $this->getInspiringPost($user);
            if ($inspiringPost) {
                $recommendations[] = $inspiringPost;
            }
        }

        return array_slice(array_filter($recommendations), 0, 3);
    }

    private function moodBasedRecommendation(int $moodLevel): array
    {
        return match (true) {
            $moodLevel <= 2 => [
                'type' => 'activity',
                'icon' => 'ri-heart-pulse-line',
                'title' => 'Zona Calma',
                'description' => 'Exercícios de respiração e grounding para te ajudar agora.',
                'route' => 'calm.index',
                'color' => 'blue',
            ],
            $moodLevel === 3 => [
                'type' => 'activity',
                'icon' => 'ri-quill-pen-line',
                'title' => 'Diário Emocional',
                'description' => 'Escrever pode ajudar a organizar os pensamentos.',
                'route' => 'diary.index',
                'color' => 'violet',
            ],
            default => [
                'type' => 'activity',
                'icon' => 'ri-fire-line',
                'title' => 'A Fogueira',
                'description' => 'Partilha a tua energia positiva com a comunidade.',
                'route' => 'rooms.index',
                'color' => 'amber',
            ],
        };
    }

    private function timeBasedRecommendation(int $hour): array
    {
        return match (true) {
            $hour >= 6 && $hour < 10 => [
                'type' => 'activity',
                'icon' => 'ri-sun-line',
                'title' => 'Momento Matinal',
                'description' => 'Começa o dia com um registo no diário emocional.',
                'route' => 'diary.index',
                'color' => 'amber',
            ],
            $hour >= 22 || $hour < 6 => [
                'type' => 'activity',
                'icon' => 'ri-moon-line',
                'title' => 'Modo Noturno',
                'description' => 'Exercícios de relaxamento antes de dormir.',
                'route' => 'calm.grounding',
                'color' => 'indigo',
            ],
            default => [
                'type' => 'activity',
                'icon' => 'ri-book-open-line',
                'title' => 'Biblioteca',
                'description' => 'Descobre recursos partilhados pela comunidade.',
                'route' => 'library.index',
                'color' => 'emerald',
            ],
        };
    }

    private function tagBasedRecommendation(array $tags): ?array
    {
        $anxietyTags = ['Ansiedade', 'Pânico', 'Sobrecarregado'];
        $griefTags = ['Luto', 'Perda', 'Saudade'];
        $lonelinessTags = ['Solidão', 'Isolamento'];

        if (array_intersect($tags, $anxietyTags)) {
            return [
                'type' => 'resource',
                'icon' => 'ri-mental-health-line',
                'title' => 'Grounding 5-4-3-2-1',
                'description' => 'Técnica sensorial para momentos de ansiedade.',
                'route' => 'calm.grounding',
                'color' => 'teal',
            ];
        }

        if (array_intersect($tags, $griefTags)) {
            return [
                'type' => 'resource',
                'icon' => 'ri-seedling-line',
                'title' => 'Mural da Esperança',
                'description' => 'Histórias de quem passou por momentos semelhantes.',
                'route' => 'forum.index',
                'color' => 'emerald',
            ];
        }

        if (array_intersect($tags, $lonelinessTags)) {
            return [
                'type' => 'resource',
                'icon' => 'ri-team-line',
                'title' => 'Pedir um Ouvinte',
                'description' => 'Alguém disponível para te ouvir, sem julgamento.',
                'route' => 'buddy.dashboard',
                'color' => 'violet',
            ];
        }

        return null;
    }

    private function getInspiringPost(User $user): ?array
    {
        $post = Post::where('tag', 'hope')
            ->where('user_id', '!=', $user->id)
            ->where('is_sensitive', false)
            ->whereHas('reactions', null, '>=', 2)
            ->latest()
            ->first();

        if (!$post) {
            return null;
        }

        return [
            'type' => 'post',
            'icon' => 'ri-sparkling-line',
            'title' => $post->title,
            'description' => 'Uma história de esperança da comunidade.',
            'route' => 'forum.show',
            'route_params' => [$post],
            'color' => 'emerald',
        ];
    }
}
