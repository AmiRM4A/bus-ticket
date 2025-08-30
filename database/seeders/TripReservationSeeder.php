<?php

namespace Database\Seeders;

use App\Enums\TripSeatStatusEnum;
use App\Models\Passenger;
use App\Models\Trip;
use App\Models\TripReservation;
use App\Models\TripSeat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TripReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $trips = Trip::all();
            $passengers = Passenger::all();
            $tripReservationsToInsert = [];
            $tripSeatIdsToUpdate = [];
            $tripReservedSeatsCounts = [];
            $now = now();

            foreach ($trips as $trip) {
                $allTripSeats = TripSeat::where('trip_id', $trip->id)->get();
                $totalSeats = $allTripSeats->count();
                $reservedSeatsCount = 0;

                // Decide booking scenario for the trip
                $scenario = rand(1, 10); // 1-3: Empty, 4-7: Partially booked, 8-10: Fully booked

                if ($scenario >= 4 && $totalSeats > 0) { // Partially or Fully booked
                    if ($scenario >= 8) { // Fully booked
                        $numberOfReservations = $totalSeats;
                    } else { // Partially booked
                        $numberOfReservations = rand(1, $totalSeats - 1);
                    }

                    dd($trip, $numberOfReservations);

                    $seatsToReserve = $allTripSeats->shuffle()->take($numberOfReservations);

                    foreach ($seatsToReserve as $tripSeat) {
                        $tripReservationsToInsert[] = [
                            'trip_id' => $trip->id,
                            'passenger_id' => $passengers->random()->id,
                            'trip_seat_id' => $tripSeat->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $tripSeatIdsToUpdate[] = $tripSeat->id;
                        $reservedSeatsCount++;
                    }
                }
                $tripReservedSeatsCounts[$trip->id] = $reservedSeatsCount;
            }

            if (! empty($tripReservationsToInsert)) {
                TripReservation::insert($tripReservationsToInsert);
            }

            if (! empty($tripSeatIdsToUpdate)) {
                TripSeat::whereIn('id', $tripSeatIdsToUpdate)->update(['status' => TripSeatStatusEnum::RESERVED->value, 'updated_at' => $now]);
            }

            foreach ($tripReservedSeatsCounts as $tripId => $count) {
                Trip::where('id', $tripId)->update(['reserved_seats_count' => $count, 'updated_at' => $now]);
            }
        });
    }
}
