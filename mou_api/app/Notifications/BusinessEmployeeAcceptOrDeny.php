<?php

namespace App\Notifications;

use App\CompanyEmployee;
use App\Enums\NotificationAction;
use App\Enums\NotifySendTo;
use App\Enums\UserType;
use App\Helpers\Util;
use App\NotificationChannels\FcmBusinessChannel;
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
use NotificationChannels\Twilio\TwilioSmsMessage;

class BusinessEmployeeAcceptOrDeny extends Notification implements ShouldQueue
{
    use Queueable;

    const ACTION = 'JOINT_COMPANY_EMPLOYEE_';

    private $employee;

    private $user;

    private $lang;

    /**
     * Create a new notification instance.
     */
    public function __construct(CompanyEmployee $employee, User $user, $lang)
    {
        $this->employee = $employee;
        $this->user = $user;
        $this->lang = $lang;
    }

    /**
     * Prepare data
     *
     * @return array
     */
    private function prepareDataNotification()
    {
        if ($this->employee->employee_confirm == config('constant.event.status.confirm')) {
            return [
                'user_type' => UserType::BUSINESS,
                'action' => self::ACTION.'ACCEPT',
                'avatar' => User::getPhotoAsset($this->employee?->getEmployeeAvatar()),
                'title' => __('notify.employee_accept_join_to_company_title', [], $this->lang ?? 'en'),
                'body' => __('notify.employee_accept_join_to_company_body', ['employee_name' => $this->employee->name, 'role' => $this->employee->role_name], $this->lang ?? 'en'),
                'route_name' => NotificationAction::toTeam(),
                'arguments' => null,
            ];
        }

        return [
            'user_type' => UserType::BUSINESS,
            'action' => self::ACTION.'DENY',
            'avatar' => User::getPhotoAsset($this->employee?->getEmployeeAvatar()),
            'title' => __('notify.employee_deny_join_to_company_title', [], $this->lang ?? 'en'),
            'body' => __('notify.employee_deny_join_to_company_body', ['employee_name' => $this->employee->name, 'role' => $this->employee->role_name], $this->lang ?? 'en'),
            'route_name' => NotificationAction::toTeam(),
            'arguments' => null,
        ];
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

    private function getLink()
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
        $data = $this->toArray($notifiable);
        $user = $notifiable;
        $content = $data['body'];
        $link = $this->getLink();

        return (new MailMessage)->subject(config('app.name').' - '.$data['title'])->markdown('mail.event.new-employee', compact('user', 'content', 'link'));
    } */

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->prepareDataNotification();
    }

    private function getQueryParams()
    {
        $data = $this->prepareDataNotification();

        return [
            'action' => $data['action'],
            'company_id' => $this->employee->company_id.'',
            'creator_id' => $this->employee->creator_id.'',
            'page' => 'employee',
            'notify_type' => 'SMS_MESSAGE',
            'route_name' => NotificationAction::toTeam(),
            'arguments' => null,
        ];
    }

    public function toFcmBusiness($notifiable)
    {
        $data = $this->prepareDataNotification();
        $arr = $this->getQueryParams();

        return FcmMessage::create()
            ->setData($arr)
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($data['title'])
                ->setBody($data['body']))
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

    // public function toTwilio($notifiable)
    // {
    //     $newUrl = $this->getLink();
    //     $data = $this->prepareDataNotification();
    //     return (new TwilioSmsMessage())
    //         ->content($data['body'] . ": " . $newUrl);
    // }
}
