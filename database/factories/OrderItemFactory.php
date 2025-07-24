<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{

    public function definition(): array
    {
        return [
            'count' => $this->faker->numberBetween(1, 10),
        ];
    }
}
