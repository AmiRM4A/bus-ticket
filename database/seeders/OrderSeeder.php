<?php

namespace Database\Seeders;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\TripSeatStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\TripReservation;
use App\Models\TripSeat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $tripReservations = TripReservation::all();
            $orderItemsToInsert = [];
            $paymentsToInsert = [];
            $tripSeatIdsToUpdate = [];
            $now = now();

            foreach ($tripReservations as $reservation) {
                $trip = $reservation->trip;
                $passenger = $reservation->passenger;

                if ($trip && $passenger) {
                    // Create an Order for the reservation
                    $order = Order::factory()->create([
                        'user_id' => $passenger->user_id, // Assuming passenger has a user_id
                        'status' => OrderStatusEnum::COMPLETED->value, // Assume completed for seeded data
                        'total_amount' => $trip->price_per_seat, // Initial total
                    ]);

                    // Collect OrderItem data
                    $orderItemsToInsert[] = [
                        'order_id' => $order->id,
                        'trip_reservation_id' => $reservation->id,
                        'price' => $trip->price_per_seat,
                        'quantity' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // Collect Payment data
                    $paymentsToInsert[] = [
                        'order_id' => $order->id,
                        'amount' => $order->total_amount,
                        'status' => PaymentStatusEnum::COMPLETED->value, // Assume completed for seeded data
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // Collect TripSeat IDs to update
                    $tripSeatIdsToUpdate[] = $reservation->trip_seat_id;
                }
            }

            if (! empty($orderItemsToInsert)) {
                OrderItem::insert($orderItemsToInsert);
            }

            if (! empty($paymentsToInsert)) {
                Payment::insert($paymentsToInsert);
            }

            if (! empty($tripSeatIdsToUpdate)) {
                TripSeat::whereIn('id', $tripSeatIdsToUpdate)->update(['status' => TripSeatStatusEnum::PAID->value, 'updated_at' => $now]);
            }

            // Create some additional generic orders not linked to reservations for variety
            Order::factory(5)->create()->each(function ($order) use ($now) {
                Payment::factory(1)->create(['order_id' => $order->id, 'created_at' => $now, 'updated_at' => $now]);
                OrderItem::factory(2)->create(['order_id' => $order->id, 'created_at' => $now, 'updated_at' => $now]);
            });
        });
    }
}
