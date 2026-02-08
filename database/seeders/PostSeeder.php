<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Garantir que temos utilizadores (cria 5 se não houver)
        if (User::count() < 5) {
            User::factory(5)->create();
        }
        
        $users = User::all();

        // 2. Limpar posts antigos para não duplicar
        // DB::table('posts')->truncate(); // Descomenta se quiseres limpar tudo antes

        $posts = [
            [
                'title' => 'Finalmente consegui ir ao supermercado!',
                'content' => 'Depois de semanas fechado em casa com medo de ataques de pânico, hoje ganhei coragem. Fui só comprar leite, mas senti-me um herói. Um passo de cada vez, malta!',
                'tag' => 'hope',
                'is_sensitive' => false,
                'support_count' => 45
            ],
            [
                'title' => 'Sinto-me invisível no trabalho',
                'content' => 'Falo nas reuniões e ninguém ouve. Sinto o coração a bater a mil sempre que tenho de apresentar algo. Alguém tem dicas para lidar com ansiedade social em ambiente corporativo?',
                'tag' => 'anxiety',
                'is_sensitive' => false,
                'support_count' => 12
            ],
            [
                'title' => 'Hoje está a ser um dia muito escuro...',
                'content' => 'Não consigo parar de chorar. A dor no peito é constante e sinto que nunca vai passar. Só queria que esta sensação desaparecesse. Desculpem o desabafo, precisava de tirar isto cá de dentro.',
                'tag' => 'vent',
                'is_sensitive' => true, // Teste de Blur
                'support_count' => 28
            ],
            [
                'title' => 'A terapia está a funcionar ❤️',
                'content' => 'Ao fim de 6 meses, começo a ver a luz. Aprendi a identificar os meus gatilhos e a respirar antes de espiralizar. Não desistam, a ajuda funciona mesmo.',
                'tag' => 'hope',
                'is_sensitive' => false,
                'support_count' => 89
            ],
            [
                'title' => 'Insónias outra vez',
                'content' => 'São 4 da manhã e a minha cabeça não cala. Penso em tudo o que fiz de errado em 2015. Mais alguém acordado a lutar contra os próprios pensamentos?',
                'tag' => 'anxiety',
                'is_sensitive' => false,
                'support_count' => 5
            ],
            [
                'title' => 'Gatilho: Crise forte',
                'content' => 'Tive uma crise muito feia hoje. Senti que ia perder o controlo total. Foi assustador e ainda estou a tremer. Sinto-me fraco por não conseguir lidar com isto sozinho.',
                'tag' => 'vent',
                'is_sensitive' => true, // Teste de Blur
                'support_count' => 15
            ],
            [
                'title' => 'Pequenas vitórias',
                'content' => 'Hoje fiz a cama e tomei banho. Parece pouco, mas para mim foi uma maratona. Amanhã tento lavar a louça.',
                'tag' => 'hope',
                'is_sensitive' => false,
                'support_count' => 62
            ],
            [
                'title' => 'Medo do futuro',
                'content' => 'Acabei a faculdade e sinto um vazio enorme. E agora? A pressão para arranjar emprego está a dar cabo de mim.',
                'tag' => 'anxiety',
                'is_sensitive' => false,
                'support_count' => 8
            ],
        ];

        // 3. Inserir na Base de Dados
        foreach ($posts as $data) {
            Post::create([
                'user_id' => $users->random()->id, // Atribui a um user aleatório
                'title' => $data['title'],
                'content' => $data['content'],
                'tag' => $data['tag'],
                'is_sensitive' => $data['is_sensitive'],
                'support_count' => $data['support_count'],
                'created_at' => now()->subMinutes(rand(1, 1440)) // Datas aleatórias nas últimas 24h
            ]);
        }
    }
}