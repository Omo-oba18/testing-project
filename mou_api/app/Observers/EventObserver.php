<?php

namespace App\Observers;

use App\Event;
use App\Events\EventDelete;

class EventObserver
{
    /**
     * Handle the post "deleting" event.
     *
     * @param  Event  $post
     * @return void
     */
    public function deleting(Event $event)
    {
        \event(new EventDelete($event));
    }
}
