<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class CommunityTemperatureWidget extends Widget
{
    protected static string $view = 'filament.widgets.community-temperature';
    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 1;

    public function getData(): array
    {
        return Cache::get('community_temperature', [
            'score' => 80,
            'level' => 'green',
            'high_risk_posts' => 0,
            'sensitive_messages' => 0,
            'avg_mood' => 3.0,
            'messages_per_hour' => 0,
            'calculated_at' => now()->toIso8601String(),
        ]);
    }
}
