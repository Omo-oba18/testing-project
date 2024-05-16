<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class EmployeePermissionColumn extends Enum
{
    const ADD_TASK = 'permission_add_task';

    const ADD_PROJECT = 'permission_add_project';

    const ADD_EMPLOYEE = 'permission_add_employee';

    const ADD_ROSTER = 'permission_add_roster';
}
