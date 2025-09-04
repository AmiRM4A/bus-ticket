<?php

namespace Modules\Orders\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Orders\Models\OrderItem;

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
            'trip_seat_id' => $this->tripSeat->id,
            'seat_name' => $this->tripSeat->busSeat->name,
            'seat_number' => $this->tripSeat->busSeat->seat_number,
            'passenger' => $this->whenLoaded('passenger', function () {
                return [
                    'first_name' => $this->passenger->first_name,
                    'last_name' => $this->passenger->last_name,
                    'national_code' => $this->passenger->national_code,
                ];
            }),
            'price' => $this->price,
        ];
    }
}
