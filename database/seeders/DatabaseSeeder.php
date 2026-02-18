<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Room;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Message;
use Illuminate\Database\Seeder;
use App\Models\Achievement;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. UTILIZADORES
        // Admin
        User::factory()->create([
            'name' => 'Alexandre Admin',
            'email' => 'alex@lumina.pt',
            'role' => 'admin',
            'energy_level' => 5,
            'password' => bcrypt('password'),
        ]);

        // Comunidade
        $users = User::factory(10)->create();

        // 2. SALAS DE CHAT (FOGUEIRA)
        // Definimos manualmente para ficarem bonitas, com todos os campos necessários
        $rooms = Room::factory()->createMany([
            [
                'name' => '🔥 Fogueira Geral',
                'slug' => 'fogueira-geral',
                'description' => 'O ponto de encontro principal.',
                'color' => '#f97316', // Laranja
                'icon' => 'heroicon-o-fire',
                'is_private' => false,
            ],
            [
                'name' => '💭 Desabafos',
                'slug' => 'desabafos',
                'description' => 'Espaço seguro para partilhar pesos.',
                'color' => '#64748b', // Cinzento
                'icon' => 'heroicon-o-heart',
                'is_private' => false,
            ],
            [
                'name' => '🎵 Música & Vibe',
                'slug' => 'musica-e-vibe',
                'description' => 'O que estás a ouvir?',
                'color' => '#8b5cf6', // Roxo
                'icon' => 'heroicon-o-musical-note',
                'is_private' => false,
            ],
        ]);

        // 3. MENSAGENS
        // Encher cada sala com mensagens
        foreach ($rooms as $room) {
            Message::factory(8)->create([
                'room_id' => $room->id,
                'user_id' => $users->random()->id,
            ]);
        }

        // 4. POSTS DO MURAL
        foreach ($users as $user) {
            $post = Post::factory()->create([
                'user_id' => $user->id,
            ]);
            
            // Comentários
            Comment::factory(rand(0, 3))->create([
                'post_id' => $post->id,
                'user_id' => $users->random()->id,
            ]);
        }

        Achievement::create([
        'slug' => 'voice-found',
        'name' => 'Encontraste a tua Voz',
        'description' => 'Partilhaste a tua primeira história na Fogueira.',
        'icon' => 'ri-mic-line',
        'color' => 'indigo',
        'flames_reward' => 20
    ]);

    Achievement::create([
        'slug' => 'first-journal',
        'name' => 'Querido Diário',
        'description' => 'Fizeste o teu primeiro registo de humor.',
        'icon' => 'ri-book-heart-line',
        'color' => 'rose',
        'flames_reward' => 15
    ]);

    Achievement::create([
        'slug' => 'consistency-3',
        'name' => 'Faísca Constante',
        'description' => 'Visitaste a Lumina 3 dias seguidos.',
        'icon' => 'ri-fire-line',
        'color' => 'orange',
        'flames_reward' => 50
    ]);
    }
}