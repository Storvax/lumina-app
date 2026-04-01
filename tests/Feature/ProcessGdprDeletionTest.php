<?php

use App\Jobs\ProcessGdprDeletion;
use App\Models\Achievement;
use App\Models\Comment;
use App\Models\DailyLog;
use App\Models\Post;
use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    $this->user = User::factory()->create([
        'email' => 'delete-me@lumina.test',
    ]);
});

it('elimina os daily logs do utilizador', function () {
    DailyLog::factory()->create(['user_id' => $this->user->id]);

    ProcessGdprDeletion::dispatchSync($this->user);

    expect(DailyLog::where('user_id', $this->user->id)->exists())->toBeFalse();
});

it('elimina as publicacoes do utilizador', function () {
    Post::factory()->create(['user_id' => $this->user->id]);

    ProcessGdprDeletion::dispatchSync($this->user);

    expect(Post::withoutGlobalScopes()->where('user_id', $this->user->id)->exists())->toBeFalse();
});

it('elimina os comentarios do utilizador', function () {
    $post = Post::factory()->create();
    Comment::factory()->create(['user_id' => $this->user->id, 'post_id' => $post->id]);

    ProcessGdprDeletion::dispatchSync($this->user);

    expect(Comment::where('user_id', $this->user->id)->exists())->toBeFalse();
});

it('elimina os itens do cofre do utilizador', function () {
    VaultItem::factory()->create(['user_id' => $this->user->id]);

    ProcessGdprDeletion::dispatchSync($this->user);

    expect(VaultItem::where('user_id', $this->user->id)->exists())->toBeFalse();
});

it('revoga os tokens Sanctum antes do forceDelete', function () {
    $this->user->createToken('test-token');

    ProcessGdprDeletion::dispatchSync($this->user);

    // O utilizador foi forceDeleted — os tokens são eliminados em cascata
    expect(User::withTrashed()->find($this->user->id))->toBeNull();
});

it('faz forceDelete do utilizador no final', function () {
    ProcessGdprDeletion::dispatchSync($this->user);

    expect(User::withTrashed()->find($this->user->id))->toBeNull();
});

it('desanexa conquistas do pivot user_achievements', function () {
    $achievement = Achievement::factory()->create();
    $this->user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);

    ProcessGdprDeletion::dispatchSync($this->user);

    expect(\Illuminate\Support\Facades\DB::table('user_achievements')->where('user_id', $this->user->id)->exists())->toBeFalse();
});
