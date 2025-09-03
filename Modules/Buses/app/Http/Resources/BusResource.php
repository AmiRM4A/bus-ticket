<?php

namespace Modules\Buses\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Buses\Models\Bus;

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
