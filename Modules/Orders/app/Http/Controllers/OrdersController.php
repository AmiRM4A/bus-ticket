<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Log;
use Modules\Orders\Events\OrderCancelled;
use Modules\Orders\Exceptions\InvalidOrderException;
use Modules\Orders\Http\Requests\CancelOrderRequest;
use Modules\Orders\Http\Resources\OrderResource;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderItemService;
use Modules\Orders\Services\OrderService;
use Modules\Payments\Services\PaymentService;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class OrdersController extends ApiController
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly OrderService $orderService,
        private readonly OrderItemService $orderItemService
    ) {
        //
    }

    public function show(int $orderId): JsonResponse
    {
        $order = Order::forUser(auth()->id())
            ->with([
                'orderItems:id,trip_seat_id,passenger_id,order_id,price',
                'orderItems.passenger:id,first_name,last_name,national_code',
                'orderItems.tripSeat:id,trip_id,bus_seat_id',
                'orderItems.tripSeat.trip:id,bus_id',
                'trip:id,from_province_id,to_province_id,trip_date,departure_time',
            ])
            ->findOrFail($orderId);

        return $this->success(new OrderResource($order));
    }

    public function checkout(int $orderId): JsonResponse
    {
        $order = Order::forUser(auth()->id())
            ->findOrFail($orderId);

        if (! $order->canPay()) { // Check if order is valid to pay (status)
            return $this->failure(
                message: __('api.order_not_valid_to_pay'),
                status: HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $payment = $this->paymentService->createPaymentForOrder($order);
        $paymentLink = $this->paymentService->createPaymentLink($payment);

        // An event could get triggered here
        // for sending sms to the order's owner

        return $this->success([
            'payment_url' => $paymentLink,
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
        ]);
    }

    public function destroy(CancelOrderRequest $request, int $orderId): JsonResponse
    {
        // Fetching order_id for the auth user (avoid selecting other user's order)
        $order = $this->orderService->getOrderForUser($orderId, auth()->id());

        // Check if order has correct status to get cancelled
        if (! $this->orderService->canCancelOrder($order)) {
            return $this->failure(message: __('api.order_not_valid_to_cancel'), status: HttpResponse::HTTP_BAD_REQUEST);
        }

        $itemsToCancel = $request->validated('item_ids');

        try {
            if ($itemsToCancel) { // If any seats provided, check if all the seats are belong to this order (Validation)
                $this->orderService->validateOrderItems($order, $itemsToCancel);
            } else { // Else, choose all seats (items) of the order to get deleted
                $itemsToCancel = $this->orderItemService->getItemsForOrder($order->id, ['id'])
                    ->pluck('id')
                    ->toArray();
            }

            $this->orderService->cancelOrderWithItems($order, $itemsToCancel);

            OrderCancelled::dispatch($order->id, $itemsToCancel);

            return $this->success();
        } catch (InvalidOrderException $e) {
            return $this->failure(message: $e->getMessage(), status: HttpResponse::HTTP_NOT_FOUND);
        } catch (Throwable $th) {
            Log::error(__('api.error_cancelling_reservation'), [
                'message' => $th->getMessage(),
                'order_id' => $order->id,
                'items_to_cancel' => $itemsToCancel,
            ]);

            return $this->failure(message: __('api.cancellation_failed'));
        }
    }
}
