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
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's sanctuary/profile view.
     * Aggregates garden state, gamification stats, milestones, and spiral data.
     */
    public function show(Request $request): View
    {
        $user = Auth::user();
        
        // 1. Garden State (Last 14 days)
        $garden = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $log = DailyLog::where('user_id', $user->id)
                ->where('log_date', $date->format('Y-m-d'))
                ->first();
            
            if (!$log) {
                $garden[] = ['type' => 'empty', 'date' => $date->format('d/m')];
            } else {
                $icon = match($log->mood_level) {
                    1 => '🥀', 2 => '🌱', 3 => '🌿', 4 => '🌷', 5 => '🌻', default => '🌱'
                };
                $garden[] = [
                    'type' => 'plant', 
                    'icon' => $icon, 
                    'mood' => $log->mood_level, 
                    'date' => $date->format('d/m')
                ];
            }
        }

        // 2. Gamification & Badges
        $allAchievements = Achievement::all();
        $unlockedIds = $user->achievements->pluck('id')->toArray();

        // 3. User Statistics & Bonfire Level
        $flames = $user->flames ?? 0;
        $bonfireLevel = match(true) {
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
            'badges_count' => count($unlockedIds) . '/' . $allAchievements->count()
        ];

        // 4. Mood Spiral Data (Last 30 entries formatted for the Archimedean SVG)
        $colorMap = [
            1 => '#f43f5e', // rose-500 (Very Difficult)
            2 => '#f59e0b', // amber-500 (Difficult)
            3 => '#94a3b8', // slate-400 (Neutral)
            4 => '#14b8a6', // teal-500 (Good)
            5 => '#6366f1', // indigo-500 (Excellent)
        ];

        $spiralData = DailyLog::where('user_id', $user->id)
            ->orderBy('log_date', 'desc')
            ->take(30)
            ->get()
            ->reverse() // Reverses to chronological order: inside of the spiral is older
            ->values()
            ->map(function ($log) use ($colorMap) {
                return [
                    'date' => Carbon::parse($log->log_date)->format('d/m'),
                    'color' => $colorMap[$log->mood_level] ?? '#cbd5e1',
                    'note' => $log->cbt_insight ?? 'Sem registo detalhado',
                ];
            });

        // 5. Milestones & Identity
        $milestones = $user->milestones()->orderBy('date', 'desc')->get(); 
        $tagsList = ['Ansiedade', 'Luto', 'Burnout', 'Depressão', 'TDAH', 'Pânico', 'Recuperação', 'Stress', 'Solidão'];

        return view('profile.show', compact(
            'user', 
            'garden', 
            'stats', 
            'allAchievements', 
            'unlockedIds', 
            'spiralData', 
            'milestones', 
            'tagsList'
        ));
    }

    /**
     * Update the user's emotional identity tags.
     */
    public function updateTags(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tags' => 'nullable|array|max:3',
            'tags.*' => 'string|max:30'
        ]);

        $request->user()->forceFill([
            'emotional_tags' => $validated['tags'] ?? []
        ])->save();

        return back()->with('success', 'Tags de identidade atualizadas!');
    }

    /**
     * Store a new psychological milestone for the user.
     */
    public function storeMilestone(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'date' => 'required|date',
            'is_public' => 'sometimes|accepted'
        ]);

        $request->user()->milestones()->create([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'is_public' => $request->has('is_public'),
        ]);

        return back()->with('success', 'Novo marco adicionado à tua jornada!');
    }

    /**
     * Delete an existing milestone.
     */
    public function destroyMilestone(Milestone $milestone): RedirectResponse
    {
        if ($milestone->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $milestone->delete();
        return back()->with('success', 'Marco removido com sucesso.');
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /**
     * Update the user's profile information.
     */
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
     * Inicia o processo de eliminação da conta (Direito ao Esquecimento).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password']
        ]);

        $user = $request->user();
        
        Auth::logout();

        // Soft Delete the user BEFORE dispatching the job so withTrashed() can find it
        $user->delete();

        // Dispatch the GDPR job
        \App\Jobs\ProcessGdprDeletion::dispatch($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Envia uma mensagem flash de despedida empática para a sessão
        return Redirect::to('/')->with('status', 'A tua conta está a ser eliminada em segurança. Receberás um email de confirmação em breve.');
    }
    
    /**
     * Update the user's daily battery/energy level via AJAX request.
     */
    public function updateEnergy(Request $request)
    {
        $request->validate(['level' => 'required|integer|min:1|max:5']);
        
        $request->user()->forceFill(['energy_level' => $request->level])->save();
        
        return response()->json(['success' => true]);
    }

    /**
     * Atualiza o plano de segurança pessoal do utilizador.
     * Persiste os 6 campos estruturados como JSON, permitindo exibição organizada na página de crise.
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

        // Remove campos vazios para não guardar ruído no JSON
        $plan = array_filter($validated, fn($v) => !is_null($v) && $v !== '');

        $request->user()->forceFill([
            'safety_plan' => !empty($plan) ? json_encode($plan) : null,
        ])->save();

        return back()->with('success', 'safety-plan-updated');
    }

    /**
     * Calculate current logging streak as a fallback.
     */
    private function calculateStreak($user): int
    {
        $streak = 0;
        $logs = DailyLog::where('user_id', $user->id)
            ->orderBy('log_date', 'desc')
            ->pluck('log_date')
            ->toArray();

        if (empty($logs)) {
            return $streak;
        }

        $checkDate = Carbon::today();
        
        // If there's no log for today, check if streak is maintained by yesterday's log
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
     * Record a completed breathing exercise via Gamification Service.
     */
    public function logBreathing(GamificationService $gamification)
    {
        $gamification->trackAction(Auth::user(), 'breathe');
        return response()->json(['success' => true, 'flames' => 5]);
    }

    /**
     * Update notification and quiet hours preferences.
     */
    public function updateNotificationPrefs(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wants_weekly_summary' => 'boolean',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
        ]);

        $user = $request->user();
        $user->wants_weekly_summary = $request->has('wants_weekly_summary');
        
        // Require both start and end times to enable quiet hours
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

    /**
     * Atualiza as preferências de acessibilidade visual e cognitiva do utilizador.
     */
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
}