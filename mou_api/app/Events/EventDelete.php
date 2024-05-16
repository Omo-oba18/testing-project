<?php

namespace App\Events;

use App\Event;
use Illuminate\Queue\SerializesModels;

class EventDelete
{
    use SerializesModels;

    public $event;

    /**
     * Create a new event instance.
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
