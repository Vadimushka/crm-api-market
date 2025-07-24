<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockFactory extends Factory
{

    public function definition(): array
    {
        return [
            'stock' => $this->faker->numberBetween(1, 100),
        ];
    }
}
