<?php

namespace App\Services;

use App\Enums\TripSeatStatusEnum;
use App\Exceptions\InvalidSeatForReservation;
use App\Models\BusSeat;
use App\Models\Passenger;
use App\Models\Trip;
use App\Models\TripSeat;
use Illuminate\Support\Collection;

class TripSeatService
{
    /**
     * @throws InvalidSeatForReservation
     */
    public static function reserveSeats(Trip $trip, Collection $passengers): Collection
    {
        $seatIds = $passengers->keys()->toArray();
        $expiresAt = now()->addMinutes(config('app.seat_reservation_ttl_minutes'));

        // Pre-fetch data for optimization
        $busSeatsMap = self::getBusSeatsMap($trip->bus_id);
        $occupiedTripsSeats = self::getOccupiedTripSeats($trip);
        $seatIdsSet = array_flip($seatIds); // O(1) lookup

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
            self::validateGenderPolicy(
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

    private static function getBusSeatsMap(int $bus_id): Collection
    {
        return BusSeat::where('bus_id', $bus_id)
            ->get()
            ->keyBy(function ($seat) {
                return $seat->row.'_'.$seat->column;
            });
    }

    private static function getOccupiedTripSeats(Trip $trip): Collection
    {
        return TripSeat::with(['busSeat', 'orderItems.passenger'])
            ->where('trip_id', $trip->id)
            ->whereIn('status', [TripSeatStatusEnum::SOLD, TripSeatStatusEnum::RESERVED])
            ->get()
            ->keyBy('bus_seat_id');
    }

    private static function validateGenderPolicy(
        TripSeat $seat,
        Passenger $passenger,
        Collection $bus_seats_map,
        Collection $occupied_trips_seats,
        array $seat_ids_set
    ): void {
        $busSeat = $seat->busSeat;
        $adjacentCoords = self::getAdjacentSeats($busSeat->row, $busSeat->column);

        foreach ($adjacentCoords as $coord) {
            $coordKey = $coord['row'].'_'.$coord['column'];

            // Check if adjacent bus seat exists
            if (! isset($bus_seats_map[$coordKey])) {
                continue; // Adjacent seat doesn't exist (edge of bus)
            }

            $adjacentBusSeat = $bus_seats_map[$coordKey];

            // Check if adjacent trip seat is occupied
            if (! isset($occupied_trips_seats[$adjacentBusSeat->id])) {
                continue; // Adjacent seat is available, no conflict
            }

            $adjacentTripSeat = $occupied_trips_seats[$adjacentBusSeat->id];

            // Condition 1: Adjacent seat is being booked in the same group
            if (isset($seat_ids_set[$adjacentTripSeat->id])) {
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

    private static function getAdjacentSeats(int $row, string $column): array
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

    public static function markTripSeatsAsSold(int|array $seatId): bool
    {
        $seatId = is_int($seatId) ? [$seatId] : $seatId;

        return TripSeat::whereIn('id', $seatId)
            ->whereNotIn('status', [TripSeatStatusEnum::SOLD, TripSeatStatusEnum::AVAILABLE]) // Only update reserved seats (hold ones)
            ->update(['status' => TripSeatStatusEnum::SOLD]);
    }
}
