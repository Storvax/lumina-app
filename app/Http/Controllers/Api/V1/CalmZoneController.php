<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreVaultItemRequest;
use App\Http\Resources\VaultItemResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalmZoneController extends Controller
{
    /**
     * Listar items do cofre (Vault) do utilizador.
     * GET /api/v1/calm-zone/vault
     */
    public function vaultIndex(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $items = $user->vaultItems()->get();

        return response()->json([
            'data' => VaultItemResource::collection($items),
        ], 200);
    }

    /**
     * Criar novo item no cofre.
     * POST /api/v1/calm-zone/vault
     */
    public function vaultStore(StoreVaultItemRequest $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $item = $user->vaultItems()->create([
            'content' => $request->content,
        ]);

        return response()->json([
            'data' => new VaultItemResource($item),
            'message' => 'Item adicionado ao cofre com sucesso.',
        ], 201);
    }

    /**
     * Apagar item do cofre.
     * DELETE /api/v1/calm-zone/vault/{item_id}
     */
    public function vaultDestroy(Request $request, int $item_id): JsonResponse
    {
        $user = $request->user('sanctum');

        $item = $user->vaultItems()->find($item_id);

        if (!$item) {
            return response()->json([
                'message' => 'Item não encontrado.',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item removido do cofre.',
        ], 204);
    }
}
