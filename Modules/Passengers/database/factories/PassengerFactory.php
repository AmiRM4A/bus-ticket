<?php

namespace Modules\Passengers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Passengers\Models\Passenger;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Passengers\Models\Passenger>
 */
class PassengerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Passenger::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'mobile' => $this->faker->unique()->numerify('09#########'),
            'national_code' => $this->faker->unique()->numerify('##########'),
            'birth_date' => $this->faker->date(),
            'gender' => $this->faker->boolean(),
        ];
    }
}
