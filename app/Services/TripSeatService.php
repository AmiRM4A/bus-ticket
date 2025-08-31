<?php

namespace App\Services;

use App\Enums\TripSeatStatusEnum;
use App\Exceptions\InvalidSeatForReservation;
use App\Models\BusSeat;
use App\Models\Passenger;
use App\Models\Trip;
use App\Models\TripSeat;
use Illuminate\Support\Collection;

readonly class TripSeatService
{
    public function __construct(
        private int $reservationTtlMinutes = 15
    ) {
        //
    }

    /**
     * @throws InvalidSeatForReservation
     */
    public function reserveSeats(Trip $trip, Collection $passengers): Collection
    {
        $seatIds = $passengers->keys()->toArray();
        $expiresAt = now()->addMinutes($this->reservationTtlMinutes);

        $busSeatsMap = $this->getBusSeatsMap($trip->bus_id);
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
                throw new InvalidSeatForReservation("Seat $seatId is no longer available");
            }

            // Validate gender policy
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

    private function getBusSeatsMap(int $busId): Collection
    {
        return BusSeat::where('bus_id', $busId)
            ->get()
            ->keyBy(function ($seat) {
                return $seat->row.'_'.$seat->column;
            });
    }

    private function getOccupiedTripSeats(Trip $trip): Collection
    {
        return TripSeat::with(['busSeat', 'orderItems.passenger'])
            ->where('trip_id', $trip->id)
            ->whereIn('status', [TripSeatStatusEnum::SOLD, TripSeatStatusEnum::RESERVED])
            ->get()
            ->keyBy('bus_seat_id');
    }

    private function validateGenderPolicy(
        TripSeat $seat,
        Passenger $passenger,
        Collection $busSeatsMap,
        Collection $occupiedTripsSeats,
        array $seatIdsSet
    ): void {
        $busSeat = $seat->busSeat;
        $adjacentCoords = $this->getAdjacentSeats($busSeat->row, $busSeat->column);

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
                "Seat $busSeat->name cannot be booked due to gender policy conflict with adjacent seat $adjacentBusSeat->name"
            );
        }
    }

    private function getAdjacentSeats(int $row, string $column): array
    {
        $layout = ['A', 'B', 'C', 'D']; // Left to right
        $index = array_search($column, $layout);
        $adjacent = [];

        if ($index > 0) {
            $adjacent[] = ['row' => $row, 'column' => $layout[$index - 1]];
        }
        if ($index < count($layout) - 1) {
            $adjacent[] = ['row' => $row, 'column' => $layout[$index + 1]];
        }

        return $adjacent;
    }
}
