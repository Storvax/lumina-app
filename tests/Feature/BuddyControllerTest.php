<?php

use App\Http\Middleware\EnsureOnboardingCompleted;
use App\Models\BuddySession;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Ignora o middleware de onboarding — estamos a testar a lógica do BuddyController.
beforeEach(fn () => $this->withoutMiddleware(EnsureOnboardingCompleted::class));

// --- requestBuddy ---

it('utilizador pode pedir apoio de um buddy', function () {
    $user = User::factory()->create(['is_buddy' => false]);

    $this->actingAs($user)
        ->post(route('buddy.request'))
        ->assertRedirect();

    expect(BuddySession::where('user_id', $user->id)->where('status', 'pending')->exists())->toBeTrue();
});

it('nao cria pedido duplicado se ja existe um pendente', function () {
    $user = User::factory()->create(['is_buddy' => false]);
    BuddySession::create(['user_id' => $user->id, 'status' => 'pending']);

    $this->actingAs($user)
        ->post(route('buddy.request'))
        ->assertRedirect();

    expect(BuddySession::where('user_id', $user->id)->count())->toBe(1);
});

// --- acceptSession ---

it('buddy pode aceitar sessao pendente', function () {
    $buddy = User::factory()->create(['is_buddy' => true]);
    $requester = User::factory()->create(['is_buddy' => false]);
    $session = BuddySession::create(['user_id' => $requester->id, 'status' => 'pending']);

    $this->actingAs($buddy)
        ->post(route('buddy.accept', $session))
        ->assertRedirect();

    expect($session->fresh()->status)->toBe('active')
        ->and($session->fresh()->buddy_id)->toBe($buddy->id);
});

it('buddy nao pode aceitar o proprio pedido', function () {
    $user = User::factory()->create(['is_buddy' => true]);
    $session = BuddySession::create(['user_id' => $user->id, 'status' => 'pending']);

    $this->actingAs($user)
        ->post(route('buddy.accept', $session))
        ->assertStatus(403);
});

it('utilizador sem is_buddy nao pode aceitar sessao', function () {
    $notBuddy = User::factory()->create(['is_buddy' => false]);
    $session = BuddySession::create(['user_id' => User::factory()->create()->id, 'status' => 'pending']);

    $this->actingAs($notBuddy)
        ->post(route('buddy.accept', $session))
        ->assertStatus(403);
});

// --- escalate ---

it('buddy atribuido pode escalar sessao ativa', function () {
    $buddy = User::factory()->create(['is_buddy' => true]);
    $room  = Room::factory()->create();
    $session = BuddySession::create([
        'user_id'  => User::factory()->create()->id,
        'buddy_id' => $buddy->id,
        'room_id'  => $room->id,
        'status'   => 'active',
    ]);

    $this->actingAs($buddy)
        ->post(route('buddy.escalate', $session))
        ->assertRedirect();

    expect($session->fresh()->status)->toBe('escalated');
});

it('buddy nao atribuido nao pode escalar sessao', function () {
    $otherBuddy = User::factory()->create(['is_buddy' => true]);
    $room       = Room::factory()->create();
    $session = BuddySession::create([
        'user_id'  => User::factory()->create()->id,
        'buddy_id' => User::factory()->create(['is_buddy' => true])->id,
        'room_id'  => $room->id,
        'status'   => 'active',
    ]);

    $this->actingAs($otherBuddy)
        ->post(route('buddy.escalate', $session))
        ->assertStatus(403);
});

// --- Painel do Buddy (dashboard) ---

it('buddy aprovado acede ao painel', function () {
    $buddy = User::factory()->create(['is_buddy' => true]);

    $this->actingAs($buddy)
        ->get(route('buddy.dashboard'))
        ->assertStatus(200);
});

it('utilizador normal nao acede ao painel do buddy', function () {
    $user = User::factory()->create(['is_buddy' => false]);

    $this->actingAs($user)
        ->get(route('buddy.dashboard'))
        ->assertStatus(403);
});
