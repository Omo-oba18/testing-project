<?php

namespace App\Events;

use App\Event;
use App\User;
use Illuminate\Queue\SerializesModels;

class EventInteract
{
    use SerializesModels;

    public $event;

    public $active;

    public $sender;

    public $contactId;

    /**
     * Create a new event instance.
     */
    public function __construct(Event $event, User $sender, string $active, int $contactId)
    {
        $this->event = $event;
        $this->sender = $sender;
        $this->active = $active;
        $this->contactId = $contactId;
    }
}
