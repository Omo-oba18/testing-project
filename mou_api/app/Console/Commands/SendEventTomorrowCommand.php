<?php

namespace App\Console\Commands;

use App\Enums\EventAction;
use App\Event;
use App\Jobs\SendNotifyEventActionJob;
use Illuminate\Console\Command;

class SendEventTomorrowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mou:send-event-tomorrow-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When there are 24 hours before the event happens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = now()->addDay()->format('Y-m-d H:i');
        Event::query()->with(['creator', 'contacts', 'contacts.userContact'])->where('start_date', $tomorrow)->where('type', config('constant.event.type.event'))->chunkById(500, function ($events) {
            foreach ($events as $event) {
                SendNotifyEventActionJob::dispatch($event, EventAction::SEND_BEFORE_DAY_EVENT_START);
            }
        });

        return Command::SUCCESS;
    }
}
