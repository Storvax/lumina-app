<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'           => $this->faker->sentence(4),
            'description'     => $this->faker->sentence(),
            'type'            => 'daily',
            'action_type'     => 'daily_log',
            'goal_count'      => 1,
            'target_count'    => 1,
            'flames_reward'   => 10,
            'available_from'  => now()->subDay()->toDateString(),
            'available_until' => now()->addDay()->toDateString(),
        ];
    }
}
