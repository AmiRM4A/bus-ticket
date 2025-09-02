<?php

namespace Modules\Buses\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Drivers\Models\Driver;
use Modules\Buses\Models\Bus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Buses\Models\Bus>
 */
class BusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bus::class;

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
