<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static TASK()
 * @method static static PROJECT()
 * @method static static ROSTER()
 */
final class BusinessType extends Enum
{
    const TASK = 'TASK';

    const PROJECT = 'PROJECT';

    const ROSTER = 'ROSTER';

    const PROJECT_TASK = 'PROJECT_TASK';
}
