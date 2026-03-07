<?php

namespace App\Http\Controllers;

use App\Events\SomaticSyncTriggered;
use App\Models\DailyLog;
use App\Models\Therapist;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TherapistController extends Controller
{
    /**
     * Dashboard do terapeuta com pacientes atribuídos
     * e o clima emocional de cada um nos últimos 7 dias.
     */
    public function dashboard()
    {
        $therapist = Therapist::where('name', 'like', '%' . Auth::user()->name . '%')->first();

        if (!$therapist) {
            abort(403, 'Perfil de terapeuta não encontrado.');
        }

        $patients = $therapist->patients()->get();
        $patientIds = $patients->pluck('id');

        // Query única para todos os logs dos pacientes (previne N+1)
        $weekStart = Carbon::today()->subDays(6)->toDateString();
        $logs = DailyLog::whereIn('user_id', $patientIds)
            ->where('log_date', '>=', $weekStart)
            ->get()
            ->groupBy('user_id');

        // Calcular clima emocional por paciente
        $patientsWithWeather = $patients->map(function ($patient) use ($logs) {
            $patientLogs = $logs->get($patient->id, collect());
            $avgMood = $patientLogs->isNotEmpty()
                ? round($patientLogs->avg('mood_level'), 1)
                : null;

            return [
                'user' => $patient,
                'avg_mood_7d' => $avgMood,
                'log_count_7d' => $patientLogs->count(),
                'weather' => match (true) {
                    $avgMood === null => 'unknown',
                    $avgMood <= 2.0 => 'stormy',
                    $avgMood <= 3.0 => 'cloudy',
                    $avgMood <= 4.0 => 'partly_sunny',
                    default => 'sunny',
                },
            ];
        });

        return view('therapist.dashboard', [
            'therapist' => $therapist,
            'patients' => $patientsWithWeather,
        ]);
    }

    /**
     * Atribui uma missão terapêutica personalizada a um paciente.
     * Apenas o terapeuta atribuído pode definir missões.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignMission(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:users,id',
            'mission_id' => 'required|exists:missions,id',
        ]);

        $therapist = Therapist::whereHas('patients', function ($q) use ($validated) {
            $q->where('user_id', $validated['patient_id']);
        })->first();

        if (!$therapist) {
            return response()->json(['error' => 'Não és o terapeuta deste paciente.'], 403);
        }

        $patient = \App\Models\User::findOrFail($validated['patient_id']);

        // Evitar duplicação de missão no mesmo dia
        $today = now()->toDateString();
        $alreadyAssigned = $patient->missions()
            ->where('mission_id', $validated['mission_id'])
            ->wherePivot('assigned_date', $today)
            ->exists();

        if ($alreadyAssigned) {
            return response()->json(['message' => 'Missão já atribuída hoje.'], 409);
        }

        $patient->missions()->attach($validated['mission_id'], [
            'assigned_date' => $today,
            'progress' => 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Missão atribuída com sucesso.']);
    }

    /**
     * Dispara um evento de co-regulação somática via WebSocket
     * para sincronizar exercícios em tempo real com o paciente.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function triggerSomaticSync(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|integer',
            'exercise' => 'required|string|in:breathing,grounding,heartbeat',
            'bpm' => 'nullable|integer|min:40|max:120',
        ]);

        broadcast(new SomaticSyncTriggered(
            $validated['session_id'],
            $validated['exercise'],
            $validated['bpm'] ?? 60,
            Auth::id()
        ));

        return response()->json(['success' => true]);
    }
}
