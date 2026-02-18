<?php

namespace App\Entity;

enum JobStatus: string
{
    case OPEN = 'open';
    case ASSIGNED = 'assigned';
    case COMPLETED = 'completed';
}
