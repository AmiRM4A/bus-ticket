<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\BusSeat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusSeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
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
