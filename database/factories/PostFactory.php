<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(6),
            'content' => fake()->paragraphs(3, true),
            // CORREÇÃO: Usar as tags (chaves) que o sistema espera
            'tag' => fake()->randomElement(['hope', 'vent', 'anxiety']), 
            'is_sensitive' => fake()->boolean(10),
            'support_count' => fake()->numberBetween(0, 50),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}