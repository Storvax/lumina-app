<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyLog;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        // Lógica do Jardim (Últimos 14 dias para caber bem no mobile/desktop)
        $garden = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $log = DailyLog::where('user_id', $user->id)->where('log_date', $date->format('Y-m-d'))->first();
            
            // Define o "tipo" de planta baseado no humor
            if (!$log) {
                $garden[] = ['type' => 'empty', 'date' => $date->format('d M')];
            } else {
                $icon = match($log->mood_level) {
                    1 => '🥀', // Murcha (precisa carinho)
                    2 => '🌱', // Broto
                    3 => '🌿', // Planta
                    4 => '🌷', // Tulipa
                    5 => '🌻', // Girassol Radiante
                    default => '🌱'
                };
                $garden[] = ['type' => 'plant', 'icon' => $icon, 'mood' => $log->mood_level, 'date' => $date->format('d M')];
            }
        }

        $stats = [
            'total_logs' => DailyLog::where('user_id', $user->id)->count(),
            'streak' => $this->calculateStreak($user), // Usa a tua função privada existente
            'level' => floor(DailyLog::where('user_id', $user->id)->count() / 5) + 1 // Nível sobe a cada 5 posts
        ];

        return view('profile.show', compact('user', 'garden', 'stats'));
    }

    // Método AJAX para atualizar energia
    public function updateEnergy(Request $request)
    {
        $request->validate(['level' => 'required|integer|min:1|max:5']);
        $user = Auth::user();
        $user->energy_level = $request->level;
        $user->save();
        
        return response()->json(['success' => true]);
    }

    // Método para atualizar o Plano de Segurança
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

    // --- FUNÇÃO PRIVADA QUE ESTAVA EM FALTA ---
    private function calculateStreak($user)
    {
        $streak = 0;
        // Pega em todas as datas de registo, da mais recente para a mais antiga
        $logs = DailyLog::where('user_id', $user->id)
                        ->orderBy('log_date', 'desc')
                        ->pluck('log_date')
                        ->toArray();
        
        if (!empty($logs)) {
            $checkDate = Carbon::today();
            
            // Se não escreveu hoje, verifica se escreveu ontem para manter a streak viva
            // (Se o último log não for hoje, assumimos que pode ser ontem. Se for antes de ontem, a streak quebra no loop)
            if ($logs[0] != $checkDate->format('Y-m-d')) {
                $checkDate = Carbon::yesterday();
            }
            
            foreach ($logs as $date) {
                // Compara strings de data (Y-m-d)
                if ($date == $checkDate->format('Y-m-d')) {
                    $streak++;
                    $checkDate->subDay(); // Recua um dia para verificar o próximo
                } else {
                    break; // Quebrou a sequência
                }
            }
        }

        return $streak;
    }

    public function update(Request $request)
    {
        // 1. Validação dos dados
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
            'bio' => ['nullable', 'string', 'max:500'],
            // Validação para o Plano de Segurança (array)
            'safety_plan.triggers' => ['nullable', 'string'],
            'safety_plan.coping' => ['nullable', 'string'],
            'safety_plan.contacts' => ['nullable', 'string'],
        ]);

        // 2. Preencher os dados no utilizador
        $request->user()->fill($validated);

        // 3. Reset da verificação de email se o email mudar
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // 4. Guardar
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
}