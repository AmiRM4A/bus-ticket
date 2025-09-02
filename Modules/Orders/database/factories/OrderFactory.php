<?php

namespace Modules\Orders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Orders\Enums\OrderStatusEnum;
use Modules\Orders\Models\Order;
use Modules\Users\Models\User;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(OrderStatusEnum::values()),
            'data' => json_encode(['notes' => $this->faker->sentence()], JSON_THROW_ON_ERROR),
        ];
    }
}
