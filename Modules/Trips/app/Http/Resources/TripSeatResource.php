<?php

namespace Modules\Trips\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Trips\Enums\GenderEnum;
use Modules\Trips\Enums\TripSeatStatusEnum;
use Modules\Trips\Models\TripSeat;

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
            'row' => $this->busSeat->row,
            'column' => $this->busSeat->column,
            'reserved_gender' => GenderEnum::resolveByNum($this->reserved_gender),
            'is_available' => $this->status === TripSeatStatusEnum::AVAILABLE,
            'is_reserved' => $this->status === TripSeatStatusEnum::RESERVED,
            'is_sold' => $this->status === TripSeatStatusEnum::SOLD,
        ];
    }
}
