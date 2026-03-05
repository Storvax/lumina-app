<?php

namespace Database\Seeders;

use App\Models\PactPrompt;
use Illuminate\Database\Seeder;

class PactPromptSeeder extends Seeder
{
    public function run(): void
    {
        $prompts = [
            ['body' => 'Hoje comprometo-me a ser gentil comigo mesmo/a quando cometer um erro.', 'category' => 'autocompaixão'],
            ['body' => 'Hoje vou permitir-me sentir sem julgamento.', 'category' => 'aceitação'],
            ['body' => 'Hoje vou reconhecer uma coisa boa que fiz por mim.', 'category' => 'reconhecimento'],
            ['body' => 'Hoje vou dizer "não" a algo que me esgota.', 'category' => 'limites'],
            ['body' => 'Hoje vou descansar sem culpa.', 'category' => 'descanso'],
            ['body' => 'Hoje vou agradecer ao meu corpo por me ter trazido até aqui.', 'category' => 'gratidão'],
            ['body' => 'Hoje vou pedir ajuda se precisar, sem vergonha.', 'category' => 'vulnerabilidade'],
            ['body' => 'Hoje vou celebrar um pequeno progresso.', 'category' => 'celebração'],
            ['body' => 'Hoje escolho não me comparar com ninguém.', 'category' => 'aceitação'],
            ['body' => 'Hoje vou tratar-me como trataria o meu melhor amigo.', 'category' => 'autocompaixão'],
            ['body' => 'Hoje vou respirar fundo antes de reagir.', 'category' => 'regulação'],
            ['body' => 'Hoje vou fazer uma pausa quando sentir que preciso.', 'category' => 'descanso'],
            ['body' => 'Hoje vou escrever algo que preciso de libertar.', 'category' => 'expressão'],
            ['body' => 'Hoje aceito que não preciso de ser perfeito/a.', 'category' => 'aceitação'],
            ['body' => 'Hoje vou notar três coisas bonitas à minha volta.', 'category' => 'gratidão'],
            ['body' => 'Hoje vou honrar o meu ritmo, mesmo que seja diferente dos outros.', 'category' => 'autocompaixão'],
            ['body' => 'Hoje escolho a esperança, mesmo que seja difícil.', 'category' => 'esperança'],
            ['body' => 'Hoje vou lembrar-me de que mereço ocupar espaço.', 'category' => 'valor próprio'],
            ['body' => 'Hoje vou ser paciente com o meu processo de cura.', 'category' => 'paciência'],
            ['body' => 'Hoje vou permitir-me chorar se precisar.', 'category' => 'vulnerabilidade'],
            ['body' => 'Hoje comprometo-me a não ignorar os meus sinais de alerta.', 'category' => 'autocuidado'],
            ['body' => 'Hoje vou fazer algo que o meu eu do futuro vai agradecer.', 'category' => 'intencionalidade'],
            ['body' => 'Hoje vou lembrar-me de que já sobrevivi a 100% dos meus piores dias.', 'category' => 'resiliência'],
            ['body' => 'Hoje vou abraçar a incerteza como parte da vida.', 'category' => 'aceitação'],
            ['body' => 'Hoje vou dedicar 5 minutos só a mim.', 'category' => 'autocuidado'],
            ['body' => 'Hoje escolho acreditar que dias melhores virão.', 'category' => 'esperança'],
            ['body' => 'Hoje vou reconhecer a minha coragem de continuar.', 'category' => 'resiliência'],
            ['body' => 'Hoje vou soltar algo que não posso controlar.', 'category' => 'aceitação'],
            ['body' => 'Hoje vou ouvir o meu corpo e responder com carinho.', 'category' => 'autocuidado'],
            ['body' => 'Hoje lembro-me: pedir ajuda é um ato de força.', 'category' => 'vulnerabilidade'],
        ];

        foreach ($prompts as $prompt) {
            PactPrompt::firstOrCreate(['body' => $prompt['body']], $prompt);
        }
    }
}
