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

    public function getOrderItemsCountBySeatIds(int $orderId, array $seatIds): int
    {
        return OrderItem::where('order_id', $orderId)
            ->whereIn('trip_seat_id', $seatIds)
            ->count();
    }

    public function deleteItems(int|array $id): ?bool
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

    public function getItemsBySeatIds(array $seatIds): Collection
    {
        return OrderItem::whereIn('trip_seat_id', $seatIds)->get();
    }

    public function prepareReservationsData(Collection $items): array
    {
        $now = now();
        $reservationsToCreate = [];

        foreach ($items as $item) {
            $reservationsToCreate[] = [
                'passenger_id' => $item->passenger_id,
                'trip_id' => $item->tripSeat->trip_id,
                'trip_seat_id' => $item->trip_seat_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $reservationsToCreate;
    }

    public function getSeatIdsFromItems(Collection $items): array
    {
        return $items->pluck('trip_seat_id')->toArray();
    }
}
