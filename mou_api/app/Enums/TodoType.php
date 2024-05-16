<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static SINGLE()
 * @method static static GROUP()
 */
final class TodoType extends Enum
{
    const SINGLE = 'SINGLE';

    const GROUP = 'GROUP';
}
