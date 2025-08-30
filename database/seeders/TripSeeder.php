<?php

namespace Database\Seeders;

use App\Enums\TripSeatStatusEnum;
use App\Models\Bus;
use App\Models\BusSeat;
use App\Models\Province;
use App\Models\Trip;
use App\Models\TripSeat;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $provinces = Province::all();
        $buses = Bus::all();

        DB::transaction(function () use ($faker, $provinces, $buses) {
            if ($provinces->count() >= 2 && $buses->count() > 0) {
                $trips = Trip::factory(15)->make([
                    'bus_id' => function () use ($buses) {
                        return $buses->random()->id;
                    },
                    'from_province_id' => function () use ($provinces) {
                        return $provinces->random()->id;
                    },
                    'to_province_id' => function () use ($provinces) {
                        return $provinces->random()->id;
                    },
                ])->each(function ($trip) use ($faker) {
                    $bus = Bus::find($trip->bus_id);
                    $trip->total_seats = $bus->seats_count;
                    $trip->price_per_seat = $faker->randomFloat(2, 20, 100); // Set a realistic price
                    $trip->trip_date = $faker->dateTimeBetween('-1 month', '+3 months')->format('Y-m-d'); // Mix of past and future trips
                    $trip->departure_time = $faker->time('H:i'); // Realistic departure time
                    $trip->reserved_seats_count = 0; // Initialize to 0, will be updated by reservations
                    $trip->save();
                });

                // Create TripSeats for each trip and its bus seats
                $tripSeatsToInsert = [];
                $now = now();
                foreach ($trips as $trip) {
                    $busSeats = BusSeat::where('bus_id', $trip->bus_id)->get();
                    foreach ($busSeats as $busSeat) {
                        $tripSeatsToInsert[] = [
                            'trip_id' => $trip->id,
                            'bus_seat_id' => $busSeat->id,
                            'status' => TripSeatStatusEnum::AVAILABLE->value,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (! empty($tripSeatsToInsert)) {
                    TripSeat::insert($tripSeatsToInsert);
                }
            }
        });
    }
}
