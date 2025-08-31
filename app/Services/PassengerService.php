<?php

namespace App\Services;

use App\Models\Passenger;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PassengerService
{
    public function createOrRetrievePassengers(array $passengersData): Collection
    {
        $nationalCodes = array_column($passengersData, 'national_code');

        // Check for duplicates in input
        if (count($nationalCodes) !== count(array_unique($nationalCodes))) {
            throw new ValidationException('Duplicate passengers found in request');
        }

        $existingPassengers = Passenger::whereIn('national_code', $nationalCodes)
            ->get()
            ->keyBy('national_code');

        $now = now();
        $resolvedPassengers = collect();
        $passengersToCreate = [];

        foreach ($passengersData as $passengerData) {
            $nationalCode = $passengerData['national_code'];
            $seatId = $passengerData['trip_seat_id'];

            if ($existingPassengers->has($nationalCode)) {
                $resolvedPassengers[$seatId] = $existingPassengers[$nationalCode];
            } else {
                $passengersToCreate[] = [
                    'first_name' => $passengerData['first_name'],
                    'last_name' => $passengerData['last_name'],
                    'mobile' => $passengerData['mobile'],
                    'national_code' => $nationalCode,
                    'birth_date' => $passengerData['birth_date'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'gender' => $passengerData['gender'],
                ];
                // Store trip_seat_id mapping for later
                $resolvedPassengers[$seatId] = $nationalCode; // Temporary placeholder
            }
        }

        // Bulk create new passengers if needed
        if (! empty($passengersToCreate)) {
            Passenger::insert($passengersToCreate);
            $newPassengers = Passenger::whereIn('national_code', array_column($passengersToCreate, 'national_code'))
                ->get()
                ->keyBy('national_code');

            // Replace national_code placeholders with actual passenger objects
            foreach ($resolvedPassengers as $seatId => $value) {
                if (is_string($value)) { // It's a national_code placeholder
                    $resolvedPassengers[$seatId] = $newPassengers[$value];
                }
            }
        }

        return $resolvedPassengers;
    }
}
