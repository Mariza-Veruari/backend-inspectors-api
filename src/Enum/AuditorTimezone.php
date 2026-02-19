<?php

declare(strict_types=1);

namespace App\Enum;

enum AuditorTimezone: string
{
    case MADRID = 'Europe/Madrid';
    case MEXICO_CITY = 'America/Mexico_City';
    case LONDON = 'Europe/London';

    public const ALL = [
        self::MADRID->value,
        self::MEXICO_CITY->value,
        self::LONDON->value,
    ];
}
