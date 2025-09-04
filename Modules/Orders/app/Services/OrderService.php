<?php

namespace Modules\Orders\Services;

use Illuminate\Support\Collection;
use Modules\Orders\Enums\OrderStatusEnum;
use Modules\Orders\Exceptions\InvalidOrderException;
use Modules\Orders\Models\Order;
use Modules\Trips\Models\Trip;
use Modules\Trips\Models\TripReservation;
use Modules\Trips\Services\TripSeatService;
use Modules\Users\Models\User;
use Throwable;

readonly class OrderService
{
    public function __construct(
        private TripSeatService $tripSeatService,
        private OrderItemService $orderItemService
    ) {
        //
    }

    public function createOrderWithItems(User $user, Trip $trip, Collection $seats, Collection $passengers): Order
    {
        // Create order for user
        $order = $user->orders()->create([
            'status' => OrderStatusEnum::Pending,
            'data' => ['trip_id' => $trip->id],
        ]);

        // Create order items through OrderItemService
        $this->orderItemService->createOrderItems($order->id, $trip, $seats, $passengers);

        return $order;
    }

    /**
     * @throws InvalidOrderException|Throwable
     */
    public function fulfillOrder(Order $order): void
    {
        // Right now, we just have one type of order, so we'll build our structure based on that
        // Note that this structure could get refactored in the future if any other type of order is going to be added.
        // (Resolving the fulfiller dynamically based on order's type)
        $items = $order->orderItems;

        if ($items->isEmpty()) {
            throw new InvalidOrderException(__('api.order_no_items'));
        }

        // Prepare seat's selling data using OrderItemService
        $reservationsToCreate = $this->orderItemService->prepareReservationsData($items);

        // Mark order as completed
        $order->markAsCompleted();

        // Sell the seats (add them to trip_reservations table)
        TripReservation::insert($reservationsToCreate);

        // Mark seats as sold (for the trip)
        $seatIds = $this->orderItemService->getSeatIdsFromItems($items);
        $this->tripSeatService->markTripSeatsAsSold($seatIds);

        // We can also add the reserved seats count to the trip's reserved seats
    }

    public function cancelOrder(array|int $orderId): bool
    {
        return Order::whereIn('id', (array) $orderId)
            ->update(['status' => OrderStatusEnum::Cancelled]);
    }

    public function getOrderForUser(int $orderId, int $userId): Order
    {
        return Order::forUser($userId)
            ->findOrFail($orderId);
    }

    public function canCancelOrder(Order $order): bool
    {
        return in_array($order->status, [
            OrderStatusEnum::Pending,
            OrderStatusEnum::Completed,
        ], true);
    }
}
