<?php

declare(strict_types=1);

namespace App\Enum;

/** Allowed auditor timezones. Used for validation and to format datetimes in API responses. */
enum AuditorTimezone: string
{
    case MADRID = 'Europe/Madrid';
    case MEXICO_CITY = 'America/Mexico_City';
    case LONDON = 'Europe/London';

    /** All allowed values (for Choice constraint / OpenAPI). */
    public const ALL = [
        self::MADRID->value,
        self::MEXICO_CITY->value,
        self::LONDON->value,
    ];
}
