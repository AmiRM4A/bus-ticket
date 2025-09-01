<?php

namespace App\Services;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

readonly class PaymentService
{
    public function __construct(
        private OrderService $orderService
    ) {
        //
    }

    public function createPaymentLink(Order $order): string
    {
        $payment = $this->createPaymentForOrder($order);

        // Create payment link here (by using the $payment's price and transaction_id)
        // the below one is just a TEST LINK after gateway (callback)

        return route('payment.callback', [$payment->transaction_id]);
    }

    public function verify(Payment $payment): void
    {
        try {
            $this->verifyPaymentFromPSP($payment);

            DB::beginTransaction();

            $payment->markAsVerified();
            $this->orderService->fulfillOrder($payment->order);

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function verifyPaymentFromPSP(): bool
    {
        // Check the payment's verification from PSP
        // if verification wasn't true, throw an exception

        return true;
    }

    public function cancelPaymentsForOrder(int $order_id): void
    {
        Payment::forOrder($order_id)
            ->update(['status' => PaymentStatusEnum::CANCELLED]);
    }

    private function createPaymentForOrder(Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'amount' => $order->orderItems()->sum('price'),
            'transaction_id' => Str::uuid(),
        ]);
    }
}
