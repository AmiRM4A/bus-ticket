<?php

namespace Modules\Trips\Console;

use DB;
use Illuminate\Console\Command;
use Modules\Orders\Services\OrderItemService;
use Modules\Orders\Services\OrderService;
use Modules\Payments\Services\PaymentService;
use Modules\Trips\Enums\TripSeatStatusEnum;
use Modules\Trips\Models\TripSeat;
use Modules\Trips\Services\TripSeatService;

class ReleaseExpiredSeats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-expired-seats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Releases expired trip seats and cancels associated orders and payments.';

    public function __construct(
        private readonly TripSeatService $tripSeatService,
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
        private readonly OrderItemService $orderItemService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Checking for expired trip seats...');

        // Find trip seats that have expired and are still reserved
        $expiredTripSeats = TripSeat::where('status', TripSeatStatusEnum::RESERVED)
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredTripSeats->isEmpty()) {
            $this->info('No expired trip seats found.');

            return;
        }

        $this->info(sprintf('Found %d expired trip seats.', $expiredTripSeats->count()));

        DB::transaction(function () use ($expiredTripSeats) {
            $expiredTripSeatIds = $expiredTripSeats->pluck('id')->toArray();

            // Release the seats
            $this->tripSeatService->releaseSeats($expiredTripSeatIds);
            $this->info(sprintf('Released %d trip seats.', count($expiredTripSeatIds)));

            // Get associated order items and then orders
            $orderIds = $this->orderItemService->findOrderIdsBySeatIds($expiredTripSeatIds);

            if (! empty($orderIds)) {
                // Cancel associated orders
                $this->orderService->cancelOrders($orderIds);

                // Cancel associated payments to fetched orders
                $this->paymentService->cancelByOrderIds($orderIds);
            }
        });

        $this->info('Expired trip seats processed successfully.');
    }
}
