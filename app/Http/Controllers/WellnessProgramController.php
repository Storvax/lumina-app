<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\WellnessProgram;
use App\Models\WellnessProgramParticipant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WellnessProgramController extends Controller
{
    /**
     * Lista os programas ativos da empresa do utilizador (vista do colaborador).
     */
    public function index(): View
    {
        $user     = Auth::user();
        $programs = WellnessProgram::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('starts_at')
            ->get();

        // IDs de programas em que o utilizador já está inscrito
        $enrolledIds = WellnessProgramParticipant::where('user_id', $user->id)
            ->pluck('wellness_program_id')
            ->toArray();

        return view('wellness.index', compact('programs', 'enrolledIds'));
    }

    /**
     * Inscreve o utilizador num programa de bem-estar.
     * Idempotente — ignora inscrições duplicadas.
     */
    public function enroll(WellnessProgram $program): RedirectResponse
    {
        $user = Auth::user();

        // Verifica que o programa pertence à empresa do utilizador.
        if ($program->company_id !== $user->company_id) {
            abort(403, 'Este programa não está disponível para a tua empresa.');
        }

        if ($program->status !== 'active') {
            return back()->with('error', 'Este programa já não está disponível para inscrição.');
        }

        WellnessProgramParticipant::firstOrCreate([
            'wellness_program_id' => $program->id,
            'user_id'             => $user->id,
        ]);

        return back()->with('success', 'Inscrito no programa com sucesso! Começa hoje.');
    }

    /**
     * Dashboard do programa de bem-estar para gestores de RH.
     * Apenas métricas anónimas e agregadas — nunca dados individuais.
     */
    public function dashboard(): View
    {
        $company  = Auth::user()->company;
        $programs = WellnessProgram::where('company_id', $company->id)
            ->orderBy('starts_at', 'desc')
            ->get();

        return view('wellness.dashboard', compact('company', 'programs'));
    }

    /**
     * Cria um novo programa de bem-estar para a empresa.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'                => ['required', 'string', 'max:150'],
            'description'          => ['nullable', 'string', 'max:1000'],
            'starts_at'            => ['required', 'date', 'after_or_equal:today'],
            'ends_at'              => ['required', 'date', 'after:starts_at'],
            'target_diary_days'    => ['nullable', 'integer', 'min:0', 'max:365'],
            'target_meditations'   => ['nullable', 'integer', 'min:0', 'max:365'],
        ]);

        WellnessProgram::create(array_merge($validated, [
            'company_id' => Auth::user()->company_id,
            'status'     => 'active',
        ]));

        return redirect()->route('wellness.dashboard')
            ->with('success', 'Programa de bem-estar criado com sucesso!');
    }
}
