<?php

namespace App\Http\Controllers;

use App\Models\PactPrompt;
use App\Models\PactAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PactController extends Controller
{
    /**
     * Diário do Pacto — prompt do dia + respostas anónimas da comunidade.
     */
    public function index()
    {
        // Prompt do dia: roda entre prompts ativos usando o dia do ano
        $prompts = PactPrompt::where('is_active', true)->orderBy('id')->get();

        $todayPrompt = $prompts->isNotEmpty()
            ? $prompts[now()->dayOfYear % $prompts->count()]
            : null;

        // Respostas anónimas do dia (de outros utilizadores)
        $communityAnswers = $todayPrompt
            ? PactAnswer::where('pact_prompt_id', $todayPrompt->id)
                ->where('answer_date', now()->toDateString())
                ->where('is_anonymous', true)
                ->latest()
                ->take(20)
                ->get()
            : collect();

        // Resposta do utilizador atual (se já respondeu hoje)
        $myAnswer = $todayPrompt
            ? PactAnswer::where('user_id', Auth::id())
                ->where('pact_prompt_id', $todayPrompt->id)
                ->where('answer_date', now()->toDateString())
                ->first()
            : null;

        return view('calm.pact', compact('todayPrompt', 'communityAnswers', 'myAnswer'));
    }

    /**
     * Guardar resposta ao prompt do dia.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pact_prompt_id' => 'required|exists:pact_prompts,id',
            'body' => 'required|string|max:2000',
            'is_anonymous' => 'sometimes|boolean',
        ]);

        $answer = Auth::user()->pactAnswers()->updateOrCreate(
            [
                'pact_prompt_id' => $validated['pact_prompt_id'],
                'answer_date' => now()->toDateString(),
            ],
            [
                'body' => $validated['body'],
                'is_anonymous' => $request->boolean('is_anonymous', true),
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'answer' => $answer]);
        }

        return back()->with('success', 'A tua reflexão foi guardada.');
    }
}
