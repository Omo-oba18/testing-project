<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static TASK_CREATE()
 * @method static static TASK_EDIT()
 * @method static static TASK_NOT_RESPONSE()
 * @method static static PROJECT_CREATE()
 * @method static static PROJECT_EDIT()
 * @method static static PROJECT_NOT_RESPONSE()
 */
final class TaskAndProjectAction extends Enum
{
    const TASK_CREATE = 'TASK_CREATE';

    const TASK_EDIT = 'TASK_EDIT';

    const TASK_NOT_RESPONSE = 'NOT_RESPONSE_TASK';

    const PROJECT_CREATE = 'PROJECT_CREATE';

    const PROJECT_EDIT = 'PROJECT_EDIT';

    const PROJECT_NOT_RESPONSE = 'PROJECT_NOT_RESPONSE';

    const SEND_CREATOR = 'SEND_CREATOR';
}
