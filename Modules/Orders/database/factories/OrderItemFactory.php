<?php

namespace Modules\Orders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderItem;
use Modules\Passengers\Models\Passenger;
use Modules\Trips\Models\TripSeat;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'trip_seat_id' => TripSeat::factory(),
            'passenger_id' => Passenger::factory(),
            'price' => $this->faker->randomFloat(2, 10, 200),
        ];
    }
}
