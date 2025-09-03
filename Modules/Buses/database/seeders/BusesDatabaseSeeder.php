<?php

namespace Modules\Buses\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Buses\Models\Bus;
use Modules\Buses\Models\BusSeat;
use Modules\Drivers\Models\Driver;

class BusesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create drivers with associated users
            $drivers = Driver::factory(5)->create();

            // Create buses with drivers
            Bus::factory(10)->create(['driver_id' => function () use ($drivers) {
                return $drivers->random()->id;
            }]);

            // Create seats for buses (limited to 4 rows only)
            $buses = Bus::all();
            $busSeatsToInsert = [];
            $now = now();
            $columns = ['A', 'B', 'C', 'D'];

            foreach ($buses as $bus) {
                $seatNumber = 1;

                // Generate seats for exactly 4 rows
                for ($row = 1; $row <= 4; $row++) {
                    for ($columnIndex = 0; $columnIndex < 4; $columnIndex++) {
                        $busSeatsToInsert[] = [
                            'bus_id' => $bus->id,
                            'name' => "Seat {$seatNumber}",
                            'row' => $row,
                            'column' => $columns[$columnIndex],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $seatNumber++;
                    }
                }
            }

            BusSeat::insert($busSeatsToInsert);
        });
    }
}
