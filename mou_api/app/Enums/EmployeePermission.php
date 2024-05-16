<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static NOT_ALLOW()
 * @method static static ALLOW()
 */
final class EmployeePermission extends Enum
{
    const NOT_ALLOW = false;

    const ALLOW = true;
}
