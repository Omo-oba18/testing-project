<?php

namespace App\Jobs;

use App\Enums\TaskAndProjectAction;
use App\Event;
use App\Notifications\NotifyMyTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTaskAndProjectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Event $event, protected string $action)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->event->contacts()->wherePivot('status', config('constant.event.status.waiting'))->chunkById(500, function ($contacts) {
            foreach ($contacts as $value) {
                if ($this->action == TaskAndProjectAction::TASK_NOT_RESPONSE || $this->action == TaskAndProjectAction::PROJECT_NOT_RESPONSE) {
                    $value->pivot->update([
                        'status' => config('constant.event.status.deny'),
                    ]);
                    $lang = optional($this->event->creator?->setting)->language_code;
                    $this->event->creator->notify(new NotifyMyTask($this->event, $this->event->type, TaskAndProjectAction::SEND_CREATOR, $value->userContact ? $value?->userContact->name : $value->name, $lang));
                }
                if ($value->userContact) {
                    $lang = optional($value->userContact?->setting)->language_code;
                    $value->userContact->notify(new NotifyMyTask($this->event, $this->event->type, $this->action, $lang));
                }
            }
        });
    }
}
