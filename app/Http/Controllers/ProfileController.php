<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\DailyLog;
use App\Models\Achievement;
use App\Models\Milestone;
use App\Services\GamificationService;
use App\Services\MoodTrendService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ProfileController extends Controller
{
    /**
     * Aggregates the user's sanctuary view: emotional garden, gamification stats,
     * mood spiral, milestones, guardian status, and soul climate.
     */
    public function show(Request $request): View
    {
        $user = Auth::user();

        // --- Garden State (14-day emotional history, single query) ---
        $gardenStart = Carbon::today()->subDays(13);
        $logs = DailyLog::where('user_id', $user->id)
            ->where('log_date', '>=', $gardenStart->format('Y-m-d'))
            ->pluck('mood_level', 'log_date');

        $garden = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateKey = $date->format('Y-m-d');

            if (!$logs->has($dateKey)) {
                $garden[] = ['type' => 'empty', 'date' => $date->format('d/m')];
            } else {
                $mood = $logs[$dateKey];
                $garden[] = [
                    'type' => 'plant',
                    'icon' => match ($mood) { 1 => '🥀', 2 => '🌱', 3 => '🌿', 4 => '🌷', 5 => '🌻', default => '🌱' },
                    'mood' => $mood,
                    'date' => $date->format('d/m'),
                ];
            }
        }

        // --- Gamification & Badges ---
        // Achievements são definidos em seed e raramente mudam — cache de 1h partilhado por todos os utilizadores.
        $allAchievements = Cache::remember('all_achievements', 3600, fn () => Achievement::all());
        $unlockedIds = $user->achievements->pluck('id')->toArray();

        $flames = $user->flames ?? 0;
        $bonfireLevel = match (true) {
            $flames >= 500 => 'beacon',
            $flames >= 200 => 'bonfire',
            $flames >= 50  => 'flame',
            default        => 'spark',
        };

        $stats = [
            'total_logs' => DailyLog::where('user_id', $user->id)->count(),
            'streak' => $user->current_streak ?? $this->calculateStreak($user),
            'flames' => $flames,
            'bonfire_level' => $bonfireLevel,
            'badges_count' => count($unlockedIds) . '/' . $allAchievements->count(),
        ];

        // --- Mood Spiral (last 30 entries for the Archimedean SVG) ---
        $colorMap = [
            1 => '#f43f5e', 2 => '#f59e0b', 3 => '#94a3b8',
            4 => '#14b8a6', 5 => '#6366f1',
        ];

        // Cache de 24h: a espiral só muda quando o utilizador regista um novo diário.
        $spiralData = Cache::remember("spiral:{$user->id}", 86400, function () use ($user, $colorMap) {
            return DailyLog::where('user_id', $user->id)
                ->orderBy('log_date', 'desc')
                ->take(30)
                ->get()
                ->reverse()
                ->values()
                ->map(fn ($log) => [
                    'date'  => Carbon::parse($log->log_date)->format('d/m'),
                    'color' => $colorMap[$log->mood_level] ?? '#cbd5e1',
                    'note'  => $log->note ? mb_strimwidth($log->note, 0, 60, '...') : 'Sem nota escrita',
                ]);
        });

        $milestones = $user->milestones()->orderBy('date', 'desc')->get();
        $tagsList = ['Ansiedade', 'Luto', 'Burnout', 'Depressão', 'TDAH', 'Pânico', 'Recuperação', 'Stress', 'Solidão'];

        // --- Guardian Status (3-tier progression: Semente → Broto → Árvore) ---
        $guardianStatus = match (true) {
            $flames >= 151 => ['emoji' => '🌳', 'stage_name' => 'Árvore', 'next_stage_flames' => null],
            $flames >= 51  => ['emoji' => '🌿', 'stage_name' => 'Broto', 'next_stage_flames' => 151],
            default        => ['emoji' => '🌱', 'stage_name' => 'Semente', 'next_stage_flames' => 51],
        };

        // --- Soul Climate (based on TODAY's log — mood + distress tags) ---
        $todayLog = DailyLog::where('user_id', $user->id)
            ->where('log_date', Carbon::today()->toDateString())
            ->first();

        $distressTags = ['ansiedade', 'tristeza', 'pânico', 'medo', 'solidão'];
        $isDistressed = $todayLog && (
            $todayLog->mood_level <= 2
            || !empty(array_intersect($todayLog->tags ?? [], $distressTags))
        );

        $weather = $todayLog ? ($isDistressed ? 'rainy' : 'sunny') : 'sunny';

        return view('profile.show', compact(
            'user', 'garden', 'stats', 'allAchievements', 'unlockedIds',
            'spiralData', 'milestones', 'tagsList', 'guardianStatus', 'weather'
        ));
    }

    public function updateTags(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tags' => 'nullable|array|max:3',
            'tags.*' => 'string|max:30',
        ]);

        $request->user()->forceFill([
            'emotional_tags' => $validated['tags'] ?? [],
        ])->save();

        return back()->with('success', 'Tags de identidade atualizadas!');
    }

    public function storeMilestone(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'date' => 'required|date',
            'is_public' => 'sometimes|accepted',
        ]);

        $request->user()->milestones()->create([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'is_public' => $request->has('is_public'),
        ]);

        return back()->with('success', 'Novo marco adicionado à tua jornada!');
    }

    public function destroyMilestone(Milestone $milestone): RedirectResponse
    {
        if ($milestone->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $milestone->delete();
        return back()->with('success', 'Marco removido com sucesso.');
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Initiates account deletion (GDPR Right to Erasure).
     * Soft-deletes the user first so the async GDPR job can find the record.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Registo de auditoria antes do logout — após logout $request->user() é null.
        \App\Models\DataAccessLog::create([
            'user_id'     => $user->id,
            'accessed_by' => $user->id,
            'data_type'   => 'account_deletion_initiated',
            'purpose'     => 'Pedido de eliminação de conta pelo próprio utilizador.',
            'ip_address'  => $request->ip(),
        ]);

        Auth::logout();
        $user->delete();

        \App\Jobs\ProcessGdprDeletion::dispatch($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('status', 'A tua conta está a ser eliminada em segurança. Receberás um email de confirmação em breve.');
    }

    public function updateEnergy(Request $request)
    {
        $request->validate(['level' => 'required|integer|min:1|max:5']);
        $request->user()->forceFill(['energy_level' => $request->level])->save();

        return response()->json(['success' => true]);
    }

    /**
     * Persists the structured safety plan (6 fields) as JSON.
     * Used by the crisis page to display an organized personal safety resource.
     */
    public function updateSafetyPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warning_signs'         => 'nullable|string|max:1000',
            'coping_strategies'     => 'nullable|string|max:1000',
            'reasons_to_live'       => 'nullable|string|max:1000',
            'support_contacts'      => 'nullable|string|max:1000',
            'professional_contacts' => 'nullable|string|max:1000',
            'environment_safety'    => 'nullable|string|max:1000',
        ]);

        $plan = array_filter($validated, fn ($v) => !is_null($v) && $v !== '');

        $request->user()->forceFill([
            'safety_plan' => !empty($plan) ? json_encode($plan) : null,
        ])->save();

        return back()->with('success', 'safety-plan-updated');
    }

    /**
     * Fallback streak calculation when the cached `current_streak` column is stale.
     * Walks backwards from today checking for consecutive daily log entries.
     */
    private function calculateStreak($user): int
    {
        $logs = DailyLog::where('user_id', $user->id)
            ->orderBy('log_date', 'desc')
            ->pluck('log_date')
            ->toArray();

        if (empty($logs)) {
            return 0;
        }

        $streak = 0;
        $checkDate = Carbon::today();

        if ($logs[0] != $checkDate->format('Y-m-d')) {
            $checkDate = Carbon::yesterday();
        }

        foreach ($logs as $date) {
            if ($date == $checkDate->format('Y-m-d')) {
                $streak++;
                $checkDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Dashboard de tendências de humor (7d, 30d, 90d) com média móvel e alerta proativo.
     * Dados pré-computados pelo MoodTrendService e cacheados por 6h.
     */
    public function moodTrends(MoodTrendService $trendService): View
    {
        $user = Auth::user();
        $data = $trendService->getDashboardData($user);

        return view('profile.mood-trends', compact('user', 'data'));
    }

    public function logBreathing(GamificationService $gamification)
    {
        $gamification->trackAction(Auth::user(), 'breathe');
        return response()->json(['success' => true, 'flames' => 5]);
    }

    public function updateNotificationPrefs(Request $request): RedirectResponse
    {
        $request->validate([
            'wants_weekly_summary' => 'boolean',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
        ]);

        $user = $request->user();
        $user->wants_weekly_summary = $request->has('wants_weekly_summary');

        // Both start and end are required to enable quiet hours
        if ($request->quiet_hours_start && $request->quiet_hours_end) {
            $user->quiet_hours_start = $request->quiet_hours_start;
            $user->quiet_hours_end = $request->quiet_hours_end;
        } else {
            $user->quiet_hours_start = null;
            $user->quiet_hours_end = null;
        }

        $user->save();

        return back()->with('status', 'notification-prefs-updated');
    }

    public function updateAccessibility(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'a11y_dyslexic_font' => 'boolean',
            'a11y_reduced_motion' => 'boolean',
            'a11y_text_size' => 'required|in:sm,base,lg,xl',
        ]);

        $request->user()->forceFill([
            'a11y_dyslexic_font' => $request->boolean('a11y_dyslexic_font'),
            'a11y_reduced_motion' => $request->boolean('a11y_reduced_motion'),
            'a11y_text_size' => $validated['a11y_text_size'],
        ])->save();

        return back()->with('status', 'accessibility-updated');
    }

    /**
     * Gera o Passaporte Emocional: síntese dos últimos 30 dias.
     * Calcula média de humor e tags mais frequentes para dar ao
     * utilizador uma visão consolidada do seu percurso recente.
     */
    public function exportPassport(): View
    {
        $user = Auth::user();

        $logs = DailyLog::where('user_id', $user->id)
            ->where('log_date', '>=', Carbon::today()->subDays(30)->toDateString())
            ->orderBy('log_date')
            ->get();

        $averageMood = $logs->isNotEmpty()
            ? round($logs->avg('mood_level'), 1)
            : null;

        // Agregar tags e ordenar por frequência descendente
        $tagCounts = [];
        foreach ($logs as $log) {
            foreach ($log->tags ?? [] as $tag) {
                $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
            }
        }
        arsort($tagCounts);
        $topTags = array_slice(array_keys($tagCounts), 0, 5);

        $totalLogs = $logs->count();

        return view('profile.passport', compact('user', 'logs', 'averageMood', 'topTags', 'totalLogs'));
    }
}
