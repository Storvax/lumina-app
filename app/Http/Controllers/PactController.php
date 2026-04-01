<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PactAnswer;
use App\Models\PactPrompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Gere o Pacto do Dia: exibição do prompt ativo e submissão de respostas.
 * Extraído do ForumController para isolar a responsabilidade do Pacto.
 */
class PactController extends Controller
{
    /**
     * Exibe o pacto do dia com respostas comunitárias.
     * Rotação determinística por active_date; fallback cíclico se nenhum estiver agendado.
     */
    public function show(): View
    {
        $todayPrompt = PactPrompt::where('active_date', now()->toDateString())->first();

        // Fallback cíclico: roda entre todos os prompts por dia do ano
        if (!$todayPrompt) {
            $prompts     = PactPrompt::orderBy('id')->get();
            $todayPrompt = $prompts->isNotEmpty()
                ? $prompts[now()->dayOfYear % $prompts->count()]
                : null;
        }

        $communityAnswers = collect();
        $myAnswer         = null;

        if ($todayPrompt) {
            $communityAnswers = PactAnswer::where('pact_prompt_id', $todayPrompt->id)
                ->where('user_id', '!=', Auth::id())
                ->latest()
                ->take(20)
                ->get();

            $myAnswer = PactAnswer::where('user_id', Auth::id())
                ->where('pact_prompt_id', $todayPrompt->id)
                ->whereDate('created_at', now()->toDateString())
                ->first();
        }

        return view('calm.pact', compact('todayPrompt', 'communityAnswers', 'myAnswer'));
    }

    /**
     * Guarda a resposta do utilizador ao pacto do dia.
     * updateOrCreate garante uma única resposta por prompt por utilizador.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'pact_prompt_id' => 'required|exists:pact_prompts,id',
            'answer'         => 'required|string|max:2000',
        ]);

        $answer = Auth::user()->pactAnswers()->updateOrCreate(
            ['pact_prompt_id' => $validated['pact_prompt_id']],
            ['answer'         => $validated['answer']],
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'answer' => $answer]);
        }

        return back()->with('success', 'A tua reflexão foi guardada.');
    }
}
