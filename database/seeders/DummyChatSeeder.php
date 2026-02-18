<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyChatSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Garantir que temos Salas e Users
        $rooms = Room::all();
        if ($rooms->isEmpty()) {
            $this->command->info('A criar salas de teste...');
            $rooms = Room::factory(3)->create();
        }

        $users = User::all();
        if ($users->count() < 3) {
            $this->command->info('A criar utilizadores de teste...');
            $users = User::factory(5)->create();
        }

        $this->command->info('A gerar conversas nas salas...');

        foreach ($rooms as $room) {
            // --- CENÁRIO A: Mensagens Normais (Para testar Reportar) ---
            foreach($users->random(3) as $user) {
                Message::create([
                    'room_id' => $room->id,
                    'user_id' => $user->id,
                    'content' => "Olá a todos! Alguém por aqui para conversar na sala {$room->name}?",
                    'created_at' => now()->subMinutes(rand(10, 60)),
                    'is_anonymous' => false,
                    'is_sensitive' => false,
                ]);
            }

            // --- CENÁRIO B: Mensagem Anónima (Para testar o ícone de Espião) ---
            Message::create([
                'room_id' => $room->id,
                'user_id' => $users->random()->id,
                'content' => "Tenho vergonha de admitir isto com o meu nome real, mas sinto-me muito sozinho hoje.",
                'created_at' => now()->subMinutes(5),
                'is_anonymous' => true, // <--- TESTE ANONIMATO
                'is_sensitive' => false,
            ]);

            // --- CENÁRIO C: Mensagem Sensível (Para testar o Blur/Overlay) ---
            $sensitiveMsg = Message::create([
                'room_id' => $room->id,
                'user_id' => $users->random()->id,
                'content' => "Tive uma recaída hoje. O ambiente em casa estava insuportável e acabei por me magoar. Preciso de ajuda para parar.",
                'created_at' => now()->subMinutes(2),
                'is_anonymous' => false,
                'is_sensitive' => true, // <--- TESTE SENSÍVEL
            ]);

            // Adicionar Reações a esta mensagem sensível (Para testar contadores)
            MessageReaction::create(['message_id' => $sensitiveMsg->id, 'user_id' => $users[0]->id, 'type' => 'hug']);
            MessageReaction::create(['message_id' => $sensitiveMsg->id, 'user_id' => $users[1]->id, 'type' => 'candle']);

            // --- CENÁRIO D: Mensagem do "Próprio" (Para testar o Delete) ---
            // Nota: Como não sei qual o ID do teu user logado, vou criar uma para o User ID 1 (assumindo que és tu)
            if ($myUser = User::find(1)) {
                Message::create([
                    'room_id' => $room->id,
                    'user_id' => $myUser->id,
                    'content' => "Esta é uma mensagem minha de teste. Devo conseguir ver o botão de Apagar aqui.",
                    'created_at' => now(),
                    'is_anonymous' => false,
                    'is_sensitive' => false,
                ]);
            }
        }
        
        $this->command->info('Dummy Data criado com sucesso! Vai verificar o Chat.');
    }
}