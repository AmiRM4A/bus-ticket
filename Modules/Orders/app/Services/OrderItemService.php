<?php

namespace Modules\Orders\Services;

use Illuminate\Support\Collection;
use Modules\Orders\Models\OrderItem;
use Modules\Trips\Models\Trip;

class OrderItemService
{
    public function createOrderItems(int $orderId, Trip $trip, Collection $seats, Collection $passengers): void
    {
        $orderItemsData = [];
        $now = now();

        foreach ($seats as $seat) {
            $passenger = $passengers[$seat->id];
            $price = $trip->price_per_seat;

            $orderItemsData[] = [
                'order_id' => $orderId,
                'trip_seat_id' => $seat->id,
                'passenger_id' => $passenger->id,
                'price' => $price,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        OrderItem::insert($orderItemsData);
    }

    public function orderHasItems(int $orderId): bool
    {
        return OrderItem::where('order_id', $orderId)->exists();
    }

    public function getOrderItemsCount(int $orderId, array $itemIds): int
    {
        return OrderItem::where('order_id', $orderId)
            ->whereIn('id', $itemIds)
            ->count();
    }

    public function deleteItem(int|array $id): ?bool
    {
        $id = is_int($id) ? [$id] : $id;

        return OrderItem::whereIn('id', $id)->delete();
    }

    public function findOrderIdsBySeatIds(array $seatIds): array
    {
        return OrderItem::whereIn('trip_seat_id', $seatIds)
            ->pluck('order_id')
            ->unique()
            ->all();
    }

    public function getItemsForOrder(int $orderId, array $columns = ['*']): Collection
    {
        return OrderItem::forOrder($orderId)->get($columns);
    }

    public function getTripSeatsIdsByItemIds(array $itemIds): array
    {
        return OrderItem::whereIn('id', $itemIds)
            ->pluck('trip_seat_id')
            ->toArray();
    }
}
