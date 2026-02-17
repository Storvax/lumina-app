<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Confirmado: 'content' igual à migração
            'content' => fake()->sentence(rand(2, 12)), 
            'room_id' => Room::factory(),
            'user_id' => User::factory(),
            'is_anonymous' => fake()->boolean(10),
        ];
    }
}