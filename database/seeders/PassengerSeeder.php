<?php

namespace Database\Seeders;

use App\Models\Passenger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PassengerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            Passenger::factory(20)->create();
        });
    }
}
