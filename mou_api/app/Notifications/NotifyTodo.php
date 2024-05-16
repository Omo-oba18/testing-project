<?php

namespace App\Notifications;

use App\Contact;
use App\Enums\NotificationAction;
use App\Enums\NotifySendTo;
use App\Enums\TodoType;
use App\Enums\UserType;
use App\Helpers\Util;
use App\Todo;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class NotifyTodo extends Notification implements ShouldQueue
{
    use Queueable;

    private $todo;

    private $sender;

    private $action;

    private $lang;

    /**
     * Create a new notification instance.
     */
    public function __construct(Todo $todo, User $sender, string $action, $lang = null)
    {
        $this->todo = $todo;
        $this->sender = $sender;
        $this->action = $action;
        $this->lang = $lang;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', FcmChannel::class]; //FcmChannel::class, TwilioChannel::class, 'mail', 'database'
    }

    /**
     * Return creator's name of todo
     *
     * @return mixed
     */
    private function getSenderName($userReceive, $userSender)
    {
        $creatorName = $userSender->name;
        // Get name creator of todo in contact table - of user receive notify
        $creatorNameOfContact = Contact::where('user_id', $userReceive->id)
            ->where('user_contact_id', $userSender->id)
            ->whereNotNull('name')->first();
        if ($creatorNameOfContact) {
            $creatorName = $creatorNameOfContact->name;
        }

        return $creatorName;
    }

    public function getDataNotification($notifiable)
    {
        $todo = $this->todo;
        if (! $todo) {
            return;
        }

        $senderName = $this->getSenderName($notifiable, $this->sender);

        $title = '';
        $content = '';
        switch ($this->action) {
            case 'ADD':
                $title = 'New todo: '.$todo->title;
                $content = __('notify.create_todo', ['creator_name' => $this->sender->name, 'todo_title' => $this->todo->title], $this->lang ?? 'en');
                break;

            default:
        }

        return [
            'title' => $title,
            'body' => $content,
            'user_type' => UserType::PERSONAL,
            'action' => 'TODO_'.$this->action,
            'avatar' => $this->todo->creator->avatar_url,
            'route_name' => $this->todo->type == TodoType::SINGLE ? NotificationAction::toToDoSingle() : NotificationAction::toToDoGroup(),
            'arguments' => $this->todo->type == TodoType::SINGLE ? null : strval($this->todo->id),
        ];
    }

    private function queryParams()
    {
        $todo = $this->todo;
        if (! $todo) {
            return null;
        }

        return [
            'id' => $todo->id.'',
            'action' => 'todo_'.$this->action,
            'created_at' => $todo->created_at.'',
            'page' => 'todo',
            'key' => '0',
        ];
    }

    public function toFcm($notifiable)
    {
        $params = $this->queryParams();
        if (! $params) {
            return;
        }

        $dataContent = $this->getDataNotification($notifiable);
        $params['route_name'] = $dataContent['route_name'];
        $params['arguments'] = $dataContent['arguments'];
        if (empty($dataContent['title'])) {
            return;
        }

        return FcmMessage::create()
            ->setData($params)
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle($dataContent['title'])
                    ->setBody($dataContent['body'])
            )

            //                ->setImage('http://example.com/url-to-image-here.png'));
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('todo_notify_android'))
                    ->setNotification(AndroidNotification::create()->setColor('#f7dc7a')->setClickAction('FLUTTER_NOTIFICATION_CLICK'))
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('todo_notify_ios'))
            );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    /* public function toMail($notifiable)
    {
        $params = $this->queryParams();
        if (! $params) {
            return;
        }

        $dataContent = $this->getDataNotification($notifiable);
        $content = $dataContent['body'];

        $queryParams = http_build_query($params);
        $link = Util::createDynamicLink(NotifySendTo::PERSONAL, "?$queryParams");

        return (new MailMessage)
            ->subject($dataContent['title'])
            ->markdown('mail.todo.notify', compact('content', 'notifiable', 'link'));
    } */

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->getDataNotification($notifiable);
    }
}
