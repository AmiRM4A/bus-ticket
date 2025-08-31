<?php

namespace App\Enums;

enum GenderEnum: string
{
    case MALE = 'Male';
    case FEMALE = 'Female';

    public static function resolveByNum(?int $num): ?GenderEnum
    {
        if ($num === 0) {
            return self::FEMALE;
        }

        if ($num === 1) {
            return self::MALE;
        }

        return null;
    }
}
