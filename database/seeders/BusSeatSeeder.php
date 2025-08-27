<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\BusSeat;
use Illuminate\Database\Seeder;

class BusSeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create seats for each bus
        $buses = Bus::all();
        foreach ($buses as $bus) {
            for ($i = 1; $i <= $bus->seats_count; $i++) {
                BusSeat::factory()->create([
                    'bus_id' => $bus->id,
                    'name' => "Seat {$i}",
                ]);
            }
        }
    }
}
