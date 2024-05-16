<?php

namespace App\Console\Commands;

use App\Enums\EventAction;
use App\Event;
use App\Jobs\SendNotifyEventEmployeeJob;
use Illuminate\Console\Command;

class SendEventStartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mou:send-event-start-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notify when event start';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now()->format('Y-m-d H:i');
        Event::query()->with(['creator', 'contacts', 'contacts.userContact'])->where('type', config('constant.event.type.event'))->where('start_date', $now)->chunkById(500, function ($events) {
            foreach ($events as $event) {
                SendNotifyEventEmployeeJob::dispatch($event, EventAction::START, $event->creator, null);
            }
        });

        return Command::SUCCESS;
    }
}
