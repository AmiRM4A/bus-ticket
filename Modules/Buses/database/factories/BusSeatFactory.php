<?php

namespace Modules\Buses\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Buses\Models\Bus;
use Modules\Buses\Models\BusSeat;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Buses\Models\BusSeat>
 */
class BusSeatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BusSeat::class;

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
