<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Buses\Database\Seeders\BusesDatabaseSeeder;
use Modules\Locations\Database\Seeders\LocationsDatabaseSeeder;
use Modules\Trips\Database\Seeders\TripsDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LocationsDatabaseSeeder::class,
            BusesDatabaseSeeder::class,
            TripsDatabaseSeeder::class,
        ]);
    }
}
