<?php

use App\Models\Achievement;
use App\Models\Mission;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(GamificationService::class);
    $this->user = User::factory()->create([
        'flames' => 0,
        'current_streak' => 0,
        'last_activity_at' => null,
    ]);
});

// --- trackAction: atribuição de chamas ---

it('atribui chamas corretas para daily_log', function () {
    $this->service->trackAction($this->user, 'daily_log');

    expect($this->user->fresh()->flames)->toBe(10);
});

it('atribui chamas corretas para reaction', function () {
    $this->service->trackAction($this->user, 'reaction');

    expect($this->user->fresh()->flames)->toBe(2);
});

it('atribui chamas corretas para first_post', function () {
    $this->service->trackAction($this->user, 'first_post');

    expect($this->user->fresh()->flames)->toBe(20);
});

it('nao atribui chamas para acao desconhecida', function () {
    $this->service->trackAction($this->user, 'acao_inexistente');

    expect($this->user->fresh()->flames)->toBe(0);
});

// --- updateGentleStreak ---

it('inicia streak em 1 na primeira atividade', function () {
    $this->service->trackAction($this->user, 'daily_log');

    expect($this->user->fresh()->current_streak)->toBe(1);
});

it('incrementa streak quando atividade foi ontem', function () {
    $this->user->update([
        'current_streak' => 3,
        'last_activity_at' => now()->subDay(),
    ]);

    $this->service->trackAction($this->user, 'daily_log');

    expect($this->user->fresh()->current_streak)->toBe(4);
});

it('reinicia streak sem culpa quando última atividade foi há 2+ dias', function () {
    $this->user->update([
        'current_streak' => 10,
        'last_activity_at' => now()->subDays(2),
    ]);

    $this->service->trackAction($this->user, 'daily_log');

    // Recomeço sem punição — o streak volta a 1, não a 0.
    expect($this->user->fresh()->current_streak)->toBe(1);
});

it('nao altera streak para acoes que nao sao daily_log', function () {
    $this->user->update(['current_streak' => 5, 'last_activity_at' => now()->subDay()]);

    $this->service->trackAction($this->user, 'reaction');

    expect($this->user->fresh()->current_streak)->toBe(5);
});

// --- assignDailyMissions ---

it('atribui missoes diarias quando utilizador nao tem nenhuma hoje', function () {
    Mission::factory()->count(3)->create([
        'available_from'  => now()->subDay()->toDateString(),
        'available_until' => now()->addDay()->toDateString(),
    ]);

    $this->service->assignDailyMissions($this->user);

    expect($this->user->missions()->wherePivot('assigned_date', now()->toDateString())->count())->toBe(3);
});

it('nao duplica missoes se chamado duas vezes no mesmo dia', function () {
    Mission::factory()->count(3)->create([
        'available_from'  => now()->subDay()->toDateString(),
        'available_until' => now()->addDay()->toDateString(),
    ]);

    $this->service->assignDailyMissions($this->user);
    $this->service->assignDailyMissions($this->user);

    expect($this->user->missions()->wherePivot('assigned_date', now()->toDateString())->count())->toBe(3);
});

it('nao atribui missoes fora do periodo de disponibilidade', function () {
    Mission::factory()->count(3)->create([
        'available_from'  => now()->addDay()->toDateString(),
        'available_until' => now()->addDays(5)->toDateString(),
    ]);

    $this->service->assignDailyMissions($this->user);

    expect($this->user->missions()->wherePivot('assigned_date', now()->toDateString())->count())->toBe(0);
});

// --- checkMilestones: conquistas ---

it('desbloqueia conquista guardian_flame ao atingir 100 chamas', function () {
    Achievement::factory()->create(['slug' => 'guardian_flame', 'flames_reward' => 0]);

    $this->user->update(['flames' => 100]);
    $this->service->trackAction($this->user, 'reaction'); // dispara checkMilestones

    expect($this->user->achievements()->where('slug', 'guardian_flame')->exists())->toBeTrue();
});

it('nao duplica conquista se ja desbloqueada', function () {
    $achievement = Achievement::factory()->create(['slug' => 'guardian_flame', 'flames_reward' => 0]);
    $this->user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
    $this->user->update(['flames' => 100]);

    $this->service->trackAction($this->user, 'reaction');

    expect($this->user->achievements()->where('slug', 'guardian_flame')->count())->toBe(1);
});
