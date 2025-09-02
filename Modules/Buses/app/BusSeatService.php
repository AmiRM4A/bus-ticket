<?php

namespace Modules\Buses;

use Illuminate\Support\Collection;
use Modules\Buses\Models\BusSeat;

class BusSeatService
{
    public function getSeatMap(int $bus_id): Collection
    {
        return BusSeat::where('bus_id', $bus_id)
            ->get()
            ->keyBy(function ($seat) {
                return $seat->row.'_'.$seat->column;
            });
    }

    public function getAdjacentSeats(int $row, string $column): array
    {
        $layout = ['A', 'B', 'C', 'D']; // Seat layout left to right
        $index = array_search($column, $layout);
        $adjacent = [];

        // Check seat to the left
        if ($index > 0) {
            $adjacent[] = ['row' => $row, 'column' => $layout[$index - 1]];
        }

        // Check seat to the right
        if ($index < count($layout) - 1) {
            $adjacent[] = ['row' => $row, 'column' => $layout[$index + 1]];
        }

        return $adjacent;
    }
}
