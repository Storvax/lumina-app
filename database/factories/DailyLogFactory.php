<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DailyLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => \App\Models\User::factory(),
            'mood_level' => $this->faker->numberBetween(1, 5),
            'log_date'   => now()->toDateString(),
            'note'       => $this->faker->sentence(),
            'tags'       => [],
        ];
    }
}
