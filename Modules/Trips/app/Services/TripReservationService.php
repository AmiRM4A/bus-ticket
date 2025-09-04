<?php

namespace Modules\Trips\Services;

use DB;
use Illuminate\Support\Collection;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderService;
use Modules\Passengers\Services\PassengerService;
use Modules\Trips\Models\Trip;
use Modules\Users\Models\User;
use Throwable;

readonly class TripReservationService
{
    public function __construct(
        private PassengerService $passengerService,
        private TripSeatService $tripSeatService,
        private OrderService $orderService
    ) {
        //
    }

    /**
     * @throws Throwable
     */
    public function createReservation(User $user, Trip $trip, array $passengersData): Order
    {
        return DB::transaction(function () use ($user, $trip, $passengersData) {
            // Resolve passengers (create new ones if needed) - keyed by seat_id
            $passengers = $this->passengerService->createOrRetrievePassengers($passengersData);

            // Validate and reserve seats (mark them as reserved/hold)
            $reservedSeats = $this->tripSeatService->reserveSeats($trip, $passengers);

            // Create order with items and payment
            return $this->orderService->createOrderWithItems($user, $trip, $reservedSeats, $passengers);
        });
    }

    public function sellSeatsToPassengers(int $tripId, array $data)
    {
        dd($tripId, $data);

        // Mark seats as sold (for the trip)
        $seatIds = $items->pluck('trip_seat_id')->toArray();
        $this->tripSeatService->markTripSeatsAsSold($seatIds);
    }

    private function prepareReservationsData(Collection $items): array
    {
        $now = now();
        $reservationsToCreate = [];

        foreach ($items as $item) {
            $reservationsToCreate[] = [
                'passenger_id' => $item->passenger_id,
                'trip_id' => $item->tripSeat->trip_id,
                'trip_seat_id' => $item->trip_seat_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $reservationsToCreate;
    }
}
