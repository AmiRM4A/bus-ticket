<?php

namespace App\Http\Resources;

use App\Enums\TripSeatStatusEnum;
use App\Models\TripSeat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TripSeat
 */
class TripSeatResource extends JsonResource
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
            'status' => $this->status,
            'name' => $this->busSeat?->name,
            'is_available' => $this->status === TripSeatStatusEnum::AVAILABLE,
            'is_reserved' => $this->status === TripSeatStatusEnum::RESERVED,
            'is_sold' => $this->status === TripSeatStatusEnum::SOLD,
        ];
    }
}
