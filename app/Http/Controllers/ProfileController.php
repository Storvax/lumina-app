<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest; // Importante para validação padrão
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\DailyLog;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // --- 1. VISUALIZAÇÃO DO PERFIL (Jardim e Estatísticas) ---
    public function show()
    {
        $user = Auth::user();
        
        // Lógica do Jardim (Últimos 14 dias)
        $garden = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $log = DailyLog::where('user_id', $user->id)->where('log_date', $date->format('Y-m-d'))->first();
            
            if (!$log) {
                $garden[] = ['type' => 'empty', 'date' => $date->format('d M')];
            } else {
                $icon = match($log->mood_level) {
                    1 => '🥀', 2 => '🌱', 3 => '🌿', 4 => '🌷', 5 => '🌻', default => '🌱'
                };
                $garden[] = ['type' => 'plant', 'icon' => $icon, 'mood' => $log->mood_level, 'date' => $date->format('d M')];
            }
        }

        $stats = [
            'total_logs' => DailyLog::where('user_id', $user->id)->count(),
            'streak' => $this->calculateStreak($user),
            'level' => floor(DailyLog::where('user_id', $user->id)->count() / 5) + 1
        ];

        return view('profile.show', compact('user', 'garden', 'stats'));
    }

    // --- 2. EDIÇÃO DE CONTA (Faltava este método!) ---
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    // --- 3. ATUALIZAR DADOS DA CONTA (Nome, Email) ---
    public function update(Request $request): RedirectResponse
    {
        // Validação
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // --- 4. APAGAR CONTA (Faltava este método!) ---
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    // --- MÉTODOS ESPECÍFICOS DA LUMINA ---

    public function updateEnergy(Request $request)
    {
        $request->validate(['level' => 'required|integer|min:1|max:5']);
        $user = Auth::user();
        $user->energy_level = $request->level;
        $user->save();
        
        return response()->json(['success' => true]);
    }

    public function updateSafetyPlan(Request $request)
    {
        $data = $request->validate([
            'triggers' => 'nullable|string',
            'contacts' => 'nullable|string',
            'coping' => 'nullable|string',
        ]);

        $user = Auth::user();
        $user->safety_plan = $data;
        $user->save();

        return back()->with('success', 'Plano de segurança atualizado.');
    }

    private function calculateStreak($user)
    {
        $streak = 0;
        $logs = DailyLog::where('user_id', $user->id)
                        ->orderBy('log_date', 'desc')
                        ->pluck('log_date')
                        ->toArray();
        
        if (!empty($logs)) {
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
        }

        return $streak;
    }
}