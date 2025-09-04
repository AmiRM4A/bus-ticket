<?php

namespace Modules\Trips\Listeners;

use Modules\Orders\Events\OrderCancelled;
use Modules\Orders\Services\OrderItemService;
use Modules\Trips\Services\TripSeatService;
use Illuminate\Contracts\Queue\ShouldQueue;

readonly class RevertCancelledTripSeats implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private OrderItemService $orderItemService,
        private TripSeatService $tripSeatService
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCancelled $event): void
    {
        // Make the cancelled seat ids available again
        $seatIds = $this->orderItemService->getTripSeatsIdsByItemIds($event->cancelledItemsIds);
        $this->tripSeatService->releaseSeats($seatIds);
    }
}
