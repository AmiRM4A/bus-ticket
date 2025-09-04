<?php

namespace Modules\Payments\Listeners;

use Modules\Orders\Events\OrderCancelled;
use Modules\Payments\Services\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;

readonly class CancelCancelledOrderPayments implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private PaymentService $paymentService
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCancelled $event): void
    {
        $this->paymentService->cancelPaymentsForOrder($event->orderId);
    }
}
