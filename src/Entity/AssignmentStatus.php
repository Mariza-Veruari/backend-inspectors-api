<?php

namespace App\Entity;

enum AssignmentStatus: string
{
    case SCHEDULED = 'scheduled';
    case COMPLETED = 'completed';
}
