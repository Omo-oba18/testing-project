<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static DONE()
 * @method static static PROGRESS()
 * @method static static OPEN()
 */
final class StatusEvent extends Enum
{
    const DONE = 'done';

    const PROGRESS = 'progress';

    const OPEN = 'open';
}
