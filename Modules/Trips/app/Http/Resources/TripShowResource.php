<?php

namespace Modules\Trips\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Buses\Http\Resources\BusResource;
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
            'bus' => new BusResource($this->bus),
            'seats' => TripSeatResource::collection($this->seats),
            'origin' => $this->origin,
            'destination' => $this->destination,
            'total_seats' => $this->total_seats,
            'reserved_seats_count' => $this->reserved_seats_count,
            'trip_date' => $this->trip_date->format('Y-m-d'),
            'departure_time' => $this->departure_time,
            'arrived_at' => $this->arrived_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
