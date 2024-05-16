<?php

namespace App\Listeners;

use App\Event;
use App\Events\EventCreate;
use App\Events\EventDelete;
use App\Events\EventInteract;
use App\Events\EventUpdate;
use App\Notifications\NotifyEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserEventSubscriber implements ShouldQueue
{
    /**
     * Check event Busy mode
     *
     * @return bool
     */
    private function isEventBusyMode(Event $event)
    {
        return ! $event || $event->busy_mode;
    }

    /**
     * Get user list will receive notification.
     *
     * @return array|void
     */
    private function getUsersReceiveNotifyByEvent(Event $event)
    {
        if ($this->isEventBusyMode($event)) {
            return [];
        } //event busy mode
        // Get the contacts involved in the event
        $contacts = $event->contactNoDenies()->with('userContact', 'userContact.setting')->get();
        if (! $contacts) {
            return;
        }
        // Get users of contacts
        $users = [];
        foreach ($contacts as $contact) {
            if (! $contact->userContact) {
                continue;
            } //TODO: need send sms for people contact but not have account
            // user setting: busy_mod
            if (! empty($contact->userContact->setting) && $contact->userContact->setting->busy_mode) {
                continue;
            }
            $users[] = $contact->userContact;
        }

        return $users;
    }

    /**
     * Handle create event.
     */
    public function handleEventCreate(EventCreate $eventCreate)
    {
        $event = $eventCreate->event;
        $users = $this->getUsersReceiveNotifyByEvent($event);
        if (count($users) > 0) {
            foreach ($users as $user) {
                $lang = optional($user->setting)->language_code;
                $user->notify(new NotifyEvent($event, $event->creator, 'ADD', null, $lang));
            }
        }
    }

    /**
     * Handle edit event.
     */
    public function handleEventUpdate(EventUpdate $eventUpdate)
    {
        $event = $eventUpdate->event;
        $users = $this->getUsersReceiveNotifyByEvent($event);
        if (count($users) > 0) {
            foreach ($users as $user) {
                $lang = optional($user->setting)->language_code;
                $user->notify(new NotifyEvent($event, $event->creator, 'EDIT', null, $lang));
            }
        }
    }

    /**
     * Handle edit delete.
     */
    public function handleEventDelete(EventDelete $eventDelete)
    {
        $event = $eventDelete->event;
        $users = $this->getUsersReceiveNotifyByEvent($event);
        if (count($users) > 0) {
            foreach ($users as $user) {
                $lang = optional($user->setting)->language_code;
                $user->notify(new NotifyEvent($event, $event->creator, 'DELETE', null, $lang));
            }
        }
    }

    /**
     * Handle interact event: accept, deny or leave
     */
    public function handleEventInteract(EventInteract $eventInteract)
    {
        $active = $eventInteract->active;
        $sender = $eventInteract->sender;
        $event = $eventInteract->event;
        $contactId = $eventInteract->contactId;
        if (! in_array($active, config('constant.event.status')) || ! $sender) {
            return;
        }
        if ($this->isEventBusyMode($event)) {
            return;
        } //event busy mode
        $lang = optional($event->creator?->setting)->language_code;
        $event->creator->notify(new NotifyEvent($event, $sender, 'INTERACT_'.$active, null, $lang));
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            EventCreate::class,
            'App\Listeners\UserEventSubscriber@handleEventCreate'
        );

        $events->listen(
            EventUpdate::class,
            'App\Listeners\UserEventSubscriber@handleEventUpdate'
        );

        $events->listen(
            EventDelete::class,
            'App\Listeners\UserEventSubscriber@handleEventDelete'
        );

        $events->listen(
            EventInteract::class,
            'App\Listeners\UserEventSubscriber@handleEventInteract'
        );
    }
}
