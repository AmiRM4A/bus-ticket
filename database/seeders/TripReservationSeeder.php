<?php

namespace Database\Seeders;

use App\Enums\TripSeatStatusEnum;
use App\Models\Passenger;
use App\Models\Trip;
use App\Models\TripReservation;
use App\Models\TripSeat;
use Illuminate\Database\Seeder;

class TripReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trips = Trip::all();
        $passengers = Passenger::all();

        foreach ($trips as $trip) {
            $availableTripSeats = TripSeat::where('trip_id', $trip->id)
                ->where('status', TripSeatStatusEnum::AVAILABLE->value)
                ->get();

            // Randomly reserve some available seats
            $numberOfReservations = rand(0, min($availableTripSeats->count(), 5)); // Reserve up to 5 seats per trip

            for ($i = 0; $i < $numberOfReservations; $i++) {
                $tripSeat = $availableTripSeats->random();
                if ($tripSeat) {
                    TripReservation::factory()->create([
                        'trip_id' => $trip->id,
                        'passenger_id' => $passengers->random()->id,
                        'trip_seat_id' => $tripSeat->id,
                    ]);
                    $tripSeat->update(['status' => TripSeatStatusEnum::RESERVED->value]);
                    $availableTripSeats = $availableTripSeats->except($tripSeat->id); // Remove reserved seat from available list
                }
            }
        }
    }
}
