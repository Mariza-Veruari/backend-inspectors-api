<?php

declare(strict_types=1);

namespace App\Enum;

/** Job lifecycle status. Used for validation and API docs. */
enum JobStatus: string
{
    case OPEN = 'OPEN';
    case ASSIGNED = 'ASSIGNED';
    case COMPLETED = 'COMPLETED';

    /** All allowed values (for Choice constraint / OpenAPI). */
    public const ALL = [
        self::OPEN->value,
        self::ASSIGNED->value,
        self::COMPLETED->value,
    ];
}
