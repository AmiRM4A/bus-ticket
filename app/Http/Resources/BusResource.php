<?php

namespace App\Http\Resources;

use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Bus
 */
class BusResource extends JsonResource
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
            'model' => $this->model,
            'plate' => $this->plate,
            'seats_count' => $this->seats_count,
        ];
    }
}
