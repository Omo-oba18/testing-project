<?php

namespace App\Jobs;

use App\Enums\EventAction;
use App\Event;
use App\Notifications\NotifyEvent;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotifyEventEmployeeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Event $event, protected ?string $action, protected User $user, protected ?string $alarm)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $queryContact = $this->event->contacts()->wherePivot('status', '<>', config('constant.event.status.deny'));
        switch ($this->action) {
            case EventAction::NOT_RESPONSE:
                $queryContact = $queryContact->wherePivot('status', config('constant.event.status.waiting'));
                break;
            default:
                $queryContact;
                break;
        }
        if ($this->action == EventAction::USER_CANCEL || $this->action == EventAction::START) {
            $lang = optional($this->event?->creator?->setting)->language_code;
            $this->event?->creator->notify(new NotifyEvent($this->event, $this->user, $this->action, $lang));
        } else {
            $queryContact->chunkById(500, function ($contacts) {
                foreach ($contacts as $value) {
                    if ($value->userContact) {
                        $lang = optional($value->userContact?->setting)->language_code;
                        $value->userContact->notify(new NotifyEvent($this->event, $this->user, $this->action, $this->alarm, $lang));
                    }
                    if ($value->pivot->status == config('constant.event.status.waiting') && empty($this->alarm)) {
                        $value->pivot->update([
                            'status' => config('constant.event.status.deny'),
                        ]);
                    }
                }
            });
        }
    }
}
