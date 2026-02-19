<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\DailyLog;
use App\Models\Achievement; // Importante para Gamificação
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // --- VISUALIZAÇÃO DO PERFIL (O Mix Completo) ---
    public function show(Request $request): View
    {
        $user = Auth::user();
        
        // 1. LÓGICA DO JARDIM (Visualização dos últimos 14 dias)
        $garden = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $log = DailyLog::where('user_id', $user->id)->where('log_date', $date->format('Y-m-d'))->first();
            
            if (!$log) {
                $garden[] = ['type' => 'empty', 'date' => $date->format('d/m')];
            } else {
                // Emojis baseados no humor (1 a 5)
                $icon = match($log->mood_level) {
                    1 => '🥀', 2 => '🌱', 3 => '🌿', 4 => '🌷', 5 => '🌻', default => '🌱'
                };
                $garden[] = ['type' => 'plant', 'icon' => $icon, 'mood' => $log->mood_level, 'date' => $date->format('d/m')];
            }
        }

        // 2. LÓGICA DE GAMIFICAÇÃO (Conquistas)
        $allAchievements = Achievement::all();
        $unlockedIds = $user->achievements->pluck('id')->toArray();

        // 3. ESTATÍSTICAS UNIFICADAS
        // Calculamos o nível da fogueira baseado nas chamas (se existirem) ou logs
        $flames = $user->flames ?? 0;
        
        $bonfireLevel = 'spark';
        if ($flames >= 50) $bonfireLevel = 'flame';
        if ($flames >= 200) $bonfireLevel = 'bonfire';
        if ($flames >= 500) $bonfireLevel = 'beacon';

        $stats = [
            'total_logs' => DailyLog::where('user_id', $user->id)->count(),
            'streak' => $user->current_streak ?? $this->calculateStreak($user), // Usa coluna BD ou cálculo manual
            'flames' => $flames,
            'bonfire_level' => $bonfireLevel,
            'badges_count' => count($unlockedIds) . '/' . $allAchievements->count()
        ];

        // 4. DADOS PARA O GRÁFICO DE HUMOR (Últimos 30 dias)
        $chartData = DailyLog::where('user_id', $user->id)
            ->where('log_date', '>=', Carbon::today()->subDays(30))
            ->orderBy('log_date', 'asc')
            ->get()
            ->map(function ($log) {
                return [
                    'date' => Carbon::parse($log->log_date)->format('d/m'),
                    'mood' => $log->mood_level
                ];
            });

        // 5. JORNADA E IDENTIDADE EMOCIONAL
        $milestones = $user->milestones; 
        $tagsList = ['Ansiedade', 'Luto', 'Burnout', 'Depressão', 'TDAH', 'Pânico', 'Recuperação', 'Stress', 'Solidão'];

        // UM ÚNICO RETURN no final com tudo!
        return view('profile.show', [
            'user' => $user,
            'garden' => $garden,
            'stats' => $stats,
            'achievements' => $allAchievements,
            'unlockedIds' => $unlockedIds,
            'chartData' => $chartData,
            'milestones' => $milestones,
            'tagsList' => $tagsList
        ]);
    }

    // --- ATUALIZAR TAGS DE IDENTIDADE ---
    public function updateTags(Request $request)
    {
        $validated = $request->validate([
            'tags' => 'nullable|array|max:3', // Máximo 3 tags
            'tags.*' => 'string|max:30'
        ]);

        $user = Auth::user();
        $user->emotional_tags = $validated['tags'] ?? [];
        $user->save();

        return back()->with('success', 'Tags de identidade atualizadas!');
    }

    // --- JORNADA: ADICIONAR MARCO ---
    public function storeMilestone(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'date' => 'required|date',
            'is_public' => 'sometimes|accepted'
        ]);

        Auth::user()->milestones()->create([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'is_public' => $request->has('is_public'),
        ]);

        return back()->with('success', 'Novo marco adicionado à tua jornada!');
    }

    // --- JORNADA: APAGAR MARCO ---
    public function destroyMilestone(\App\Models\Milestone $milestone)
    {
        if ($milestone->user_id !== Auth::id()) abort(403);
        $milestone->delete();
        
        return back()->with('success', 'Marco removido.');
    }

    // --- EDIÇÃO DE CONTA ---
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    // --- ATUALIZAR DADOS ---
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }
        $request->user()->save();
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // --- APAGAR CONTA ---
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', ['password' => ['required', 'current_password']]);
        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return Redirect::to('/');
    }

    // --- AÇÕES ESPECÍFICAS ---
    public function updateEnergy(Request $request)
    {
        $request->validate(['level' => 'required|integer|min:1|max:5']);
        $user = Auth::user();
        $user->forceFill(['energy_level' => $request->level])->save();
        return response()->json(['success' => true]);
    }

    public function updateSafetyPlan(Request $request)
    {
        // Aceita array (formato antigo) ou string (formato novo)
        $data = $request->validate([
            'safety_plan' => 'nullable', 
            'triggers' => 'nullable|string', // Compatibilidade
        ]);

        $user = Auth::user();
        // Se vier do form simples, guarda como string, senão guarda JSON
        $user->safety_plan = is_array($data) ? json_encode($data) : $request->input('safety_plan');
        $user->save();

        return back()->with('success', 'Plano atualizado.');
    }

    // Helper de Streak (Fallback)
    private function calculateStreak($user)
    {
        $streak = 0;
        $logs = DailyLog::where('user_id', $user->id)->orderBy('log_date', 'desc')->pluck('log_date')->toArray();
        if (!empty($logs)) {
            $checkDate = Carbon::today();
            if ($logs[0] != $checkDate->format('Y-m-d')) $checkDate = Carbon::yesterday();
            foreach ($logs as $date) {
                if ($date == $checkDate->format('Y-m-d')) {
                    $streak++;
                    $checkDate->subDay();
                } else break;
            }
        }
        return $streak;
    }

    public function logBreathing(\App\Services\GamificationService $gamification)
    {
        $gamification->trackAction(Auth::user(), 'breathe');
        return response()->json(['success' => true, 'flames' => 5]);
    }

    public function updateNotificationPrefs(Request $request)
    {
        $validated = $request->validate([
            'wants_weekly_summary' => 'boolean',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
        ]);

        $user = Auth::user();
        $user->wants_weekly_summary = $request->has('wants_weekly_summary');
        
        // Só guarda as horas se ambas forem preenchidas
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
}