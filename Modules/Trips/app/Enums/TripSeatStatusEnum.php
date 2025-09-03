<?php

namespace Modules\Trips\Enums;

use App\Traits\EnumValues;

enum TripSeatStatusEnum: string
{
    use EnumValues;

    case RESERVED = 'RESERVED';
    case AVAILABLE = 'AVAILABLE';
    case SOLD = 'SOLD';
}
