<?php

namespace Modules\Trips\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Buses\Models\Bus;
use Modules\Locations\Models\Province;
use Modules\Trips\Models\Trip;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Trips\Models\Trip>
 */
class TripFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Trip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bus_id' => Bus::factory(),
            'from_province_id' => Province::factory(),
            'to_province_id' => Province::factory(),
            'total_seats' => $totalSeats = $this->faker->numberBetween(20, 50),
            'price_per_seat' => $this->faker->randomFloat(2, 10, 100),
            'reserved_seats_count' => $this->faker->numberBetween(0, $totalSeats),
            'trip_date' => $this->faker->date(),
            'departure_time' => $this->faker->time(),
            'arrived_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
