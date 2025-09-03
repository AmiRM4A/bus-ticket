<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Modules\Orders\Http\Resources\OrderResource;
use Modules\Orders\Models\Order;
use Modules\Payments\Services\PaymentService;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class OrdersController extends ApiController
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
        //
    }

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

    public function checkout(int $order_id): JsonResponse
    {
        $order = Order::forUser(auth()->id())
            ->findOrFail($order_id);

        if (! $order->canPay()) { // Check if order is valid to pay (status)
            return $this->failure(
                message: __('api.order_not_valid_to_pay'),
                status: HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $paymentLink = $this->paymentService->createPaymentLink($order);

        return $this->success([
            'payment_link' => $paymentLink,
        ]);
    }
}
