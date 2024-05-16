<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ToEventForYou()
 * @method static static ToEventWaiting()
 */
final class NotificationAction extends Enum
{
    /**
     * Navigation to event for you to confirm.
     */
    public static function ToEventForYou(): string
    {
        return '/event';
    }

    /**
     * Navigation to event waiting to confirm.
     */
    public static function ToEventWaiting(): string
    {
        return '/event';
    }

    /**
     * Navigation to calendar.
     */
    public static function ToCalenderPersonal(): string
    {
        return '/home';
    }

    /**
     * Navigation to calendar.
     */
    public static function ToCalenderBusiness(): string
    {
        return '/calendar';
    }

    /**
     * Navigation to setting corp.
     */
    public static function toSettingCorp(): string
    {
        return '/setting';
    }

    /**
     * Navigation to todo single.
     */
    public static function toToDoSingle(): string
    {
        return '/todos';
    }

    /**
     * Navigation to todo group.
     */
    public static function toToDoGroup(): string
    {
        return '/todo_detail';
    }

    /**
     * Navigation to team.
     */
    public static function toTeam(): string
    {
        return '/employee';
    }
}
