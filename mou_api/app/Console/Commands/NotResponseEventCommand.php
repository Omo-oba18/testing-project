<?php

namespace App\Console\Commands;

use App\Enums\EventAction;
use App\Event;
use App\Jobs\SendNotifyEventEmployeeJob;
use Illuminate\Console\Command;

class NotResponseEventCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mou:not-response-event-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send notify when not response event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now()->subMinute()->format('Y-m-d H:i');
        Event::query()->with(['creator', 'contacts', 'contacts.userContact'])->where('type', config('constant.event.type.event'))->where('start_date', '<=', $now)->chunkById(500, function ($events) {
            foreach ($events as $event) {
                SendNotifyEventEmployeeJob::dispatch($event, EventAction::NOT_RESPONSE, $event->creator, null);
            }
        });

        return Command::SUCCESS;
    }
}
