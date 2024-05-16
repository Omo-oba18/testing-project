<?php

namespace App\Console\Commands;

use App\Enums\RosterAction;
use App\Jobs\SendRosterJob;
use App\Roster;
use Illuminate\Console\Command;

class SendRosterStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mou:send-roster-start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send notify when roster start';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now()->format('Y-m-d H:i');
        Roster::query()->with(['creator', 'store', 'employee', 'employee.contact'])->where('status', config('constant.event.status.confirm'))->where('start_time', $now)->chunkById(500, function ($rosters) {
            foreach ($rosters as $roster) {
                SendRosterJob::dispatch($roster, RosterAction::START);
            }
        });

        return Command::SUCCESS;
    }
}
