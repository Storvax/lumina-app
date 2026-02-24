<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\GamificationService;
use App\Models\DailyLog;
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

        // Missões de hoje com progresso do utilizador
        $dailyMissions = $user->missions()
            ->wherePivot('assigned_date', $today)
            ->get();

        // Verificar se o utilizador já fez o registo emocional de hoje
        $todayLog = DailyLog::where('user_id', $user->id)
            ->where('log_date', $today)
            ->first();

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

        return view('dashboard', compact(
            'dailyMissions',
            'progressData',
            'greeting',
            'pendingMilestone',
            'emotionalTags',
            'encouragement',
            'todayLog'
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
}
