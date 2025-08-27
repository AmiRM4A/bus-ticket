<?php

namespace App\Services;

use App\Enums\TripSeatStatusEnum;
use App\Exceptions\InvalidSeatForReservation;
use App\Exceptions\SeatAlreadyBookedException;
use App\Models\Trip;
use App\Models\TripSeat;
use Illuminate\Support\Collection;

class TripSeatService
{
    /**
     * @throws InvalidSeatForReservation
     * @throws SeatAlreadyBookedException
     */
    public static function reserveSeats(Trip $trip, array $seatIds): Collection
    {
        // First check availability without locking
        self::validateSeatAvailability($trip, $seatIds);

        $reservedSeats = collect();
        foreach ($seatIds as $seatId) {
            $seat = TripSeat::where('id', $seatId)
                ->where('trip_id', $trip->id)
                ->where('status', TripSeatStatusEnum::AVAILABLE)
                ->lockForUpdate()
                ->first();

            if (! $seat) {
                throw new SeatAlreadyBookedException("Seat $seatId is no longer available");
            }

            $reservedSeats->push($seat);
        }

        // Mark seats as reserved (hold)
        TripSeat::whereIn('id', $seatIds)
            ->update(['status' => TripSeatStatusEnum::RESERVED]);

        return $reservedSeats;
    }

    /**
     * @throws InvalidSeatForReservation
     */
    private static function validateSeatAvailability(Trip $trip, array $seatIds): void
    {
        $availableSeats = TripSeat::where('trip_id', $trip->id)
            ->whereIn('id', $seatIds)
            ->where('status', TripSeatStatusEnum::AVAILABLE)
            ->count();

        if ($availableSeats !== count($seatIds)) {
            throw new InvalidSeatForReservation('Some requested seats are not available');
        }

        // Check for duplicate seat IDs
        if (count($seatIds) !== count(array_unique($seatIds))) {
            throw new InvalidSeatForReservation('Duplicate seat IDs found!');
        }
    }
}
