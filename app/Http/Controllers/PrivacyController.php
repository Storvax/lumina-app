<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class PrivacyController extends Controller
{
    /**
     * Exibe a página de transparência e gestão de dados.
     */
    public function index()
    {
        $auditLogs = \App\Models\DataAccessLog::where('user_id', Auth::id())
            ->latest()
            ->get();
    
        return view('privacy.index', compact('auditLogs'));
    }
    /**
     * Exporta todos os dados do utilizador em formato JSON (Compliance RGPD).
     */
    public function exportData()
    {
        $user = Auth::user()->load(['dailyLogs', 'posts', 'comments', 'milestones']);

        $data = [
            'personal_info' => $user->only(['name', 'email', 'bio', 'safety_plan', 'created_at']),
            'emotional_tags' => $user->emotional_tags,
            'daily_logs' => $user->dailyLogs,
            'forum_posts' => $user->posts,
            'comments' => $user->comments,
            'milestones' => $user->milestones,
        ];

        $fileName = 'lumina_export_' . now()->format('Ymd_His') . '.json';

        return Response::make(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Pausa a conta do utilizador (Hibernação) sem apagar os dados.
     */
    public function hibernate(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        $user->update(['hibernated_at' => now()]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'A tua conta foi colocada em pausa. Podes voltar quando estiveres pronto, bastando fazer login novamente.');
    }
}