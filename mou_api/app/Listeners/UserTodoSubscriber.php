<?php

namespace App\Listeners;

use App\Events\TodoCreated;
use App\Notifications\NotifyTodo;
use App\Todo;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserTodoSubscriber implements ShouldQueue
{
    /**
     * Get user list will receive notification.
     *
     * @return array|void
     */
    private function getUsersReceiveNotifyByEvent(Todo $todo)
    {
        // Get the contacts involved in the event
        $contacts = $todo->contacts()->with('userContact', 'userContact.setting')->get();
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
    public function handleCreate(TodoCreated $todoCreated)
    {
        $todo = $todoCreated->todo;

        $users = $this->getUsersReceiveNotifyByEvent($todo);
        if (count($users) > 0) {
            foreach ($users as $user) {

                $lang = optional($user->setting)->language_code;
                $user->notify(new NotifyTodo($todo, $todo->creator, 'ADD', $lang));
            }
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            TodoCreated::class,
            'App\Listeners\UserTodoSubscriber@handleCreate'
        );
    }
}
