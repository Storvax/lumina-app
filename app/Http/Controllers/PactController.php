<?php

namespace App\Http\Controllers;

use App\Models\PactPrompt;
use App\Models\PactAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PactController extends Controller
{
    /**
     * Displays the daily pact prompt with anonymous community answers.
     * The prompt rotates deterministically based on the day of the year.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $prompts = PactPrompt::where('is_active', true)->orderBy('id')->get();

        $todayPrompt = $prompts->isNotEmpty()
            ? $prompts[now()->dayOfYear % $prompts->count()]
            : null;

        $communityAnswers = collect();
        $myAnswer = null;

        if ($todayPrompt) {
            $communityAnswers = PactAnswer::where('pact_prompt_id', $todayPrompt->id)
                ->where('answer_date', now()->toDateString())
                ->where('is_anonymous', true)
                ->where('user_id', '!=', Auth::id())
                ->latest()
                ->take(20)
                ->get();

            $myAnswer = PactAnswer::where('user_id', Auth::id())
                ->where('pact_prompt_id', $todayPrompt->id)
                ->where('answer_date', now()->toDateString())
                ->first();
        }

        return view('calm.pact', compact('todayPrompt', 'communityAnswers', 'myAnswer'));
    }

    /**
     * Stores or updates the user's answer to today's pact prompt.
     * Uses updateOrCreate to enforce one answer per user per prompt per day.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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
