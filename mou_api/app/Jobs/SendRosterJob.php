<?php

namespace App\Jobs;

use App\Enums\RosterAction;
use App\Notifications\NotifyRosterAction;
use App\Roster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRosterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Roster $roster, protected string $action)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $contact = $this->roster->employee?->contact;
        if ($this->roster->status == config('constant.event.status.waiting') && $this->action == RosterAction::NOT_RESPONSE) {
            $this->roster->update([
                'status' => config('constant.event.status.deny'),
            ]);
            $lang = optional($this->roster->creator?->setting)->language_code;
            $this->roster->creator->notify(new NotifyRosterAction($this->roster, RosterAction::SEND_CREATOR, $lang));
        }
        if ($contact && $contact->userContact) {
            $lang = optional($contact->userContact?->setting)->language_code;
            $contact->userContact->notify(new NotifyRosterAction($this->roster, $this->action, $lang));
        }
    }
}
