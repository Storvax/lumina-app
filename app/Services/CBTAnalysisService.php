<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Motor Central de Inteligência Emocional.
 * Integra-se com LLMs para análise semântica, priorização de moderação e psicoeducação.
 */
class CBTAnalysisService
{
    /** @var array Camada 1: Deteção rápida (Zero-latency). */
    private const CRISIS_KEYWORDS = ['suicidio', 'suicídio', 'morte', 'sangue', 'cortar', 'abuso', 'violencia', 'matar', 'morrer'];

    /**
     * Analisa publicações do fórum através de um modelo multicamada (Keywords + NLP).
     * Retorna um dicionário com estado de sensibilidade, risco e sentimento.
     */
    public function analyzeForumPost(string $text): array
    {
        $hasKeywords = Str::contains(Str::lower($text), self::CRISIS_KEYWORDS);

        $defaultResponse = [
            'is_sensitive' => $hasKeywords,
            'risk_level' => $hasKeywords ? 'high' : 'low',
            'sentiment' => 'neutral'
        ];

        $apiKey = config('services.openai.key') ?? env('OPENAI_API_KEY');
        
        if (!$apiKey) {
            return $defaultResponse;
        }

        try {
            // Timeout de 3s garante que a UX não sofre se a API da OpenAI estiver lenta
            $response = Http::withToken($apiKey)
                ->timeout(3)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "És um algoritmo de triagem clínica. Analisa a intenção e o sentimento do texto. Devolve EXATAMENTE este JSON: {\"is_sensitive\": boolean (true se houver intenção de dano, crise ou abuso), \"risk_level\": \"low|medium|high\", \"sentiment\": \"positive|neutral|distress\"}."
                        ],
                        ['role' => 'user', 'content' => $text]
                    ]
                ]);

            if ($response->successful()) {
                $aiData = json_decode($response->json('choices.0.message.content'), true);
                
                // Camada de Segurança: A regra local (Keywords) sobrepõe-se à IA caso a IA falhe a identificação
                $aiData['is_sensitive'] = $aiData['is_sensitive'] || $hasKeywords;
                if ($hasKeywords && $aiData['risk_level'] === 'low') {
                    $aiData['risk_level'] = 'high';
                }
                
                return $aiData;
            }
        } catch (\Exception $e) {
            Log::error('Falha na API OpenAI (Mural NLP): ' . $e->getMessage());
        }

        return $defaultResponse;
    }

    /**
     * Analisa entradas de diário em busca de distorções cognitivas.
     * Substitui regex por prompts dinâmicos e adaptativos.
     */
    public function analyze(?string $text): ?array
    {
        if (empty($text)) return null;

        $apiKey = config('services.openai.key') ?? env('OPENAI_API_KEY');

        if (!$apiKey) {
            return $this->fallbackAnalysis($text);
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(5)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "És um assistente de psicoeducação (TCC/CBT). Não assumes o papel de terapeuta. Lê o texto e verifica se contém distorções cognitivas (ex: pensamento tudo-ou-nada, catastrofização). Se NÃO houver distorção clara, devolve um JSON vazio {}. Se houver, devolve EXATAMENTE este JSON: {\"type\": \"Nome da Distorção\", \"message\": \"Mensagem empática em PT-PT a validar o sentimento e identificar o padrão.\", \"prompts\": [\"Pergunta socrática de reflexão 1\", \"Pergunta socrática 2\"]}."
                        ],
                        ['role' => 'user', 'content' => $text]
                    ]
                ]);

            if ($response->successful()) {
                $data = json_decode($response->json('choices.0.message.content'), true);
                return empty($data) ? null : $data;
            }
        } catch (\Exception $e) {
            Log::error('Falha na API OpenAI (Diário CBT): ' . $e->getMessage());
        }

        return $this->fallbackAnalysis($text);
    }

    /**
     * Motor determinístico de recurso em caso de falha da IA.
     */
    private function fallbackAnalysis(string $text): ?array
    {
        $text = Str::lower($text);

        if (Str::containsAll($text, ['nunca']) || Str::containsAll($text, ['sempre'])) {
            return [
                'type' => 'Tudo ou Nada',
                'message' => 'Identifiquei um padrão de pensamento "tudo ou nada". O extremismo aumenta a ansiedade.',
                'prompts' => [
                    'Há alguma pequena exceção a esta regra do "sempre" ou "nunca"?',
                    'Qual seria um meio-termo realista para o que aconteceu?'
                ]
            ];
        }

        return null;
    }
}