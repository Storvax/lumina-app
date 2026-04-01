<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ClinicalNote;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClinicalNoteController extends Controller
{
    /**
     * Lista as notas clínicas do terapeuta autenticado para um paciente específico.
     * Acesso restrito ao terapeuta atribuído — verificado pela policy.
     */
    public function index(User $patient): View
    {
        $therapist = Auth::user()->therapistProfile;

        if (!$therapist) {
            abort(403, 'Perfil de terapeuta não encontrado.');
        }

        // Confirma que o paciente está atribuído a este terapeuta.
        $isAssigned = $therapist->patients()->where('users.id', $patient->id)->exists();
        if (!$isAssigned) {
            abort(403, 'Este paciente não está atribuído ao teu perfil.');
        }

        $notes = ClinicalNote::where('therapist_id', $therapist->id)
            ->where('patient_id', $patient->id)
            ->orderBy('session_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('therapist.clinical-notes.index', compact('patient', 'notes'));
    }

    /**
     * Guarda uma nova nota clínica encriptada.
     * O conteúdo é encriptado automaticamente pelo cast Eloquent antes de persistir.
     */
    public function store(Request $request, User $patient): RedirectResponse
    {
        $this->authorize('create', ClinicalNote::class);

        $therapist = Auth::user()->therapistProfile;

        if (!$therapist) {
            abort(403, 'Perfil de terapeuta não encontrado.');
        }

        $isAssigned = $therapist->patients()->where('users.id', $patient->id)->exists();
        if (!$isAssigned) {
            abort(403, 'Este paciente não está atribuído ao teu perfil.');
        }

        $validated = $request->validate([
            'content'      => ['required', 'string', 'min:10', 'max:10000'],
            'session_date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        ClinicalNote::create([
            'therapist_id' => $therapist->id,
            'patient_id'   => $patient->id,
            'content'      => $validated['content'],
            'session_date' => $validated['session_date'] ?? now()->toDateString(),
        ]);

        return redirect()->route('clinical-notes.index', $patient)
            ->with('success', 'Nota clínica guardada com segurança.');
    }

    /**
     * Mostra formulário de edição de uma nota existente.
     */
    public function edit(User $patient, ClinicalNote $clinicalNote): View
    {
        $this->authorize('update', $clinicalNote);

        return view('therapist.clinical-notes.edit', compact('patient', 'clinicalNote'));
    }

    /**
     * Atualiza o conteúdo de uma nota clínica existente.
     */
    public function update(Request $request, User $patient, ClinicalNote $clinicalNote): RedirectResponse
    {
        $this->authorize('update', $clinicalNote);

        $validated = $request->validate([
            'content'      => ['required', 'string', 'min:10', 'max:10000'],
            'session_date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $clinicalNote->update([
            'content'      => $validated['content'],
            'session_date' => $validated['session_date'] ?? $clinicalNote->session_date,
        ]);

        return redirect()->route('clinical-notes.index', $patient)
            ->with('success', 'Nota clínica atualizada.');
    }

    /**
     * Soft-delete da nota — mantém histórico auditável conforme RGPD.
     */
    public function destroy(User $patient, ClinicalNote $clinicalNote): RedirectResponse
    {
        $this->authorize('delete', $clinicalNote);

        $clinicalNote->delete();

        return redirect()->route('clinical-notes.index', $patient)
            ->with('success', 'Nota clínica removida.');
    }
}
