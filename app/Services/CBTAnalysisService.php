<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Motor Central de Inteligência Emocional.
 * Integra-se com LLMs para análise semântica, priorização de moderação e psicoeducação.
 *
 * Arquitetura de deteção de crise em três camadas:
 *   - Camada 1 (Keywords): Termos diretos de crise — zero latência.
 *   - Camada 2 (Intent): Frases que expressam intenção sem keywords explícitas.
 *   - Camada 3 (NLP): Análise semântica via LLM para casos ambíguos.
 */
class CBTAnalysisService
{
    /** @var array Camada 1: Termos diretos de deteção rápida (zero latência). */
    private const CRISIS_KEYWORDS = [
        'suicidio', 'suicídio', 'morte', 'sangue', 'cortar', 'abuso', 'violencia', 'matar', 'morrer',
    ];

    /**
     * Camada 2: Padrões de intenção — frases que indicam crise sem keywords diretas.
     * Inclui variantes com e sem acentuação para maior cobertura.
     *
     * @var array
     */
    private const INTENT_PATTERNS = [
        'nao aguento mais', 'não aguento mais',
        'quero desaparecer',
        'ninguem se importa', 'ninguém se importa',
        'seria melhor sem mim',
        'nao vale a pena', 'não vale a pena',
        'cansado de viver', 'cansada de viver',
        'nao consigo continuar', 'não consigo continuar',
        'quero acabar com isto',
        'nao vejo saida', 'não vejo saída',
        'ja nao faz sentido', 'já não faz sentido',
        'estou a mais',
        'nao tenho forca', 'não tenho força',
        'quem me dera nao acordar', 'quem me dera não acordar',
        'desistir de tudo',
    ];

    /**
     * Motor de deteção de crise unificado (Camadas 1 + 2).
     * Método reutilizável em qualquer ponto da aplicação (Fórum, Chat, Diário).
     *
     * @return array{detected: bool, level: string, type: string|null}
     */
    public function detectCrisis(string $text): array
    {
        $lower = Str::lower($text);
        $hasKeywords = Str::contains($lower, self::CRISIS_KEYWORDS);
        $hasIntent   = Str::contains($lower, self::INTENT_PATTERNS);

        return [
            'detected' => $hasKeywords || $hasIntent,
            'level'    => $hasKeywords ? 'critical' : ($hasIntent ? 'high' : 'none'),
            'type'     => $hasKeywords ? 'keyword' : ($hasIntent ? 'intent' : null),
        ];
    }

    /**
     * Analisa publicações do fórum através de um modelo multicamada (Keywords + Intent + NLP).
     * Retorna um dicionário com estado de sensibilidade, risco e sentimento.
     */
    public function analyzeForumPost(string $text): array
    {
        $lower          = Str::lower($text);
        $hasKeywords    = Str::contains($lower, self::CRISIS_KEYWORDS);
        $hasIntentPattern = Str::contains($lower, self::INTENT_PATTERNS);

        // Resposta local (Camadas 1+2) usada como fallback e como âncora de segurança
        $defaultResponse = [
            'is_sensitive' => $hasKeywords || $hasIntentPattern,
            'risk_level'   => ($hasKeywords || $hasIntentPattern) ? 'high' : 'low',
            'sentiment'    => 'neutral',
        ];

        $apiKey = config('services.openai.key') ?? env('OPENAI_API_KEY');

        if (!$apiKey) {
            return $defaultResponse;
        }

        try {
            // Timeout de 3s garante que a UX não sofre se a API estiver lenta
            $response = Http::withToken($apiKey)
                ->timeout(3)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role'    => 'system',
                            'content' => "És um algoritmo de triagem clínica. Analisa a intenção e o sentimento do texto. Devolve EXATAMENTE este JSON: {\"is_sensitive\": boolean (true se houver intenção de dano, crise ou abuso), \"risk_level\": \"low|medium|high\", \"sentiment\": \"positive|neutral|distress\"}.",
                        ],
                        ['role' => 'user', 'content' => $text],
                    ],
                ]);

            if ($response->successful()) {
                $aiData = json_decode($response->json('choices.0.message.content'), true);

                // Camada de Segurança: as regras locais sobrepõem-se à IA para garantir que
                // casos detetados pelas camadas 1 e 2 nunca são revertidos pela camada 3.
                $aiData['is_sensitive'] = $aiData['is_sensitive'] || $hasKeywords || $hasIntentPattern;
                if (($hasKeywords || $hasIntentPattern) && $aiData['risk_level'] === 'low') {
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