<?php

namespace App\Jobs;

use App\Event;
use App\Notifications\NotifyEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotifyEventActionJob implements ShouldQueue
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
                if ($value->userContact) {
                    $lang = optional($value->userContact?->setting)->language_code;
                    $value->userContact->notify(new NotifyEvent($this->event, $this->event?->creator, $this->action, $lang));
                }
            }
        });
    }
}
