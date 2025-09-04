<?php

namespace Modules\Orders\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Trips\Models\Trip;

/**
 * @mixin Trip
 */
class OrderTripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'from_province' => $this->origin->name,
            'to_province' => $this->destination->name,
            'trip_date' => $this->trip_date->format('Y-m-d'),
            'departure_time' => $this->departure_time,
        ];
    }
}
