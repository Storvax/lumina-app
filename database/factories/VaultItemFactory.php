<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VaultItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'content' => $this->faker->sentence(),
        ];
    }
}
