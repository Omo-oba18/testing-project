<?php

namespace App\Console\Commands;

use App\Enums\TaskAndProjectAction;
use App\Event;
use App\Jobs\SendTaskAndProjectJob;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class NotResponseTaskCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mou:not-response-task-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send notify when not response task';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now()->format('Y-m-d H:i');
        Event::query()->with(['contacts', 'contacts.userContact'])->where(function (Builder $query) {
            $query->where('type', config('constant.event.type.task'))->orWhere('type', config('constant.event.type.project_task'));
        })->where('start_date', '<=', $now)->whereRaw('TIMESTAMPDIFF(HOUR, created_at, start_date) < 24')->chunkById(500, function ($events) {
            foreach ($events as $event) {
                SendTaskAndProjectJob::dispatch($event, $event->type == config('constant.event.type.task') ? TaskAndProjectAction::TASK_NOT_RESPONSE : TaskAndProjectAction::PROJECT_NOT_RESPONSE);
            }
        });

        return Command::SUCCESS;
    }
}
