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

            // Create seats for buses
            $buses = Bus::all();
            $busSeatsToInsert = [];
            $now = now();

            foreach ($buses as $bus) {
                for ($i = 1; $i <= $bus->seats_count; $i++) {
                    $busSeatsToInsert[] = [
                        'bus_id' => $bus->id,
                        'name' => "Seat {$i}",
                        'row' => ceil($i / 4),
                        'column' => match (($i - 1) % 4) {
                            0 => 'A',
                            1 => 'B',
                            2 => 'C',
                            3 => 'D',
                        },
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            BusSeat::insert($busSeatsToInsert);
        });
    }
}
