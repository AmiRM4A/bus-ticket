<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Driver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusAndDriverSeeder extends Seeder
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
        });
    }
}
