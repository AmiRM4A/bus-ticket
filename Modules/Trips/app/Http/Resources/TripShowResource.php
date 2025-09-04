<?php

namespace Modules\Trips\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Buses\Http\Resources\BusResource;
use Modules\Locations\Http\Resources\ProvinceResource;
use Modules\Trips\Models\Trip;

/**
 * @mixin Trip
 */
class TripShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bus' => BusResource::make($this->whenLoaded('bus')),
            'seats' => TripSeatResource::collection($this->whenLoaded('seats')),
            'origin' => ProvinceResource::make($this->whenLoaded('origin')),
            'destination' => ProvinceResource::make($this->whenLoaded('destination')),
            'total_seats' => $this->total_seats,
            'reserved_seats_count' => $this->reserved_seats_count,
            'trip_date' => $this->trip_date->format('Y-m-d'),
            'departure_time' => $this->departure_time,
            'price_per_seat' => $this->price_per_seat,
            'arrived_at' => $this->arrived_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
