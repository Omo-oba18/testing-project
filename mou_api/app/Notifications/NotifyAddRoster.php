<?php

namespace App\Notifications;

use App\Enums\NotificationAction;
use App\Enums\NotifySendTo;
use App\Enums\UserType;
use App\Helpers\Util;
use App\Roster;
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

class NotifyAddRoster extends Notification implements ShouldQueue
{
    use Queueable;

    const ACTION = 'ADD_ROSTER';

    private $roster;

    private $title;

    private $body;

    private $lang;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Roster $roster, string $employee_name, $lang = null)
    {
        $this->roster = $roster;
        $this->title = __('notify.employee_action_roster_title', [], $lang ?? 'en');
        $this->body = __('notify.send_roster', ['company_name' => optional($roster->employee)?->company?->name, 'day' => now()->format('d-F'), 'hour' => date('d-F H:i', strtotime($roster->start_time)).' '.date('d-F H:i', strtotime($roster->end_time)), 'store_name' => optional($roster->store)->name], $lang ?? 'en');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', FcmChannel::class];
    }

    /**
     * Handle query param
     *
     * @return array
     */
    private function getQueryParams()
    {
        return [
            'action' => self::ACTION,
            'event_id' => $this->roster->id.'',
            'page' => 'event',
            // 'key' => '0',
            'notify_type' => 'SMS_MESSAGE',
        ];
    }

    public function toArray($notifiable)
    {

        return [
            'action' => self::ACTION,
            'avatar' => $this->roster?->creator?->company?->logo_url,
            'title' => $this->title,
            'body' => $this->body,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'user_type' => UserType::PERSONAL,
            'route_name' => NotificationAction::ToEventForYou(),
            'arguments' => '0',
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

        return Util::createDynamicLink(NotifySendTo::PERSONAL, "?$queryParams");
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
     * Send fcm notify to personal app
     *
     * @param  mixed  $notifiable
     * @return FcmMessage
     */
    public function toFcm($notifiable)
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
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('join_project_notify_android'))
                    ->setNotification(AndroidNotification::create()->setColor('#f7dc7a'))
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('join_project_notify_ios'))
            );
    }

    /**
     * Send sms to employee
     *
     * @param  mixed  $notifiable
     * @return TwilioSmsMessage
     */
    /* public function toTwilio($notifiable)
    {
        $newUrl = $this->getLink();

        return (new TwilioSmsMessage())
            ->content($this->body.': '.$newUrl);
    } */
}
