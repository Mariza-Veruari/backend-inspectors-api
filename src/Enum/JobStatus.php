<?php

declare(strict_types=1);

namespace App\Enum;

enum JobStatus: string
{
    case OPEN = 'OPEN';
    case ASSIGNED = 'ASSIGNED';
    case COMPLETED = 'COMPLETED';

    public const ALL = [
        self::OPEN->value,
        self::ASSIGNED->value,
        self::COMPLETED->value,
    ];
}
