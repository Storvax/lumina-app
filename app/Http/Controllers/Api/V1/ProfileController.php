<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Obter perfil do utilizador autenticado.
     * GET /api/v1/profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        return response()->json([
            'user' => new UserResource($user),
        ], 200);
    }
}
