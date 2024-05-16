<?php

namespace App\Console\Commands;

use App\Enums\EventAction;
use App\Event;
use App\Jobs\SendNotifyEventEmployeeJob;
use Illuminate\Console\Command;

class SendEventSetAlarmCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mou:send-event-set-alarm-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When the alarm is set to send a push notification only';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now()->format('Y-m-d H:i');
        Event::query()->with(['creator', 'contacts', 'contacts.userContact'])->where('type', config('constant.event.type.event'))->where('start_date', '>', $now)->whereNotNull('alarm')->chunkById(500, function ($events) {
            foreach ($events as $event) {
                $alarms = explode(Event::CHARACTER_SPECIAL, $event->alarm);
                foreach ($alarms as $value) {
                    switch ($value) {
                        case config('constant.event.alarm.5m'):
                            if ($event->start_date == now()->subMinutes(5)->format('Y-m-d H:i:00')) {
                                SendNotifyEventEmployeeJob::dispatch($event, EventAction::SET_ALARM, $event->creator, config('constant.event.alarm.5m'));
                            }
                            break;
                        case config('constant.event.alarm.10m'):
                            if ($event->start_date == now()->subMinutes(10)->format('Y-m-d H:i:00')) {
                                SendNotifyEventEmployeeJob::dispatch($event, EventAction::SET_ALARM, $event->creator, config('constant.event.alarm.10m'));
                            }
                            break;
                        case config('constant.event.alarm.30m'):
                            if ($event->start_date == now()->subMinutes(30)->format('Y-m-d H:i:00')) {
                                SendNotifyEventEmployeeJob::dispatch($event, EventAction::SET_ALARM, $event->creator, config('constant.event.alarm.30m'));
                            }
                            break;
                        case config('constant.event.alarm.1h'):
                            if ($event->start_date == now()->subHour()->format('Y-m-d H:i:00')) {
                                SendNotifyEventEmployeeJob::dispatch($event, EventAction::SET_ALARM, $event->creator, config('constant.event.alarm.1h'));
                            }
                            break;
                        case config('constant.event.alarm.1d'):
                            if ($event->start_date == now()->subDay()->format('Y-m-d H:i:00')) {
                                SendNotifyEventEmployeeJob::dispatch($event, EventAction::SET_ALARM, $event->creator, config('constant.event.alarm.1d'));
                            }
                            break;
                        case config('constant.event.alarm.1w'):
                            if ($event->start_date == now()->subWeek()->format('Y-m-d H:i:00')) {
                                SendNotifyEventEmployeeJob::dispatch($event, EventAction::SET_ALARM, $event->creator, config('constant.event.alarm.1w'));
                            }
                            break;
                        default:
                            // code...
                            break;
                    }
                }
            }
        });

        return Command::SUCCESS;
    }
}
