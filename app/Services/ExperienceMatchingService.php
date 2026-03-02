<?php

namespace App\Services;

use App\Models\ExperienceConnection;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Calcula a similaridade entre utilizadores com base em:
 *  1. Tags emocionais em comum (emotional_tags).
 *  2. Salas visitadas em comum (room_visits).
 *  3. Tags de posts em comum.
 *
 * O score normalizado (0-100) determina se uma sugestão é relevante.
 * Threshold mínimo: 30 pontos.
 */
class ExperienceMatchingService
{
    private const THRESHOLD = 30;
    private const TAG_WEIGHT = 5;
    private const ROOM_WEIGHT = 10;
    private const POST_TAG_WEIGHT = 3;

    /**
     * Encontra o melhor match para o utilizador dado.
     *
     * @return array{user: User, score: int}|null
     */
    public function findMatch(User $user): ?array
    {
        $userTags = $user->emotional_tags ?? [];
        if (empty($userTags)) {
            return null;
        }

        // Salas visitadas pelo utilizador
        $userRoomIds = DB::table('room_visits')
            ->where('user_id', $user->id)
            ->pluck('room_id')
            ->toArray();

        // Tags de posts do utilizador
        $userPostTags = $user->posts()->pluck('tag')->filter()->unique()->toArray();

        // IDs de utilizadores já conectados ou sugeridos
        $excludeIds = ExperienceConnection::where('user_id', $user->id)
            ->orWhere('suggested_user_id', $user->id)
            ->get()
            ->flatMap(fn ($c) => [$c->user_id, $c->suggested_user_id])
            ->unique()
            ->toArray();

        $excludeIds[] = $user->id;

        $bestMatch = null;
        $bestScore = 0;

        User::whereNotIn('id', $excludeIds)
            ->whereNull('hibernated_at')
            ->whereNull('banned_at')
            ->chunkById(100, function ($candidates) use ($userTags, $userRoomIds, $userPostTags, &$bestMatch, &$bestScore) {
                foreach ($candidates as $candidate) {
                    $score = $this->calculateScore($candidate, $userTags, $userRoomIds, $userPostTags);

                    if ($score > $bestScore && $score >= self::THRESHOLD) {
                        $bestScore = $score;
                        $bestMatch = $candidate;
                    }
                }
            });

        if (!$bestMatch) {
            return null;
        }

        return ['user' => $bestMatch, 'score' => $bestScore];
    }

    private function calculateScore(User $candidate, array $userTags, array $userRoomIds, array $userPostTags): int
    {
        $score = 0;

        // Tags emocionais em comum
        $candidateTags = $candidate->emotional_tags ?? [];
        $commonTags = array_intersect($userTags, $candidateTags);
        $score += count($commonTags) * self::TAG_WEIGHT;

        // Salas visitadas em comum
        $candidateRoomIds = DB::table('room_visits')
            ->where('user_id', $candidate->id)
            ->pluck('room_id')
            ->toArray();
        $commonRooms = array_intersect($userRoomIds, $candidateRoomIds);
        $score += count($commonRooms) * self::ROOM_WEIGHT;

        // Tags de posts em comum
        $candidatePostTags = $candidate->posts()->pluck('tag')->filter()->unique()->toArray();
        $commonPostTags = array_intersect($userPostTags, $candidatePostTags);
        $score += count($commonPostTags) * self::POST_TAG_WEIGHT;

        return min(100, $score);
    }
}
