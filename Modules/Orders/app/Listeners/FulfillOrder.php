<?php

namespace Modules\Orders\Listeners;

use Modules\Orders\Services\OrderService;
use Modules\Payments\Events\PaymentVerified;
use Illuminate\Contracts\Queue\ShouldQueue;

readonly class FulfillOrder implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private OrderService $orderService,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentVerified $event): void
    {
        $order = $this->orderService->findOrder($event->orderId);
        $this->orderService->fulfillOrder($order);
    }
}
