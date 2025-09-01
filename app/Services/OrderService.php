<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Exceptions\InvalidOrderException;
use App\Exceptions\OrderCannotBeCancelledException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Trip;
use App\Models\TripReservation;
use App\Models\User;
use Illuminate\Support\Collection;
use Throwable;

readonly class OrderService
{
    public function __construct(
        private TripSeatService $tripSeatService
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

        // Create order items (which are the reserved seat id(s) here)
        $orderItemsData = [];
        $now = now();
        foreach ($seats as $seat) {
            $passenger = $passengers[$seat->id];
            $price = $trip->price_per_seat;

            $orderItemsData[] = [
                'order_id' => $order->id,
                'trip_seat_id' => $seat->id,
                'passenger_id' => $passenger->id,
                'price' => $price,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        OrderItem::insert($orderItemsData);

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
            throw new InvalidOrderException('Order has no items to fulfill');
        }

        // Prepare seat's selling data
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

        // Mark order as completed
        $order->markAsCompleted();

        // Sell the seats (add them to trip_reservations table)
        TripReservation::insert($reservationsToCreate);

        // Mark seats as sold (for the trip)
        $seatIds = $items->pluck('trip_seat_id')->toArray();
        $this->tripSeatService->markTripSeatsAsSold($seatIds);

        // We can also add the reserved seats count to the trip's reserved seats
    }

    /**
     * @throws OrderCannotBeCancelledException
     */
    public function cancelOrder(Order|int $order): bool
    {
        $order = is_int($order) ? Order::findOrFail($order) : $order;

        if (! $this->canCancelOrder($order)) {
            throw new OrderCannotBeCancelledException;
        }

        return $order->markAsCancelled();
    }

    /**
     * @throws InvalidOrderException
     */
    public function validateOrderSeats(Order $order, array $seats_to_cancel): Collection
    {
        $seats = $order->orderItems()
            ->whereIn('trip_seat_id', $seats_to_cancel)
            ->get();

        if (count($seats_to_cancel) !== $seats->count()) {
            throw new InvalidOrderException('Some seats do not belong to this order');
        }

        return $seats;
    }

    private function canCancelOrder(Order $order): bool
    {
        return in_array($order->status, [
            OrderStatusEnum::Pending,
            OrderStatusEnum::Completed,
        ], true);
    }

    public function deleteItems(int|array $id): ?bool
    {
        $id = is_int($id) ? [$id] : $id;

        return OrderItem::whereIn('id', $id)->delete();
    }
}
