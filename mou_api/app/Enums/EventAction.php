<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static START()
 * @method static static USER_CANCEL()
 * @method static static SET_ALARM()
 * @method static static NOT_RESPONSE()
 * @method static static SEND_BEFORE_DAY_EVENT_START()
 */
final class EventAction extends Enum
{
    const START = 'START';

    const USER_CANCEL = 'USER_CANCEL';

    const SET_ALARM = 'SET_ALARM';

    const NOT_RESPONSE = 'NOT_RESPONSE_EVENT';

    const SEND_BEFORE_DAY_EVENT_START = 'SEND_BEFORE_DAY_EVENT_START';

    const SEND_CREATOR = 'SEND_CREATOR';
}
