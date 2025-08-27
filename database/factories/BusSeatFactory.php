<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Bus;

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
        ];
    }
}