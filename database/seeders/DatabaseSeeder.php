<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Order;
use App\Models\Passenger;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a specific user for testing relations
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create a passenger, driver, and order associated with the test user
        Passenger::factory()->create(['user_id' => $user->id]);
        Driver::factory()->create(['user_id' => $user->id]);
        Order::factory(2)->create(['user_id' => $user->id])->each(function ($order) {
            $order->payments()->createMany(
                \App\Models\Payment::factory(1)->make(['order_id' => $order->id])->toArray()
            );
            $order->orderItems()->createMany(
                \App\Models\OrderItem::factory(3)->make(['order_id' => $order->id])->toArray()
            );
        });

        $this->call([
            ProvinceSeeder::class,
            BusAndDriverSeeder::class,
            BusSeatSeeder::class,
            PassengerSeeder::class,
            TripSeeder::class,
            TripPriceSeeder::class,
            TripReservationSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
