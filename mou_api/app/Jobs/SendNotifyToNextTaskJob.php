<?php

namespace App\Jobs;

use App\Event;
use App\Notifications\NotifyDoneTask;
use App\Project;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotifyToNextTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Event $event, protected Project $project, protected User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $nextTask = $this->project->tasks()->with('contacts')->where('start_date', '>', $this->event->start_date)->orderBy('start_date')->first();
        if ($nextTask) {
            $nextTask->contacts()->wherePivot('status', config('constant.event.status.confirm'))->chunkById(500, function ($contacts) use ($nextTask) {
                foreach ($contacts as $value) {
                    if ($value->userContact) {
                        $lang = optional($value->userContact?->setting)->language_code;
                        $value->userContact->notify(new NotifyDoneTask($this->user, $nextTask, true, 'next_task', $lang));
                    }
                }
            });
        }

    }
}
