<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{

    public function definition(): array
    {
        return [
            'customer' => $this->faker->name,
            'status' => OrderStatus::ACTIVE,
        ];
    }
}
