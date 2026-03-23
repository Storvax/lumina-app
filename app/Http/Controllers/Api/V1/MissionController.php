<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    /**
     * Listar missões do utilizador.
     * GET /api/v1/missions?status=active
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $query = $user->missions();

        // Filtro por status
        if ($request->get('status') === 'completed') {
            $query->wherePivot('completed_at', '!=', null);
        } elseif ($request->get('status') === 'active') {
            $query->wherePivot('completed_at', null);
        }

        $missions = $query->get();

        return response()->json([
            'data' => MissionResource::collection($missions),
        ], 200);
    }

    /**
     * Atualizar progresso de missão.
     * PATCH /api/v1/missions/{mission_id}/progress
     */
    public function updateProgress(Request $request, int $mission_id): JsonResponse
    {
        $user = $request->user('sanctum');

        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $mission = $user->missions()->find($mission_id);

        if (!$mission) {
            return response()->json([
                'message' => 'Missão não encontrada.',
            ], 404);
        }

        // Atualizar pivot
        $mission->pivot->update(['progress' => $validated['progress']]);

        // Se progress = 100, marcar como completada
        if ($validated['progress'] == 100) {
            $mission->pivot->update(['completed_at' => now()]);
        }

        return response()->json([
            'data' => new MissionResource($mission->refresh()),
            'message' => 'Progresso da missão atualizado.',
        ], 200);
    }
}
