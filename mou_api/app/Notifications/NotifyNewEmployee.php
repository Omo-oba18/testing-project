<?php

namespace App\Notifications;

use App\CompanyEmployee;
use App\Enums\NotificationAction;
use App\Enums\NotifySendTo;
use App\Enums\UserType;
use App\Helpers\Util;
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
use NotificationChannels\Twilio\TwilioSmsMessage;

class NotifyNewEmployee extends Notification implements ShouldQueue
{
    use Queueable;

    const ACTION = 'INVITATION_JOIN_COMPANY';

    private $content;

    private $employee;

    private $lang;

    /**
     * Create a new notification instance.
     */
    public function __construct(CompanyEmployee $employee, $lang = null)
    {
        $this->employee = $employee;
        $this->content = __('notify.add_to_company', ['company_name' => optional($this->employee->company)->name, 'job_title' => $employee->role_name], $lang ?? 'en');
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
        return ['database', FcmChannel::class];
    }

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
        $content = $this->content;
        $link = $this->getLink();

        return (new MailMessage)->subject(config('app.name').' - '.__('notify.add_employee_to_company_title'))->markdown('mail.event.new-employee', compact('user', 'content', 'link'));
    } */

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'action' => self::ACTION,
            'title' => __('notify.add_employee_to_company_title', [], $this->lang ?? 'en'),
            'body' => $this->content,
            'user_type' => UserType::PERSONAL,
            'avatar' => $this->employee?->company?->logo_url,
            'route_name' => NotificationAction::toSettingCorp(),
            'arguments' => '/corp',
        ];
    }

    private function getQueryParams()
    {
        return [
            'action' => self::ACTION,
            'company_id' => $this->employee->company_id.'',
            'creator_id' => $this->employee->creator_id.'',
            'page' => 'corp',
            'notify_type' => 'SMS_MESSAGE',
        ];
    }

    public function toFcm($notifiable)
    {
        $data = $this->toArray($notifiable);

        return FcmMessage::create()
            ->setData($data)
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle(__('notify.add_employee_to_company_title'))
                ->setBody($this->content))
            //                ->setImage('http://example.com/url-to-image-here.png'));
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('invite_join_company_notify_android'))
                    ->setNotification(AndroidNotification::create()->setColor('#f7dc7a')->setClickAction('FLUTTER_NOTIFICATION_CLICK'))
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('invite_join_company_notify_ios'))
            );
    }

    // public function toTwilio($notifiable)
    // {
    //     $newUrl = $this->getLink();

    //     return (new TwilioSmsMessage())
    //         ->content($this->content . ": " . $newUrl);
    // }
}
