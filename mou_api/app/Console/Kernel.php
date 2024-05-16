<?php

namespace App\Console;

use App\Console\Commands\NotResponseEventCommand;
use App\Console\Commands\NotResponseRosterCommand;
use App\Console\Commands\NotResponseRosterTypeTwo;
use App\Console\Commands\NotResponseTaskCommand;
use App\Console\Commands\NotResponseTaskTypeTwo;
use App\Console\Commands\SendEventSetAlarmCommand;
use App\Console\Commands\SendEventStartCommand;
use App\Console\Commands\SendEventTomorrowCommand;
use App\Console\Commands\SendRosterStart;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        SendRosterStart::class,
        SendEventStartCommand::class,
        SendEventTomorrowCommand::class,
        SendEventSetAlarmCommand::class,
        NotResponseRosterCommand::class,
        NotResponseTaskCommand::class,
        NotResponseRosterTypeTwo::class,
        NotResponseTaskTypeTwo::class,
        NotResponseEventCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('mou:send-roster-start')->everyMinute()->appendOutputTo(storage_path('logs'.DIRECTORY_SEPARATOR.'laravel.log'));
        $schedule->command('mou:send-event-start-command')->everyMinute()->appendOutputTo(storage_path('logs'.DIRECTORY_SEPARATOR.'laravel.log'));
        $schedule->command('mou:send-event-tomorrow-command')->everyMinute()->appendOutputTo(storage_path('logs'.DIRECTORY_SEPARATOR.'laravel.log'));
        $schedule->command('mou:send-event-set-alarm-command')->everyMinute()->appendOutputTo(storage_path('logs'.DIRECTORY_SEPARATOR.'laravel.log'));
        $schedule->command('mou:not-response-roster-command')->everyMinute()->appendOutputTo(storage_path('logs'.DIRECTORY_SEPARATOR.'laravel.log'));
        $schedule->command('mou:not-response-event-command')->everyMinute()->appendOutputTo(storage_path('logs'.DIRECTORY_SEPARATOR.'laravel.log'));
        $schedule->command('mou:not-response-task-command')->dailyAt('00:01')->appendOutputTo(storage_path('logs'.DIRECTORY_SEPARATOR.'laravel.log'));
        $schedule->command('mou:not-response-roster-type-two')->dailyAt('17:00')->appendOutputTo(storage_path('logs'.DIRECTORY_SEPARATOR.'laravel.log'));
        $schedule->command('mou:not-response-task-type-two')->dailyAt('17:00')->appendOutputTo(storage_path('logs'.DIRECTORY_SEPARATOR.'laravel.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
