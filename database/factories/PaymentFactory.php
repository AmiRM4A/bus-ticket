<?php

namespace Database\Factories;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'transaction_id' => $this->faker->uuid(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement(PaymentStatusEnum::values()),
            'paid_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
