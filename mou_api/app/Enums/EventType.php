<?php

namespace App\Enums;

enum EventType: String
{
    case TASK = 'TASK';

    case PROJECT_TASK = 'PROJECT_TASK';

    case EVENT = 'EVENT';

    case ROSTER = 'ROSTER';
}
