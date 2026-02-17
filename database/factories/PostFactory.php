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
            // CORREÇÃO: Usamos 'content' porque é o que está na migração
            'content' => fake()->paragraphs(3, true), 
            // CORREÇÃO: Adicionada a 'tag' obrigatória
            'tag' => fake()->randomElement(['Geral', 'Ansiedade', 'Depressão', 'Dúvidas', 'Conquistas']),
            'is_sensitive' => fake()->boolean(10), // 10% de hipótese
            'support_count' => fake()->numberBetween(0, 50),
        ];
    }
}