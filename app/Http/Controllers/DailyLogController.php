<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Services\GamificationService;
use App\Services\CBTAnalysisService; // <--- NOVO
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailyLogController extends Controller
{
    protected $gamification;
    protected $cbtService;

    public function __construct(GamificationService $gamification, CBTAnalysisService $cbtService)
    {
        $this->gamification = $gamification;
        $this->cbtService = $cbtService; // <--- Injeção
    }

    public function index()
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

    public function store(Request $request)
    {
        $request->validate([
            'mood_level' => 'required|integer|min:1|max:5',
            'tags' => 'nullable|array',
            'note' => 'nullable|string|max:2000',
        ]);

        // Gerar Insight CBT
        $insight = $this->cbtService->analyze($request->note);

        $log = DailyLog::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'log_date' => Carbon::today(),
            ],
            [
                'mood_level' => $request->mood_level,
                'tags' => $request->tags ? json_encode($request->tags) : null,
                'note' => $request->note,
                'cbt_insight' => $insight ? json_encode($insight) : null, // <--- Guardar Insight
            ]
        );

        if ($log->wasRecentlyCreated) {
            $this->gamification->trackAction(Auth::user(), 'daily_log');
        }

        return redirect()->route('diary.index')->with('success', 'Diário atualizado com sucesso!');
    }
}