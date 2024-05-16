<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static START()
 * @method static static EDIT()
 * @method static static NOT_RESPONSE()
 */
final class RosterAction extends Enum
{
    const START = 'ROSTER_START';

    const EDIT = 'ROSTER_EDIT';

    const NOT_RESPONSE = 'NOT_RESPONSE_ROSTER';

    const SEND_CREATOR = 'SEND_CREATOR';
}
