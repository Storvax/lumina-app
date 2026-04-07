<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Therapist;
use App\Models\TherapySession;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TherapySessionController extends Controller
{
    /**
     * Lista as sessões agendadas do paciente autenticado.
     */
    public function index(): View
    {
        $sessions = TherapySession::where('patient_id', Auth::id())
            ->with('therapist.user')
            ->orderBy('scheduled_at')
            ->get();

        return view('scheduling.index', compact('sessions'));
    }

    /**
     * Formulário de agendamento: mostra o terapeuta e os slots disponíveis
     * para os próximos 14 dias com base na disponibilidade configurada.
     */
    public function create(Therapist $therapist): View
    {
        $slots = $this->buildAvailableSlots($therapist);

        return view('scheduling.book', compact('therapist', 'slots'));
    }

    /**
     * Regista um novo pedido de sessão (estado: pending).
     * O terapeuta confirma manualmente no seu portal.
     */
    public function store(Request $request, Therapist $therapist): RedirectResponse
    {
        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'session_type' => ['required', 'in:video,in_person'],
            'patient_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Evitar duplo agendamento no mesmo slot
        $conflict = TherapySession::where('therapist_id', $therapist->id)
            ->where('scheduled_at', $validated['scheduled_at'])
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['scheduled_at' => 'Este horário já está reservado. Por favor escolhe outro.']);
        }

        TherapySession::create([
            'therapist_id'  => $therapist->id,
            'patient_id'    => Auth::id(),
            'scheduled_at'  => $validated['scheduled_at'],
            'session_type'  => $validated['session_type'],
            'patient_notes' => $validated['patient_notes'],
            'status'        => 'pending',
        ]);

        return redirect()->route('sessions.index')
            ->with('success', 'Pedido de sessão enviado. Aguarda confirmação do terapeuta.');
    }

    /**
     * Cancela uma sessão pelo paciente.
     * Só permitido com pelo menos 2 horas de antecedência.
     */
    public function cancel(Request $request, TherapySession $session): RedirectResponse
    {
        if ($session->patient_id !== Auth::id()) {
            abort(403);
        }

        if ($session->scheduled_at->diffInHours(now()) < 2) {
            return back()->withErrors(['cancel' => 'Só é possível cancelar com pelo menos 2 horas de antecedência.']);
        }

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $session->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancelled_by'        => 'patient',
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
        ]);

        return redirect()->route('sessions.index')
            ->with('success', 'Sessão cancelada com sucesso.');
    }

    /**
     * Entra na videochamada Jitsi da sessão.
     * Acesso só permitido ao paciente ou terapeuta da sessão,
     * dentro da janela de 10 min antes até 30 min após o início.
     */
    public function videoCall(TherapySession $session): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $isPatient   = $session->patient_id === $user->id;
        $isTherapist = $user->therapistProfile && $session->therapist_id === $user->therapistProfile->id;

        if (! $isPatient && ! $isTherapist) {
            abort(403);
        }

        if (! $session->isVideoCallAccessible()) {
            return redirect()->route('sessions.index')
                ->withErrors(['video' => 'A videochamada ainda não está disponível. Volta 10 minutos antes do início.']);
        }

        return view('scheduling.video-call', [
            'session'  => $session,
            'userName' => $user->name,
            // Servidor Jitsi público — pode ser substituído por instância self-hosted
            'jitsiDomain' => config('services.jitsi.domain', 'meet.jit.si'),
        ]);
    }

    /**
     * Calcula slots de 50 minutos disponíveis para os próximos 14 dias
     * com base na disponibilidade semanal do terapeuta.
     *
     * @return array<int, array<string, string>>
     */
    private function buildAvailableSlots(Therapist $therapist): array
    {
        $availability = $therapist->availability()->where('is_active', true)->get();

        // Sessões já agendadas (para excluir conflitos)
        $booked = TherapySession::where('therapist_id', $therapist->id)
            ->whereNotIn('status', ['cancelled'])
            ->where('scheduled_at', '>=', now())
            ->pluck('scheduled_at')
            ->map(fn ($dt) => Carbon::parse($dt)->format('Y-m-d H:i'))
            ->toArray();

        $slots = [];
        $today = Carbon::today();

        for ($i = 0; $i < 14; $i++) {
            $date = $today->copy()->addDays($i);
            $dayOfWeek = (int) $date->dayOfWeek;

            $dayAvailability = $availability->where('day_of_week', $dayOfWeek)->first();

            if (! $dayAvailability) {
                continue;
            }

            $start = Carbon::parse($date->format('Y-m-d') . ' ' . $dayAvailability->start_time);
            $end   = Carbon::parse($date->format('Y-m-d') . ' ' . $dayAvailability->end_time);

            while ($start->copy()->addMinutes(50)->lte($end)) {
                if ($start->isAfter(now()->addHour())) {
                    $slotKey = $start->format('Y-m-d H:i');

                    if (! in_array($slotKey, $booked, true)) {
                        $slots[] = [
                            'value' => $start->format('Y-m-d H:i:s'),
                            'label' => $start->translatedFormat('l, d \d\e F — H:i'),
                        ];
                    }
                }

                $start->addMinutes(50);
            }
        }

        return $slots;
    }
}
