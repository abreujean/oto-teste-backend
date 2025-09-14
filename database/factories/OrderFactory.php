<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pendente', 'em processamento', 'concluído', 'cancelado']),
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}