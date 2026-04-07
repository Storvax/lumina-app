<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TherapistAvailability;
use App\Models\TherapySession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TherapistScheduleController extends Controller
{
    /**
     * Agenda do terapeuta: sessões agendadas + gestão de disponibilidade.
     */
    public function index(): View
    {
        $therapist = Auth::user()->therapistProfile;

        if (! $therapist) {
            abort(403);
        }

        $sessions = TherapySession::where('therapist_id', $therapist->id)
            ->with('patient')
            ->orderBy('scheduled_at')
            ->get();

        $availability = $therapist->availability()->orderBy('day_of_week')->get();

        return view('therapist.schedule.index', compact('sessions', 'availability', 'therapist'));
    }

    /**
     * Confirma um pedido de sessão pendente.
     * Ao confirmar sessão de vídeo, gera o token da sala Jitsi.
     */
    public function confirm(TherapySession $session): RedirectResponse
    {
        $therapist = Auth::user()->therapistProfile;

        if (! $therapist || $session->therapist_id !== $therapist->id) {
            abort(403);
        }

        $updates = ['status' => 'confirmed'];

        // Gera token único para a sala Jitsi apenas em sessões de vídeo
        if ($session->session_type === 'video' && ! $session->video_room_token) {
            $updates['video_room_token'] = 'lumina-' . Str::uuid()->toString();
        }

        $session->update($updates);

        return back()->with('success', 'Sessão confirmada com sucesso.');
    }

    /**
     * Cancela uma sessão pelo terapeuta.
     */
    public function cancel(Request $request, TherapySession $session): RedirectResponse
    {
        $therapist = Auth::user()->therapistProfile;

        if (! $therapist || $session->therapist_id !== $therapist->id) {
            abort(403);
        }

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $session->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancelled_by'        => 'therapist',
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
        ]);

        return back()->with('success', 'Sessão cancelada.');
    }

    /**
     * Marca uma sessão como concluída.
     */
    public function complete(TherapySession $session): JsonResponse
    {
        $therapist = Auth::user()->therapistProfile;

        if (! $therapist || $session->therapist_id !== $therapist->id) {
            abort(403);
        }

        $session->update(['status' => 'completed']);

        return response()->json(['success' => true]);
    }

    /**
     * Guarda a disponibilidade semanal do terapeuta.
     * Substitui todos os registos existentes para garantir consistência.
     */
    public function updateAvailability(Request $request): RedirectResponse
    {
        $therapist = Auth::user()->therapistProfile;

        if (! $therapist) {
            abort(403);
        }

        $validated = $request->validate([
            'slots'                  => ['required', 'array'],
            'slots.*.day_of_week'    => ['required', 'integer', 'between:0,6'],
            'slots.*.start_time'     => ['required', 'date_format:H:i'],
            'slots.*.end_time'       => ['required', 'date_format:H:i', 'after:slots.*.start_time'],
        ]);

        // Substitição total — mais simples e consistente do que diff incremental
        $therapist->availability()->delete();

        foreach ($validated['slots'] as $slot) {
            TherapistAvailability::create([
                'therapist_id' => $therapist->id,
                'day_of_week'  => $slot['day_of_week'],
                'start_time'   => $slot['start_time'],
                'end_time'     => $slot['end_time'],
                'is_active'    => true,
            ]);
        }

        return back()->with('success', 'Disponibilidade atualizada com sucesso.');
    }
}
