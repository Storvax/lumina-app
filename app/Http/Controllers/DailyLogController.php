<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Services\GamificationService; // <--- Importado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailyLogController extends Controller
{
    protected $gamification;

    // Injeção de dependência do serviço de Gamificação
    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    public function index()
    {
        $user = Auth::user();
        
        // Verifica se já escreveu hoje
        $todayLog = DailyLog::where('user_id', $user->id)
                            ->where('log_date', Carbon::today())
                            ->first();

        // Busca os últimos 7 dias para o histórico visual
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

        $log = DailyLog::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'log_date' => Carbon::today(),
            ],
            [
                'mood_level' => $request->mood_level,
                'tags' => $request->tags,
                'note' => $request->note,
            ]
        );

        // --- GAMIFICAÇÃO ---
        // Apenas recompensa se for um novo registo (evita spam de updates no mesmo dia)
        if ($log->wasRecentlyCreated) {
            $this->gamification->trackAction(Auth::user(), 'daily_log');
        }

        return redirect()->route('diary.index')->with('success', 'Diário atualizado com sucesso!');
    }
}