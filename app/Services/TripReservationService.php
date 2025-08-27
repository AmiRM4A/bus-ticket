<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\TripSeatStatusEnum;
use App\Events\TripReserved;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\User;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class TripReservationService
{
    public static function createReservation(User $user, Trip $trip, array $passengersData): Order
    {
        $seatIds = array_column($passengersData, 'trip_seat_id');

        try {
            DB::beginTransaction();

            // Validate and reserve seats
            $reservedSeats = TripSeatService::reserveSeats($trip, $seatIds);

            // Resolve passengers (create new ones if needed) - keyed by seat_id
            $passengers = PassengerService::resolvePassengersWithSeats($passengersData);

            // Create order with items and payment
            $order = self::createOrder($user, $trip, $reservedSeats, $passengers);

            DB::commit();

            TripReserved::dispatch($order, $trip, $passengers);

            return $order;

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function cancelReservation(Order $order): void
    {
        if (! self::canCancelOrder($order)) {
            throw new InvalidOrderStateException('Order cannot be cancelled in current state');
        }

        try {
            DB::beginTransaction();

            // Release seats
            self::releaseOrderSeats($order);

            // Update order status
            $order->update(['status' => OrderStatusEnum::Cancelled]);

            // Update payment status
            self::updatePaymentStatus($order, PaymentStatusEnum::CANCELLED);

            DB::commit();

            // Fire events
            //            event(new \App\Events\ReservationCancelled($order));

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private static function createOrder(User $user, Trip $trip, Collection $seats, Collection $passengers): Order
    {
        $order = $user->orders()->create([
            'status' => OrderStatusEnum::Pending,
        ]);

        // Create order items - passengers collection is keyed by seat_id
        $orderItemsData = [];
        $totalAmount = 0;
        $now = now();

        foreach ($seats as $seat) {
            $passenger = $passengers[$seat->id]; // Get passenger by seat_id
            //            $price = $seat->price ?? $trip->price_per_seat; // Allow per-seat pricing (Not implemented, just to know)
            $price = $trip->price_per_seat;

            $orderItemsData[] = [
                'order_id' => $order->id,
                'trip_seat_id' => $seat->id,
                'passenger_id' => $passenger->id,
                'price' => $price,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $totalAmount += $price;
        }

        OrderItem::insert($orderItemsData);

        // Create payment
        Payment::create([
            'order_id' => $order->id,
            'amount' => $totalAmount,
            'transaction_id' => Str::uuid(),
            'status' => PaymentStatusEnum::PENDING,
        ]);

        return $order->load(['orderItems', 'payments']);
    }

    private static function releaseOrderSeats(Order $order): void
    {
        foreach ($order->orderItems as $orderItem) {
            $tripSeat = $orderItem->tripSeat()->lockForUpdate()->first();
            if ($tripSeat && $tripSeat->status === TripSeatStatusEnum::RESERVED) {
                $tripSeat->update(['status' => TripSeatStatusEnum::AVAILABLE]);
            }
        }
    }

    private static function updatePaymentStatus(Order $order, PaymentStatusEnum $status): void
    {
        foreach ($order->payments as $payment) {
            $payment->update(['status' => $status]);
        }
    }

    private static function canCancelOrder(Order $order): bool
    {
        return in_array($order->status, [
            OrderStatusEnum::Pending,
            OrderStatusEnum::Completed,
        ], true);
    }
}
