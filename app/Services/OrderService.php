<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TripReservation;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderService
{
    public static function fulfillOrder(Order $order): void
    {
        // Right now, we just have one type of order, so we'll build our structure based on that
        // Note that this structure could get refactored in the future if any other type of order is going to be added.
        // (Resolving the fulfiller dynamically based on order's type)
        $items = $order->orderItems;
        if ($items->isEmpty()) {
            // There isn't any order item.
        }

        $now = now();
        $reservationsToCreate = [];
        /** @var OrderItem $item */
        foreach ($items as $item) {
            $reservationsToCreate[] = [
                'passenger_id' => $item->passenger_id,
                'trip_id' => $item->tripSeat->trip_id,
                'trip_seat_id' => $item->trip_seat_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        try {
            DB::beginTransaction();

            // Mark order as completed
            $order->markAsCompleted();

            // Reserve the seats (add the to trip_reservations table)
            TripReservation::insert($reservationsToCreate);

            // Mark those seats as sold
            $seatIds = $items->pluck('trip_seat_id')->toArray();
            TripSeatService::markTripSeatsAsSold($seatIds);

            // We can also add the reserved seats count to the trip's reserved seats

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
