<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RoomFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word()) . ' ' . fake()->suffix();
        
        return [
            'name' => $name,
            'slug' => Str::slug($name), // Gera slug automático (ex: Nome Sala -> nome-sala)
            'description' => fake()->sentence(8),
            'color' => fake()->hexColor(),
            'icon' => 'heroicon-o-hashtag',
            'is_private' => false,
        ];
    }
}