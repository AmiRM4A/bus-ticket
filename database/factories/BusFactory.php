<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Driver;
use App\Models\Province;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bus>
 */
class BusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver_id' => Driver::factory(),
            'model' => $this->faker->randomElement(['Mercedes', 'Volvo', 'Scania', 'MAN', 'Iveco']),
            'plate' => strtoupper($this->faker->bothify('??-###-??')),
            'seats_count' => $this->faker->numberBetween(20, 60),
        ];
    }
}