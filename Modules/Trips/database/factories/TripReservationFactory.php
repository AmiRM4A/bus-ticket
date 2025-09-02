<?php

namespace Modules\Trips\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Passengers\Models\Passenger;
use Modules\Trips\Models\Trip;
use Modules\Trips\Models\TripSeat;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Trips\Models\TripReservation>
 */
class TripReservationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TripReservation::class;

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
