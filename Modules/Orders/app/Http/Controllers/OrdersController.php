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
                'orderItems:trip_seat_id,passenger_id,order_id,price',
                'orderItems.passenger:id,first_name,last_name,national_code',
                'orderItems.tripSeat:id,trip_id,bus_seat_id',
                'orderItems.tripSeat.trip:id,bus_id',
                'trip:id,from_province_id,to_province_id,trip_date,departure_time',
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

        $payment = $this->paymentService->createPaymentForOrder($order);
        $paymentLink = $this->paymentService->createPaymentLink($payment);

        return $this->success([
            'payment_url' => $paymentLink,
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
        ]);
    }
}
