<?php

namespace Modules\Payments\Enums;

use App\Traits\EnumValues;

enum PaymentStatusEnum: string
{
    use EnumValues;

    case PENDING = 'Pending';
    case SUCCESS = 'Success';
    case FAILED = 'Failed';
    case CANCELLED = 'Cancelled';
}
