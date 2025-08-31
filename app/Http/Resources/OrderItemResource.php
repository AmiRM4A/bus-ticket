<?php

namespace App\Http\Resources;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'seat_id' => $this->tripSeat->id,
            'trip_id' => $this->tripSeat->trip_id,
            'passenger' => [
                'first_name' => $this->passenger->first_name,
                'last_name' => $this->passenger->last_name,
                'national_code' => $this->passenger->national_code,
            ],
        ];
    }
}
