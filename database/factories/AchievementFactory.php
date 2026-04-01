<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AchievementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug'          => $this->faker->unique()->slug(2),
            'name'          => $this->faker->words(3, true),
            'description'   => $this->faker->sentence(),
            'icon'          => 'ri-medal-line',
            'color'         => 'amber',
            'flames_reward' => 10,
            'is_hidden'     => false,
        ];
    }
}
