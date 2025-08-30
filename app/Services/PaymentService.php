<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentService
{
    public static function pay(Order $order): string
    {
        $payment = self::createPaymentForOrder($order);

        // Create payment link here (by using the $payment's price and transaction_id)
        // the below one is just a TEST LINK after gateway (callback)

        return route('payment.callback', [$payment->transaction_id]);
    }

    public static function verify(Payment $payment)
    {
        // Check the payment's verification from PSP
        // if the verification was valid, mark payment as paid
        // else, throw an exception

        $payment->markAsVerified();
    }

    private static function createPaymentForOrder(Order $order): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'amount' => $order->orderItems()->sum('price'),
            'transaction_id' => Str::uuid(),
        ]);
    }
}
