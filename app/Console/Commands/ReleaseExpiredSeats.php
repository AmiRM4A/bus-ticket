<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\TripSeatStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\TripSeat;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\TripSeatService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        protected TripSeatService $tripSeatService,
        protected OrderService $orderService,
        protected PaymentService $paymentService,
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
            $orderItems = OrderItem::whereIn('trip_seat_id', $expiredTripSeatIds)->get();
            $orderIds = $orderItems->pluck('order_id')->unique()->toArray();

            if (! empty($orderIds)) {
                // Cancel associated orders
                Order::whereIn('id', $orderIds)
                    ->update(['status' => OrderStatusEnum::Cancelled]);

                // Cancel associated payments to fetched orders
                Payment::whereIn('order_id', $orderIds)
                    ->update(['status' => PaymentStatusEnum::CANCELLED]);
            }
        });

        $this->info('Expired trip seats processed successfully.');
    }
}
