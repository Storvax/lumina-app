<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class AIReflectionService
{
    /**
     * Envia uma mensagem ao "eu do futuro" via OpenAI e devolve a resposta.
     * O eu do futuro é uma versão mais sábia e compassiva do utilizador.
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function reply(User $user, string $message, array $history = []): string
    {
        $messages = [
            [
                'role'    => 'system',
                'content' => "És o 'eu do futuro' do utilizador — uma versão mais sábia, mais calma e mais compassiva dele/a daqui a 5 anos. "
                    . "Usa a primeira pessoa do plural ('Nós conseguimos', 'Nós sabemos'). "
                    . "Responde com empatia profunda, validação emocional e esperança realista. "
                    . "Nunca dês conselhos clínicos. Fala em Português de Portugal. "
                    . "Sê breve (2-4 frases). O nome do utilizador é {$user->name}.",
            ],
        ];

        // Histórico de turnos anteriores para contexto multi-turno.
        foreach ($history as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withToken(config('services.openai.api_key'))
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => config('services.openai.model', 'gpt-4o-mini'),
                'messages'    => $messages,
                'max_tokens'  => 250,
                'temperature' => 0.8,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI retornou erro: ' . $response->status());
        }

        return $response->json('choices.0.message.content');
    }
}
