<?php

namespace Database\Factories;

use App\Models\Passenger;
use App\Models\Trip;
use App\Models\TripSeat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripReservation>
 */
class TripReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'passenger_id' => Passenger::factory(),
            'trip_id' => Trip::factory(),
            'trip_seat_id' => TripSeat::factory(),
        ];
    }
}
