<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends ApiController
{
    public function show(int $order_id): JsonResponse
    {
        $order = Order::forUser(auth()->id())
            ->with([
                'orderItems:trip_seat_id,passenger_id,order_id',
                'orderItems.passenger:id,first_name,last_name,national_code',
                'orderItems.tripSeat:id,trip_id',
                'orderItems.tripSeat.trip:id',
            ])
            ->findOrFail($order_id);

        return $this->success(new OrderResource($order));
    }
}
