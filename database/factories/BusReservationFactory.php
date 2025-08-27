<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Bus;
use App\Models\BusSeat;

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