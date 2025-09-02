<?php

namespace Modules\Payments\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Payments\Models\Payment;
use Modules\Payments\Services\PaymentService;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PaymentsController extends ApiController
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
        //
    }

    public function callback(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->isAlreadyVerified()) {
            return $this->failure(
                message: __('api.payment_already_verified'),
                status: HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if (! $payment->isPendingToVerify() || ! $this->isValidVerifyRequest($request, $payment)) {
            return $this->failure(
                message: __('api.payment_not_valid_to_verify'),
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
