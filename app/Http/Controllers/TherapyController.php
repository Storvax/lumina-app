<?php

namespace App\Http\Controllers;

use App\Models\Therapist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TherapyController extends Controller
{
    /**
     * Página inicial da triagem terapêutica.
     */
    public function index()
    {
        return view('therapy.index');
    }

    /**
     * Processa mensagens na triagem conversacional com OpenAI.
     *
     * O modelo devolve JSON com `status` ("ongoing" ou "complete")
     * e `keywords` (lista de especialidades/abordagens identificadas).
     * Quando `status: complete`, procuramos terapeutas compatíveis
     * e devolvemos os perfis ao frontend.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function matchChat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:2000',
        ]);

        $messages = [
            [
                'role' => 'system',
                'content' => 'És um assistente de triagem terapêutica empático. Faz perguntas abertas para compreender as necessidades do utilizador (máx. 3-4 trocas). Quando tiveres informação suficiente, devolve um JSON com: {"status": "complete", "keywords": ["keyword1", "keyword2"], "summary": "resumo breve"}. Enquanto precisares de mais contexto, devolve: {"status": "ongoing", "reply": "a tua pergunta seguinte"}. As keywords devem ser termos como: ansiedade, depressão, burnout, trauma, PTSD, luto, TDAH, pânico, relações, TCC, EMDR, somática, compaixão, integrativa, psicodinâmica. Responde em Português de Portugal.',
            ],
        ];

        if (!empty($validated['history'])) {
            foreach ($validated['history'] as $turn) {
                $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $validated['message']];

        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => $messages,
                    'max_tokens' => 300,
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Não foi possível processar a triagem.',
                ], 502);
            }

            $content = $response->json('choices.0.message.content');
            $parsed = json_decode($content, true);

            // Se a IA não devolver JSON válido, tratar como resposta de texto
            if (!$parsed || !isset($parsed['status'])) {
                return response()->json([
                    'status' => 'ongoing',
                    'reply' => $content,
                ]);
            }

            if ($parsed['status'] === 'complete' && !empty($parsed['keywords'])) {
                $therapists = $this->findMatchingTherapists($parsed['keywords']);

                return response()->json([
                    'status' => 'complete',
                    'summary' => $parsed['summary'] ?? '',
                    'therapists' => $therapists,
                ]);
            }

            return response()->json([
                'status' => 'ongoing',
                'reply' => $parsed['reply'] ?? $content,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'error' => 'Serviço temporariamente indisponível.',
            ], 503);
        }
    }

    /**
     * Procura terapeutas cuja especialidade ou abordagem
     * corresponda às keywords extraídas da triagem IA.
     * Usa LIKE para correspondência parcial com tolerância a variações.
     */
    private function findMatchingTherapists(array $keywords): \Illuminate\Support\Collection
    {
        return Therapist::where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $query->orWhere('specialty', 'like', "%{$keyword}%")
                      ->orWhere('approach', 'like', "%{$keyword}%");
            }
        })->get();
    }
}
