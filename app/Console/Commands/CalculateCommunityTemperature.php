<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\Post;
use App\Models\DailyLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Calcula o índice de "temperatura" emocional da comunidade.
 *
 * Verde (calm): maioria de interações positivas, poucos alertas.
 * Amarelo (alert): aumento de conteúdo sensível ou mood baixo.
 * Vermelho (crisis): múltiplos alertas de crise detetados.
 */
class CalculateCommunityTemperature extends Command
{
    protected $signature = 'lumina:community-temperature';
    protected $description = 'Calcula o índice de temperatura emocional da comunidade';

    public function handle(): int
    {
        $since = now()->subHours(6);

        // 1. Posts de alto risco nas últimas 6h
        $highRiskPosts = Post::where('risk_level', 'high')
            ->where('created_at', '>=', $since)
            ->count();

        // 2. Mensagens sensíveis
        $sensitiveMessages = Message::where('is_sensitive', true)
            ->where('created_at', '>=', $since)
            ->count();

        // 3. Mood médio dos registos do dia
        $avgMood = DailyLog::where('log_date', now()->toDateString())
            ->avg('mood_level') ?? 3;

        // 4. Atividade geral (mensagens por hora)
        $totalMessages = Message::where('created_at', '>=', $since)->count();
        $messagesPerHour = $totalMessages / 6;

        // Cálculo do score (0-100, onde 100 = mais tranquilo)
        $score = 80; // Base

        // Penalidades
        $score -= $highRiskPosts * 15;
        $score -= $sensitiveMessages * 3;
        $score -= max(0, (3 - $avgMood)) * 10;

        // Bónus de atividade saudável
        if ($messagesPerHour > 5 && $highRiskPosts === 0) {
            $score += 5;
        }

        $score = max(0, min(100, $score));

        // Classificação
        $level = match (true) {
            $score >= 60 => 'green',
            $score >= 30 => 'yellow',
            default => 'red',
        };

        $data = [
            'score' => $score,
            'level' => $level,
            'high_risk_posts' => $highRiskPosts,
            'sensitive_messages' => $sensitiveMessages,
            'avg_mood' => round($avgMood, 1),
            'messages_per_hour' => round($messagesPerHour, 1),
            'calculated_at' => now()->toIso8601String(),
        ];

        Cache::put('community_temperature', $data, 3600);

        $this->info("Temperatura: {$level} (score: {$score})");

        return self::SUCCESS;
    }
}
