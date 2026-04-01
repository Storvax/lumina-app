<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Registar novo utilizador via API móvel.
     * POST /api/v1/auth/register
     *
     * Replica a lógica de RegisteredUserController mas para API,
     * devolvendo token Sanctum em vez de redirecionar.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password'              => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Disparar evento de registo (envia email de verificação via queue)
        event(new Registered($user));

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ], 201);
    }

    /**
     * Fazer login e retornar token Sanctum.
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Validação realizada pelo FormRequest
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'As credenciais não correspondem aos nossos registos.',
            ], 401);
        }

        $user = Auth::user();

        // Atualizar streak de login (mesma lógica da web)
        $today = now()->startOfDay();
        $lastActivity = $user->last_activity_at ? \Carbon\Carbon::parse($user->last_activity_at)->startOfDay() : null;

        if (!$lastActivity || $lastActivity->lessThan($today)) {
            if ($lastActivity && $lastActivity->equalTo($today->copy()->subDay())) {
                $user->increment('current_streak');
            } elseif (!$lastActivity || $lastActivity->lessThan($today->copy()->subDay())) {
                $user->current_streak = 1;
            }
            $user->last_activity_at = now();
            $user->save();
        }

        // Gerar token Sanctum
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ], 200);
    }

    /**
     * Fazer logout e revocar token.
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        // Revogar todos os tokens do utilizador
        $request->user('sanctum')->tokens()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ], 200);
    }

    /**
     * Obter o utilizador autenticado.
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user('sanctum')),
        ], 200);
    }
}
