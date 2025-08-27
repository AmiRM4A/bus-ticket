<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\BusSeat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusReservation>
 */
class BusReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'bus_id' => Bus::factory(),
            'seat_id' => BusSeat::factory(),
        ];
    }
}
