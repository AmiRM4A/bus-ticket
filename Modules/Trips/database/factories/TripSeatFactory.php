<?php

namespace Modules\Trips\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Buses\Models\BusSeat;
use Modules\Trips\Enums\TripSeatStatusEnum;
use Modules\Trips\Models\Trip;
use Modules\Trips\Models\TripSeat;

class TripSeatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TripSeat::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'bus_seat_id' => BusSeat::factory(),
            'status' => $this->faker->randomElement(TripSeatStatusEnum::values()),
            'reserved_gender' => null,
        ];
    }
}
