<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\DailyLog;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class UserJourneyWidget extends Widget
{
    protected static string $view = 'filament.widgets.user-journey';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 3;

    public function getJourneyStats(): array
    {
        $last30 = now()->subDays(30);

        $newUsers = User::where('created_at', '>=', $last30)->count();
        $activeUsers = User::where('last_activity_at', '>=', now()->subDays(7))->count();
        $totalLogs = DailyLog::where('created_at', '>=', $last30)->count();
        $totalPosts = Post::where('created_at', '>=', $last30)->count();

        // Distribuição de mood
        $moodDistribution = DailyLog::where('created_at', '>=', $last30)
            ->selectRaw('mood_level, COUNT(*) as total')
            ->groupBy('mood_level')
            ->orderBy('mood_level')
            ->pluck('total', 'mood_level')
            ->toArray();

        // Top entry points (salas mais visitadas)
        $topRooms = DB::table('room_visits')
            ->join('rooms', 'rooms.id', '=', 'room_visits.room_id')
            ->where('room_visits.updated_at', '>=', $last30)
            ->selectRaw('rooms.name, COUNT(DISTINCT room_visits.user_id) as visitors')
            ->groupBy('rooms.name')
            ->orderByDesc('visitors')
            ->limit(5)
            ->get()
            ->toArray();

        return [
            'new_users' => $newUsers,
            'active_users' => $activeUsers,
            'total_logs' => $totalLogs,
            'total_posts' => $totalPosts,
            'mood_distribution' => $moodDistribution,
            'top_rooms' => $topRooms,
        ];
    }
}
