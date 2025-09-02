<?php

namespace Modules\Drivers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Users\Models\User;
use Modules\Drivers\Models\Driver;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Drivers\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Driver::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'trips_completed' => $this->faker->numberBetween(0, 500),
        ];
    }
}
