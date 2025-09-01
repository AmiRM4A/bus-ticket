<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PaymentController extends ApiController
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
        //
    }

    public function pay(int $order_id): JsonResponse
    {
        $order = Order::forUser(auth()->id())
            ->findOrFail($order_id);

        if (! $order->canPay()) { // Check if order is valid to pay (status)
            return $this->failure(
                message: 'Order is not valid to pay.',
                status: HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $paymentLink = $this->paymentService->createPaymentLink($order);

        return $this->success([
            'payment_link' => $paymentLink,
        ]);
    }

    public function callback(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->isAlreadyVerified()) {
            return $this->failure(
                message: 'Payment is already verified.',
                status: HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if (! $payment->isPendingToVerify() || ! $this->isValidVerifyRequest($request, $payment)) {
            return $this->failure(
                message: 'Payment is not valid to verify.',
                status: HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->paymentService->verify($payment);

        return $this->success();
    }

    private function isValidVerifyRequest(Request $request, Payment $payment): bool
    {
        // This part verifies the request's params
        //        if ($request->get('Status') !== 'OK') {
        //            $payment->update(['status' => PaymentStatusEnum::FAILED]);
        //
        //            return false;
        //        }

        //        if ($request->get('Authority') !== $payment->authority) {
        //            return false;
        //        }

        return true;
    }
}
