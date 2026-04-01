<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// --- Registo ---

it('regista novo utilizador e devolve token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Teste Lumina',
        'email'                 => 'novo@lumina.test',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
});

it('nao regista com email duplicado', function () {
    User::factory()->create(['email' => 'duplicado@lumina.test']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Outro',
        'email'                 => 'duplicado@lumina.test',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('nao regista com password fraca', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Fraco',
        'email'                 => 'fraco@lumina.test',
        'password'              => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

// --- Login ---

it('faz login com credenciais validas e devolve token', function () {
    User::factory()->create([
        'email'    => 'login@lumina.test',
        'password' => bcrypt('Password123!'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'login@lumina.test',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token', 'user']);
});

it('rejeita login com password errada', function () {
    User::factory()->create([
        'email'    => 'errado@lumina.test',
        'password' => bcrypt('CorrectPass123!'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'errado@lumina.test',
        'password' => 'WrongPass999!',
    ]);

    $response->assertStatus(401);
});

// --- Me ---

it('devolve o utilizador autenticado no endpoint me', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJsonPath('user.id', $user->id);
});

it('rejeita acesso ao endpoint me sem token', function () {
    $this->getJson('/api/v1/auth/me')->assertStatus(401);
});

// --- Logout ---

it('revoga o token no logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout')
        ->assertStatus(200);

    // Limpa cache do guard Sanctum para forçar re-autenticação na próxima request.
    // Sem este reset, o utilizador permanece em memória mesmo após a revogação do token.
    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me')
        ->assertStatus(401);
});
