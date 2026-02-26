<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Services\GamificationService;
use App\Services\CBTAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

/**
 * Gere a lógica do Diário Emocional, incluindo a gravação de entradas
 * e a integração com o serviço de análise cognitiva (CBT).
 */
class DailyLogController extends Controller
{
    protected GamificationService $gamification;
    protected CBTAnalysisService $cbtService;

    public function __construct(GamificationService $gamification, CBTAnalysisService $cbtService)
    {
        $this->gamification = $gamification;
        $this->cbtService = $cbtService;
    }

    /**
     * Exibe a vista do diário, carregando o registo de hoje e o histórico da última semana.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        $todayLog = DailyLog::where('user_id', $user->id)
            ->where('log_date', Carbon::today())
            ->first();

        $history = DailyLog::where('user_id', $user->id)
            ->where('log_date', '>=', Carbon::today()->subDays(6))
            ->orderBy('log_date', 'asc')
            ->get();

        return view('diary.index', compact('todayLog', 'history'));
    }

    /**
     * Guarda ou atualiza a entrada do diário do dia atual.
     * Aciona a análise de Terapia Cognitivo-Comportamental (CBT) ao texto introduzido.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'mood_level' => 'required|integer|min:1|max:5',
            'tags' => 'nullable|array',
            'note' => 'nullable|string|max:2000',
        ]);

        $insight = $this->cbtService->analyze($request->note);

        $log = DailyLog::updateOrCreate(
            [
                'user_id'  => Auth::id(),
                'log_date' => Carbon::today(),
            ],
            [
                'mood_level'  => $request->mood_level,
                'tags'        => $request->tags ?? [],
                'note'        => $request->note,
                'cbt_insight' => $insight,
            ]
        );

        if ($log->wasRecentlyCreated) {
            $this->gamification->trackAction(Auth::user(), 'daily_log');
        }

        return redirect()->route('diary.index')->with('success', 'Diário atualizado com sucesso!');
    }
}