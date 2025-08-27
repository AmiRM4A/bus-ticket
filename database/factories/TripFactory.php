<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Bus;
use App\Models\Province;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bus_id' => $busId = Bus::factory(),
            'from_province_id' => Province::factory(),
            'to_province_id' => Province::factory(),
            'total_seats' => $totalSeats = $this->faker->numberBetween(20, 50),
            'price_per_seat' => $this->faker->randomFloat(2, 10, 100),
            'reserved_seats_count' => $this->faker->numberBetween(0, $totalSeats),
            'completed_at' => $this->faker->optional()->dateTime(),
        ];
    }
}