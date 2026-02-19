<?php

namespace App\Services;

use Illuminate\Support\Str;

class CBTAnalysisService
{
    /**
     * Analisa a entrada do diário e tenta identificar distorções cognitivas.
     * Pode ser substituído por uma API de LLM (OpenAI/Gemini) no futuro.
     */
    public function analyze(?string $text)
    {
        if (empty($text)) return null;

        $text = Str::lower($text);

        // 1. Pensamento Tudo-ou-Nada
        if (Str::containsAll($text, ['nunca']) || Str::containsAll($text, ['sempre']) || Str::containsAll($text, ['tudo estragado'])) {
            return [
                'type' => 'Tudo ou Nada',
                'message' => 'Identifiquei um padrão de pensamento "tudo ou nada" (extremismo). Queres explorar isto?',
                'prompts' => [
                    'Há alguma pequena exceção a esta regra do "sempre" ou "nunca"?',
                    'Se um amigo estivesse nesta situação, dirias a ele que está tudo perdido?',
                    'Qual seria um meio-termo realista para o que aconteceu?'
                ]
            ];
        }

        // 2. Personalização / Culpa
        if (Str::containsAll($text, ['minha culpa']) || Str::containsAll($text, ['sou um peso']) || Str::containsAll($text, ['estraguei tudo'])) {
            return [
                'type' => 'Autocrítica / Personalização',
                'message' => 'Notei um tom de forte autocrítica. Muitas vezes assumimos a culpa por coisas que não controlamos 100%.',
                'prompts' => [
                    'Que outros fatores (pessoas, circunstâncias) podem ter contribuído para este resultado?',
                    'Estás a julgar-te com uma severidade que não usarias com outros?',
                    'O que dirias a uma criança que cometesse este mesmo erro?'
                ]
            ];
        }

        // 3. Leitura de Mentes / Ansiedade Social
        if (Str::containsAll($text, ['estão a pensar']) || Str::containsAll($text, ['ninguém gosta']) || Str::containsAll($text, ['vão achar'])) {
            return [
                'type' => 'Leitura de Mentes',
                'message' => 'Parece que estás a tentar "ler a mente" dos outros. A ansiedade faz-nos assumir o pior.',
                'prompts' => [
                    'Tens provas reais e concretas de que as pessoas pensam isso de ti?',
                    'Existe alguma outra explicação, mais neutra, para a atitude deles?',
                    'Se eles realmente pensarem isso, como podes lidar com a situação de forma saudável?'
                ]
            ];
        }

        return null; // Nenhuma distorção clara detetada
    }
}