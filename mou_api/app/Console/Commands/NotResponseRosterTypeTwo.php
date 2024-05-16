<?php

namespace App\Console\Commands;

use App\Enums\RosterAction;
use App\Jobs\SendRosterJob;
use App\Roster;
use Illuminate\Console\Command;

class NotResponseRosterTypeTwo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mou:not-response-roster-type-two';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send notify when not response roster type two';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now()->addDay()->format('Y-m-d H:i');

        Roster::query()->with(['creator', 'store', 'employee', 'employee.contact'])->where('status', config('constant.event.status.waiting'))->where('start_time', '<=', $now)->whereRaw('TIMESTAMPDIFF(HOUR, created_at, start_time) >= 24')->chunkById(500, function ($rosters) {
            foreach ($rosters as $roster) {
                SendRosterJob::dispatch($roster, RosterAction::NOT_RESPONSE);
            }
        });

        return Command::SUCCESS;
    }
}
