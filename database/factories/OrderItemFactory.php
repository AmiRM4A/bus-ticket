<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Passenger;
use App\Models\TripSeat;
use Illuminate\Database\Eloquent\Factories\Factory;

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
