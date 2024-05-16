<?php

namespace App\Enums;

enum EventTab: int
{
    case FOR_YOU_TO_CONFIRM = 1;

    case WAITING_TO_CONFIRM = 2;

    case CONFIRMED = 3;

}
