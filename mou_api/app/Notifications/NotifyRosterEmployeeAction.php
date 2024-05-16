<?php

namespace App\Notifications;

use App\Enums\NotificationAction;
use App\Enums\NotifySendTo;
use App\Enums\UserType;
use App\Helpers\Util;
use App\NotificationChannels\FcmBusinessChannel;
use App\Roster;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class NotifyRosterEmployeeAction extends Notification implements ShouldQueue
{
    use Queueable;

    const ACTION_ACCEPT = 'ROSTER_EMPLOYEE_ACCEPT';

    const ACTION_DECLINE = 'ROSTER_EMPLOYEE_DECLINE';

    private $roster;

    private $title;

    private $body;

    private $action;

    private $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Roster $roster, string $employee_name, User $user, $lang = null)
    {
        $this->roster = $roster;
        $this->title = __('notify.add_employee_to_roster_title', [], $lang ?? 'en');
        $this->user = $user;
        if ($roster->status == config('constant.event.status.confirm')) {
            $this->body = __('notify.employee_accept_roster_body', ['employee_name' => $employee_name, 'date' => now()->format('d-F'), 'start_hour' => date('H:i', strtotime($roster->start_time)), 'finish_hour' => date('H:i', strtotime($roster->end_time)), 'store_name' => $roster->store?->name], $lang ?? 'en');
            $this->action = self::ACTION_ACCEPT;
        } else {
            $this->body = __('notify.employee_decline_roster_body', ['employee_name' => $employee_name, 'date' => now()->format('Y-m-d'), 'start_hour' => date('Y-m-d H:i:s', strtotime($roster->start_time)), 'finish_hour' => date('Y-m-d H:i:s', strtotime($roster->end_time)), 'store_name' => $roster->store?->name], $lang ?? 'en');
            $this->action = self::ACTION_DECLINE;
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', FcmBusinessChannel::class];
    }

    /**
     * Handle query param
     *
     * @return array
     */
    private function getQueryParams()
    {
        return [
            'action' => $this->action,
            'event_id' => $this->roster->id.'',
            'page' => 'roster',
            // 'key' => '1',
            'notify_type' => 'SMS_MESSAGE',
            'date' => $this->roster->start_time.'',
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'action' => $this->action,
            'title' => $this->title,
            'body' => $this->body,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'user_type' => UserType::BUSINESS,
            'avatar' => $this->user->avatar_url,
            'route_name' => NotificationAction::ToCalenderBusiness(),
            'arguments' => date('Y-m-d', strtotime($this->roster->start_time)),
        ];
    }

    /**
     * Handle create dynamic link
     *
     * @return string
     */
    public function getLink()
    {
        $queryParams = http_build_query($this->getQueryParams());

        return Util::createDynamicLink(NotifySendTo::BUSINESS, "?$queryParams");
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    /* public function toMail($notifiable)
    {
        $user = $notifiable;
        $content = $this->body;
        $link = $this->getLink();

        return (new MailMessage)->subject(config('app.name').' - '.$this->title)->markdown('mail.event.new-employee', compact('user', 'content', 'link'));
    } */

    /**
     * Send fcm notify to business app
     *
     * @param  mixed  $notifiable
     * @return FcmMessage
     */
    public function toFcmBusiness($notifiable)
    {
        $data = $this->toArray($notifiable);

        return FcmMessage::create()
            ->setData($data)
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($this->title)
                ->setBody($this->body))
            //                ->setImage('http://example.com/url-to-image-here.png'));
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel(strtolower($data['action']).'_android'))
                    ->setNotification(AndroidNotification::create()->setColor('#f7dc7a')->setClickAction('FLUTTER_NOTIFICATION_CLICK'))
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel(strtolower($data['action']).'_ios'))
            );
    }
}
