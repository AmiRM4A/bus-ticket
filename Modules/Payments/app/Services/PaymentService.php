<?php

namespace Modules\Payments\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Orders\Models\Order;
use Modules\Payments\Enums\PaymentStatusEnum;
use Modules\Payments\Events\PaymentVerified;
use Modules\Payments\Models\Payment;
use Throwable;

readonly class PaymentService
{
    public function createPaymentLink(Payment $payment): string
    {
        // Create payment link here (by using the $payment's price and transaction_id)
        // the below one is just a TEST LINK after gateway (callback)

        return route('api.payments.callback', [$payment->transaction_id]);
    }

    public function verify(Payment $payment): void
    {
        try {
            // Payment verification
            $this->verifyPaymentFromPSP($payment);
            $payment->markAsVerified();

            // Dispatching event for fulfilling the order of payment
            PaymentVerified::dispatch($payment->id, $payment->order_id);
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

    public function cancelPaymentsForOrder(int $orderId): void
    {
        Payment::forOrder($orderId)
            ->update(['status' => PaymentStatusEnum::CANCELLED]);
    }

    public function createPaymentForOrder(Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'amount' => $order->orderItems()->sum('price'),
            'transaction_id' => Str::uuid(),
        ]);
    }

    public function cancelByOrderIds(array $orderIds): bool
    {
        return Payment::whereIn('order_id', $orderIds)
            ->update(['status' => PaymentStatusEnum::CANCELLED]);
    }
}
