<?php

namespace App\Enums;

use App\Traits\EnumValues;

enum OrderStatusEnum: string
{
    use EnumValues;

    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
