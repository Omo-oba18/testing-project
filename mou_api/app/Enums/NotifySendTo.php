<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static BUSINESS()
 * @method static static PERSONAL()
 */
final class NotifySendTo extends Enum
{
    const BUSINESS = 1;

    const PERSONAL = 0;
}
