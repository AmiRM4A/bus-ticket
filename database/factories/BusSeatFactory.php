<?php

namespace Database\Factories;

use App\Models\Bus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusSeat>
 */
class BusSeatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bus_id' => Bus::factory(),
            'name' => $this->faker->numerify('Seat ##'),
            'row' => $this->faker->numberBetween(1, 4),
            'column' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
        ];
    }
}
