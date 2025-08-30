<?php

namespace Database\Factories;

use App\Enums\TripSeatStatusEnum;
use App\Models\BusSeat;
use App\Models\Trip;
use App\Models\TripSeat;
use Illuminate\Database\Eloquent\Factories\Factory;

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
