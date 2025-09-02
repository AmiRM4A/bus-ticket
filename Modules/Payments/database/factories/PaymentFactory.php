<?php

namespace Modules\Payments\Database\Factories;

use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Orders\Models\Order;
use Modules\Payments\Models\Payment;

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
