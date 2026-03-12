<?php

namespace Database\Seeders;

use App\Models\PactPrompt;
use Illuminate\Database\Seeder;

class PactPromptSeeder extends Seeder
{
    public function run(): void
    {
        $prompts = [
            ['question' => 'Hoje comprometo-me a ser gentil comigo mesmo/a quando cometer um erro.', 'category' => 'autocompaixão'],
            ['question' => 'Hoje vou permitir-me sentir sem julgamento.', 'category' => 'aceitação'],
            ['question' => 'Hoje vou reconhecer uma coisa boa que fiz por mim.', 'category' => 'reconhecimento'],
            ['question' => 'Hoje vou dizer "não" a algo que me esgota.', 'category' => 'limites'],
            ['question' => 'Hoje vou descansar sem culpa.', 'category' => 'descanso'],
            ['question' => 'Hoje vou agradecer ao meu corpo por me ter trazido até aqui.', 'category' => 'gratidão'],
            ['question' => 'Hoje vou pedir ajuda se precisar, sem vergonha.', 'category' => 'vulnerabilidade'],
            ['question' => 'Hoje vou celebrar um pequeno progresso.', 'category' => 'celebração'],
            ['question' => 'Hoje escolho não me comparar com ninguém.', 'category' => 'aceitação'],
            ['question' => 'Hoje vou tratar-me como trataria o meu melhor amigo.', 'category' => 'autocompaixão'],
            ['question' => 'Hoje vou respirar fundo antes de reagir.', 'category' => 'regulação'],
            ['question' => 'Hoje vou fazer uma pausa quando sentir que preciso.', 'category' => 'descanso'],
            ['question' => 'Hoje vou escrever algo que preciso de libertar.', 'category' => 'expressão'],
            ['question' => 'Hoje aceito que não preciso de ser perfeito/a.', 'category' => 'aceitação'],
            ['question' => 'Hoje vou notar três coisas bonitas à minha volta.', 'category' => 'gratidão'],
            ['question' => 'Hoje vou honrar o meu ritmo, mesmo que seja diferente dos outros.', 'category' => 'autocompaixão'],
            ['question' => 'Hoje escolho a esperança, mesmo que seja difícil.', 'category' => 'esperança'],
            ['question' => 'Hoje vou lembrar-me de que mereço ocupar espaço.', 'category' => 'valor próprio'],
            ['question' => 'Hoje vou ser paciente com o meu processo de cura.', 'category' => 'paciência'],
            ['question' => 'Hoje vou permitir-me chorar se precisar.', 'category' => 'vulnerabilidade'],
            ['question' => 'Hoje comprometo-me a não ignorar os meus sinais de alerta.', 'category' => 'autocuidado'],
            ['question' => 'Hoje vou fazer algo que o meu eu do futuro vai agradecer.', 'category' => 'intencionalidade'],
            ['question' => 'Hoje vou lembrar-me de que já sobrevivi a 100% dos meus piores dias.', 'category' => 'resiliência'],
            ['question' => 'Hoje vou abraçar a incerteza como parte da vida.', 'category' => 'aceitação'],
            ['question' => 'Hoje vou dedicar 5 minutos só a mim.', 'category' => 'autocuidado'],
            ['question' => 'Hoje escolho acreditar que dias melhores virão.', 'category' => 'esperança'],
            ['question' => 'Hoje vou reconhecer a minha coragem de continuar.', 'category' => 'resiliência'],
            ['question' => 'Hoje vou soltar algo que não posso controlar.', 'category' => 'aceitação'],
            ['question' => 'Hoje vou ouvir o meu corpo e responder com carinho.', 'category' => 'autocuidado'],
            ['question' => 'Hoje lembro-me: pedir ajuda é um ato de força.', 'category' => 'vulnerabilidade'],
        ];

        foreach ($prompts as $prompt) {
            // Usar 'question' como chave de unicidade — campo renomeado de 'body' na migração
            // alter_pact_tables_for_new_schema. O seeder anterior falhava em migrate:fresh.
            PactPrompt::firstOrCreate(['question' => $prompt['question']], $prompt);
        }
    }
}
