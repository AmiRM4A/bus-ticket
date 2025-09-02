<?php

namespace Modules\Locations\Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Modules\Locations\Models\Province;

class LocationsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            'Alborz',
            'Ardabil',
            'Bushehr',
            'Chaharmahal and Bakhtiari',
            'East Azerbaijan',
            'Fars',
            'Gilan',
            'Golestan',
            'Hamadan',
            'Hormozgan',
            'Ilam',
            'Isfahan',
            'Kerman',
            'Kermanshah',
            'Khuzestan',
            'Kohgiluyeh and Boyer-Ahmad',
            'Kurdistan',
            'Lorestan',
            'Markazi',
            'Mazandaran',
            'North Khorasan',
            'Qazvin',
            'Qom',
            'Razavi Khorasan',
            'Semnan',
            'Sistan and Baluchestan',
            'South Khorasan',
            'Tehran',
            'West Azerbaijan',
            'Yazd',
            'Zanjan',
        ];

        DB::transaction(function () use ($provinces) {
            $provincesToInsert = [];
            $now = now();
            foreach ($provinces as $province) {
                $provincesToInsert[] = [
                    'name' => $province,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            Province::insert($provincesToInsert);
        });
    }
}
