<?php

namespace Modules\Locations\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Locations\Models\Province;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Locations\Models\Province>
 */
class ProvinceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Province::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
        ];
    }
}
