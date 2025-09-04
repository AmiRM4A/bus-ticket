<?php

namespace Modules\Orders\Services;

use DB;
use Illuminate\Support\Collection;
use Modules\Orders\Enums\OrderStatusEnum;
use Modules\Orders\Exceptions\InvalidOrderException;
use Modules\Orders\Models\Order;
use Modules\Trips\Models\Trip;
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
        $items = $this->orderItemService->getItemsForOrder($order->id);

        if ($items->isEmpty()) {
            throw new InvalidOrderException(__('api.order_no_items'));
        }

        // Right now, we just have one type of order, so we'll build our structure based on that
        // Note that this structure could get refactored in the future if any other type of order is going to be added.
        // (Resolving the fulfiller dynamically based on order's type)

        // Map passenger id and seat id
        // => passenger_id => trip_seat_id
        $passengersAndSeats = $items->pluck('passenger_id', 'trip_seat_id')->toArray();
        $tripId = $order->data['trip_id'];

        $this->tripSeatService->sellSeatsToPassengers($tripId, $passengersAndSeats);

        // Mark order as completed
        $order->markAsCompleted();
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

    public function findOrder(int $id, array $columns = ['*']): Order
    {
        return Order::findOrFail($id, $columns);
    }

    /**
     * @throws Throwable
     */
    public function cancelOrderWithItems(Order $order, array $itemsToCancel): void
    {
        DB::transaction(function () use ($order, $itemsToCancel) {
            // Mark order items as deleted
            $this->orderItemService->deleteItem($itemsToCancel);

            if (! $this->orderItemService->orderHasItems($order->id)) {
                // Cancel order if there isn't any item for this order anymore
                $this->cancelOrder($order->id);
            }
        });
    }

    /**
     * @throws InvalidOrderException
     */
    public function validateOrderItems(Order $order, array $itemIds): void
    {
        $itemsCount = $this->orderItemService->getOrderItemsCount($order->id, $itemIds);
        if (count($itemIds) !== $itemsCount) {
            throw new InvalidOrderException(__('api.seats_not_belong_to_order'));
        }
    }
}
