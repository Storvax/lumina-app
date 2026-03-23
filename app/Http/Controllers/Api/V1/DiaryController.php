<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDiaryRequest;
use App\Http\Resources\DailyLogResource;
use App\Models\DailyLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiaryController extends Controller
{
    /**
     * Listar entradas de diário do utilizador (com paginação).
     * GET /api/v1/diary?page=1&per_page=20
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $logs = $user->dailyLogs()
            ->orderBy('log_date', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => DailyLogResource::collection($logs),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
            ],
        ], 200);
    }

    /**
     * Criar nova entrada de diário.
     * POST /api/v1/diary
     */
    public function store(StoreDiaryRequest $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $log = $user->dailyLogs()->create([
            'mood_level' => $request->mood_level,
            'tags' => $request->tags ?? [],
            'note' => $request->note,
            'log_date' => now()->startOfDay(),
            'cbt_insight' => $this->generateCbtInsight($request->mood_level),
        ]);

        return response()->json([
            'data' => new DailyLogResource($log),
            'message' => 'Entrada de diário criada com sucesso.',
        ], 201);
    }

    /**
     * Gerar insight CBT baseado no nível de humor.
     * (Simplificado — em produção, seria mais sofisticado)
     */
    private function generateCbtInsight(int $moodLevel): array
    {
        $insights = [
            1 => [
                'title' => 'Identificar pensamentos automáticos',
                'description' => 'Quando os sentimentos são intensos, os pensamentos tendem a ser automáticos. Tenta identificar 1 pensamento que surgiu.',
            ],
            2 => [
                'title' => 'Validar as tuas emoções',
                'description' => 'É normal sentir-se assim às vezes. A aceitação é o primeiro passo para a mudança.',
            ],
            3 => [
                'title' => 'Manutenção é importante',
                'description' => 'Mantém as rotinas que funcionam para ti. Pequenos passos levam a grandes resultados.',
            ],
            4 => [
                'title' => 'Continua assim',
                'description' => 'Estás num bom caminho. Aproveita este momento para reflexão e planeamento.',
            ],
            5 => [
                'title' => 'Celebra os progressos',
                'description' => 'Está tudo bem! Aprecia este momento e mantém as práticas que te ajudam.',
            ],
        ];

        return $insights[$moodLevel] ?? [];
    }
}
