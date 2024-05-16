<?php

namespace App\Notifications;

use App\Contact;
use App\Enums\EventAction;
use App\Enums\NotificationAction;
use App\Enums\UserType;
use App\Event;
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
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class NotifyEvent extends Notification implements ShouldQueue
{
    use Queueable;

    private $event;

    private $sender;

    private $action;

    private $alarm;

    private $lang;

    /**
     * Create a new notification instance.
     */
    public function __construct(Event $event, User $sender, string $action, $alarm = null, $lang = null)
    {
        $this->event = $event;
        $this->sender = $sender;
        $this->action = $action;
        $this->alarm = $alarm;
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
     * Return creator's name of event
     *
     * @return mixed
     */
    private function getSenderName($userReceive, $userSender)
    {
        $creatorName = $userSender->name;
        // Get name creator of event in contact table - of user receive notify
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
        $event = $this->event;
        if (! $event) {
            return;
        }

        $senderName = $this->getSenderName($notifiable, $this->sender);

        $title = '';
        $content = '';
        $avatar = $event?->creator?->avatar_url;
        $navigation = null;
        $arguments = null;
        switch ($this->action) {
            case 'ADD':
                $title = 'New Event: '.$event->title;
                $content = __('notify.create_event', ['creator_name' => trim($senderName), 'event_title' => $event->title, 'date' => date('d-F', strtotime($event->start_date)), 'hour' => date('H:i', strtotime($event->start_date)), 'place' => $event->place ? __('common.at').' '.$event->place : null], $this->lang ?? 'en');
                $navigation = NotificationAction::ToEventForYou();
                $arguments = '0';
                break;
            case 'EDIT':
                $title = 'Update Event: '.$event->title;
                $content = __('notify.edit_event', ['creator_name' => trim($senderName), 'event_title' => $event->title, 'date' => date('d-F', strtotime($event->start_date)), 'hour' => date('H:i', strtotime($event->start_date)), 'place' => $event->place ? __('common.at').' '.$event->place : null], $this->lang ?? 'en');
                $navigation = NotificationAction::ToCalenderPersonal();
                $arguments = date('Y-m-d', strtotime($event->start_date));
                break;
            case 'DELETE':
                $title = 'Delete Event: '.$event->title;
                $content = __('notify.delete_event', ['creator_name' => trim($senderName), 'event_title' => $event->title, 'date' => date('d-F', strtotime($event->start_date)), 'hour' => date('H:i', strtotime($event->start_date)), 'place' => $event->place ? __('common.at').' '.$event->place : null], $this->lang ?? 'en');
                $navigation = NotificationAction::ToCalenderPersonal();
                $arguments = date('Y-m-d', strtotime($event->start_date));
                break;
            case 'INTERACT_'.config('constant.event.status.confirm'):
                $title = 'Confirm Event: '.$event->title;
                $content = __('notify.accept_event', ['user' => trim($senderName), 'event_title' => $event->title, 'date' => date('d-F', strtotime($event->start_date)), 'hour' => date('H:i', strtotime($event->start_date)), 'place' => $event->place ? __('common.at').' '.$event->place : null], $this->lang ?? 'en');
                $avatar = $this->sender?->avatar_url;
                $navigation = NotificationAction::ToEventWaiting();
                $arguments = '1';
                break;
            case 'INTERACT_'.config('constant.event.status.deny'):
                $title = 'Deny Event: '.$event->title;
                $content = __('notify.deny_event', ['user' => trim($senderName), 'event_title' => $event->title, 'date' => date('d-F', strtotime($event->start_date)), 'hour' => date('H:i', strtotime($event->start_date)), 'place' => $event->place ? __('common.at').' '.$event->place : null], $this->lang ?? 'en');
                $avatar = $this->sender?->avatar_url;
                $navigation = NotificationAction::ToCalenderPersonal();
                $arguments = date('Y-m-d', strtotime($event->start_date));
                break;
            case EventAction::START:
                $title = __('notify.notify_title', [], $this->lang ?? 'en');
                $content = __('notify.event_start', ['event_title' => $event->title], $this->lang ?? 'en');
                $navigation = NotificationAction::ToCalenderPersonal();
                $arguments = date('Y-m-d', strtotime($event->start_date));
                break;
            case EventAction::USER_CANCEL:
                $title = __('notify.notify_title');
                $content = __('notify.user_cancel_invitation', ['user' => trim($this->sender->name), 'event_title' => $event->title, 'date' => date('d-F', strtotime($event->start_date)), 'hour' => date('H:i', strtotime($event->start_date)), 'place' => $event->place ? __('common.at').' '.$event->place : null]);
                $avatar = $this->sender?->avatar_url;
                $navigation = NotificationAction::ToCalenderPersonal();
                $arguments = date('Y-m-d', strtotime($event->start_date));
                break;
            case EventAction::SET_ALARM:
                $title = __('notify.notify_title', [], $this->lang ?? 'en');
                $content = __('notify.alarm_event', ['event_title' => $event->title, 'alarm' => $this->alarm, 'place' => $event->place ? __('common.at').' '.$event->place : null], $this->lang ?? 'en');
                $navigation = NotificationAction::ToCalenderPersonal();
                $arguments = date('Y-m-d', strtotime($event->start_date));
                break;
            case EventAction::SEND_BEFORE_DAY_EVENT_START:
                $title = __('notify.notify_title', [], $this->lang ?? 'en');
                $content = __('notify.create_24h_event', ['creator_name' => trim($senderName), 'event_title' => $event->title, 'hour' => date('H:i', strtotime($event->start_date)), 'place' => $event->place ? __('common.at').' '.$event->place : null], $this->lang ?? 'en');
                $navigation = NotificationAction::ToCalenderPersonal();
                $arguments = date('Y-m-d', strtotime($event->start_date));
                break;
            case EventAction::NOT_RESPONSE:
                $title = __('notify.notify_title', [], $this->lang ?? 'en');
                $content = __('notify.not_response_event', ['event_title' => $this->event->title], $this->lang ?? 'en');
                $navigation = NotificationAction::ToEventWaiting();
                $arguments = '1';
                break;
            default:
        }

        return [
            'title' => $title,
            'body' => $content,
            'action' => 'EVENT_'.$this->action,
            'user_type' => UserType::PERSONAL,
            'avatar' => $avatar,
            'route_name' => $navigation,
            'arguments' => $arguments,
        ];
    }

    private function queryParams()
    {
        $event = $this->event;
        if (! $event) {
            return null;
        }

        return [
            'id' => $event->id.'',
            'action' => 'EVENT_'.$this->action,
            'created_at' => $event->created_at.'',
            'page' => 'event',
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
        $content = $dataContent['body'];

        if (empty($dataContent['title'])) {
            return;
        }

        return FcmMessage::create()
            ->setData($params)
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle($dataContent['title'])
                    ->setBody($content)
            )

            //                ->setImage('http://example.com/url-to-image-here.png'));
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('event_notify_android'))
                    ->setNotification(AndroidNotification::create()->setColor('#f7dc7a')->setClickAction('FLUTTER_NOTIFICATION_CLICK'))
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('event_notify_ios'))
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
            ->markdown('mail.event.notify', compact('content', 'notifiable', 'link'));
    } */

    /**
     * @return mixed
     */
    // public function toTwilio($notifiable)
    // {
    //     $params = $this->queryParams();
    //     if(!$params) return;

    //     $dataContent = $this->getDataNotification($notifiable);

    //     $queryParams = http_build_query($params);

    //     $newUrl = Util::createDynamicLink(0, "?$queryParams");

    //     return (new TwilioSmsMessage())
    //         ->content($dataContent['content'].": ".$newUrl);
    // }
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
