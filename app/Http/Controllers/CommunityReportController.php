<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Relatório público de impacto comunitário.
 *
 * Exibe estatísticas anónimas e agregadas sobre a comunidade Lumina.
 * Nenhum dado pessoal é exposto — apenas contadores e médias.
 */
class CommunityReportController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('community_report', 3600, function () {
            $last30 = now()->subDays(30);

            return [
                'total_members' => User::count(),
                'active_members' => User::where('last_activity_at', '>=', now()->subDays(7))->count(),
                'diary_entries' => DailyLog::where('created_at', '>=', $last30)->count(),
                'forum_posts' => Post::where('created_at', '>=', $last30)->count(),
                'hope_posts' => Post::where('tag', 'hope')->where('created_at', '>=', $last30)->count(),
                'avg_mood' => round(DailyLog::where('created_at', '>=', $last30)->avg('mood_level') ?? 0, 1),
                'reactions_given' => DB::table('post_reactions')->where('created_at', '>=', $last30)->count(),
                'buddy_sessions' => DB::table('buddy_sessions')
                    ->where('status', 'completed')
                    ->where('created_at', '>=', $last30)
                    ->count(),
            ];
        });

        return view('community.report', compact('stats'));
    }
}
