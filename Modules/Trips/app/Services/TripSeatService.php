<?php

namespace Modules\Trips\Services;

use Illuminate\Support\Collection;
use Modules\Buses\Services\BusSeatService;
use Modules\Passengers\Models\Passenger;
use Modules\Trips\Enums\TripSeatStatusEnum;
use Modules\Trips\Exceptions\InvalidSeatForReservation;
use Modules\Trips\Models\Trip;
use Modules\Trips\Models\TripSeat;

readonly class TripSeatService
{
    public function __construct(
        private int $reservationTtlMinutes,
        private BusSeatService $busSeatService,
    ) {
        //
    }

    /**
     * @throws InvalidSeatForReservation
     */
    public function reserveSeats(Trip $trip, Collection $passengers): Collection
    {
        // Each passenger has it's requested seat id as it's key
        // We'll get those keys (which are the seat ids requested for reservation)
        $seatIds = $passengers->keys()->toArray();

        // This would be the time that we'll release that seat's temporary reservation after that
        $expiresAt = now()->addMinutes($this->reservationTtlMinutes);

        // Getting bus seats map (key by the seats column & row. e.g => A_1, B_3, C_2 and...)
        $busSeatsMap = $this->busSeatService->getSeatMap($trip->bus_id);

        // Getting the bus occupied seats (those which are reserved or sold)
        $occupiedTripsSeats = $this->getOccupiedTripSeats($trip);
        $seatIdsSet = array_flip($seatIds);

        $reservedSeats = collect();
        foreach ($seatIds as $seatId) {
            $seat = TripSeat::with('busSeat')
                ->where('id', $seatId)
                ->where('trip_id', $trip->id)
                ->where('status', TripSeatStatusEnum::AVAILABLE)
                ->lockForUpdate()
                ->first();

            if (! $seat) {
                throw new InvalidSeatForReservation(__('api.seat_unavailable', ['seatId' => $seatId]));
            }

            // Validate gender policy for this seat
            $this->validateGenderPolicy(
                $seat,
                $passengers[$seatId],
                $busSeatsMap,
                $occupiedTripsSeats,
                $seatIdsSet
            );

            $reservedSeats->push($seat);

            $seat->update([
                'status' => TripSeatStatusEnum::RESERVED,
                'expires_at' => $expiresAt,
                'reserved_gender' => $passengers->get($seatId)->gender,
            ]);
        }

        return $reservedSeats;
    }

    public function markTripSeatsAsSold(int|array $seatId): bool
    {
        $seatId = is_int($seatId) ? [$seatId] : $seatId;

        return TripSeat::whereIn('id', $seatId)
            ->whereNotIn('status', [TripSeatStatusEnum::SOLD, TripSeatStatusEnum::AVAILABLE])
            ->update(['status' => TripSeatStatusEnum::SOLD]);
    }

    public function releaseSeats(array $seatIds): bool
    {
        return TripSeat::whereIn('id', $seatIds)
            ->update([
                'status' => TripSeatStatusEnum::AVAILABLE,
                'reserved_gender' => null,
                'expires_at' => null,
            ]);
    }

    private function getOccupiedTripSeats(Trip $trip): Collection
    {
        return TripSeat::with(['busSeat', 'orderItems.passenger'])
            ->where('trip_id', $trip->id)
            ->whereIn('status', [TripSeatStatusEnum::SOLD, TripSeatStatusEnum::RESERVED])
            ->get()
            ->keyBy('bus_seat_id');
    }

    /**
     * @throws InvalidSeatForReservation
     */
    private function validateGenderPolicy(
        TripSeat $seat,
        Passenger $passenger,
        Collection $busSeatsMap,
        Collection $occupiedTripsSeats,
        array $seatIdsSet
    ): void {
        $busSeat = $seat->busSeat;
        $adjacentCoords = $this->busSeatService->getAdjacentSeats($busSeat->row, $busSeat->column);

        foreach ($adjacentCoords as $coord) {
            $coordKey = $coord['row'].'_'.$coord['column'];

            // Check if adjacent bus seat exists
            if (! isset($busSeatsMap[$coordKey])) {
                continue; // Adjacent seat doesn't exist (edge of bus)
            }

            $adjacentBusSeat = $busSeatsMap[$coordKey];

            // Check if adjacent trip seat is occupied
            if (! isset($occupiedTripsSeats[$adjacentBusSeat->id])) {
                continue; // Adjacent seat is available, no conflict
            }

            $adjacentTripSeat = $occupiedTripsSeats[$adjacentBusSeat->id];

            // Condition 1: Adjacent seat is being booked in the same group
            if (isset($seatIdsSet[$adjacentTripSeat->id])) {
                continue; // Same group, no conflict
            }

            // Condition 2: Adjacent seat has passenger with same gender
            $adjacentPassenger = $adjacentTripSeat->orderItems->first()?->passenger;
            if ($adjacentPassenger && $adjacentPassenger->gender === $passenger->gender) {
                continue; // Same gender, no conflict
            }

            // Both conditions failed - throw exception
            throw new InvalidSeatForReservation(
                __('api.seat_gender_conflict', [
                    'seatId' => $seat->id,
                    'adjacentSeatId' => $adjacentTripSeat->id,
                ])
            );
        }
    }
}
