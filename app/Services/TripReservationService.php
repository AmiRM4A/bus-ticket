<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Trip;
use App\Models\User;
use DB;
use Throwable;

readonly class TripReservationService
{
    public function __construct(
        private PassengerService $passengerService,
        private TripSeatService $tripSeatService,
        private OrderService $orderService,
        private PaymentService $paymentService
    ) {
        //
    }

    public function createReservation(User $user, Trip $trip, array $passengersData): Order
    {
        try {
            DB::beginTransaction();

            // Resolve passengers (create new ones if needed) - keyed by seat_id
            $passengers = $this->passengerService->createOrRetrievePassengers($passengersData);

            // Validate and reserve seats (mark them as reserved/hold)
            $reservedSeats = $this->tripSeatService->reserveSeats($trip, $passengers);

            // Create order with items and payment
            $order = $this->orderService->createOrderWithItems($user, $trip, $reservedSeats, $passengers);

            DB::commit();

            // An event could be dispatched here
            // (For sending SMS to passengers or...)

            return $order;
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function cancelReservation(Order $order, array $seatsToCancel): void
    {
        try {
            DB::beginTransaction();

            $orderItems = $this->orderService->validateOrderSeats($order, $seatsToCancel);

            // Mark order items as deleted
            $itemIds = $orderItems->pluck('id')->toArray();
            $this->orderService->deleteItems($itemIds);

            // Release seats
            $this->tripSeatService->releaseSeats($seatsToCancel);

            if ($order->hasAnyPayment()) {
                // Cancel order's payment if there is any payment for this order
                $this->paymentService->cancelPaymentsForOrder($order->id);
            }

            if (! $order->hasAnyItem()) {
                // Cancel order if there isn't any item for this order anymore
                $this->orderService->cancelOrder($order);
            }

            // Also we can add the canceled seats count to the trip's available seats
            // We can throw an event for that or...

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
