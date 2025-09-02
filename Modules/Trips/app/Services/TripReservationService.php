<?php

namespace Modules\Trips\Services;

use DB;
use Modules\Orders\Exceptions\InvalidOrderException;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderItemService;
use Modules\Orders\Services\OrderService;
use Modules\Passengers\Services\PassengerService;
use Modules\Payments\Services\PaymentService;
use Modules\Trips\Models\Trip;
use Modules\Users\Models\User;
use Throwable;

readonly class TripReservationService
{
    public function __construct(
        private PassengerService $passengerService,
        private TripSeatService $tripSeatService,
        private OrderService $orderService,
        private PaymentService $paymentService,
        private OrderItemService $orderItemService,
    ) {
        //
    }

    /**
     * @throws Throwable
     */
    public function createReservation(User $user, Trip $trip, array $passengersData): Order
    {
        return DB::transaction(function () use ($user, $trip, $passengersData) {
            // Resolve passengers (create new ones if needed) - keyed by seat_id
            $passengers = $this->passengerService->createOrRetrievePassengers($passengersData);

            // Validate and reserve seats (mark them as reserved/hold)
            $reservedSeats = $this->tripSeatService->reserveSeats($trip, $passengers);

            // Create order with items and payment
            $order = $this->orderService->createOrderWithItems($user, $trip, $reservedSeats, $passengers);

            // An event could be dispatched here
            // (For sending SMS to passengers or...)

            return $order;
        });
    }

    /**
     * @throws Throwable
     */
    public function cancelReservation(Order $order, array $seats_to_cancel): void
    {
        DB::transaction(function () use ($order, $seats_to_cancel) {
            // Check if all the seats are belong to this order
            $itemsCount = $this->orderItemService->getOrderItemsCountBySeatIds($order->id, $seats_to_cancel);
            if (count($seats_to_cancel) !== $itemsCount) {
                throw new InvalidOrderException(__('api.seats_not_belong_to_order'));
            }

            // Mark order items as deleted
            $this->orderItemService->deleteItems($seats_to_cancel);

            // Release seats
            $this->tripSeatService->releaseSeats($seats_to_cancel);

            // Cancel order's payment(s)
            $this->paymentService->cancelPaymentsForOrder($order->id);

            if (! $this->orderItemService->orderHasItems($order->id)) {
                // Cancel order if there isn't any item for this order anymore
                $this->orderService->cancelOrder($order->id);
            }

            // Also we can add the canceled seats count to the trip's available seats
            // We can throw an event for that or...
        });
    }
}
