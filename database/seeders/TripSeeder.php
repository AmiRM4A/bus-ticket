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
                $trip->reserved_seats_count = $faker->numberBetween(0, $bus->seats_count);
                $trip->save();
            });

            // Create TripSeats for each trip and its bus seats
            foreach ($trips as $trip) {
                $busSeats = BusSeat::where('bus_id', $trip->bus_id)->get();
                foreach ($busSeats as $busSeat) {
                    TripSeat::factory()->create([
                        'trip_id' => $trip->id,
                        'bus_seat_id' => $busSeat->id,
                        'status' => TripSeatStatusEnum::AVAILABLE->value,
                    ]);
                }
            }
        }
    }
}
