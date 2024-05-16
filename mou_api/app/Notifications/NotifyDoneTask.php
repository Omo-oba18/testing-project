<?php

namespace App\Notifications;

use App\Enums\NotificationAction;
use App\Enums\NotifySendTo;
use App\Enums\UserType;
use App\Event;
use App\Helpers\Util;
use App\NotificationChannels\FcmBusinessChannel;
use App\User;
use App\UserDeviceFcm;
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

class NotifyDoneTask extends Notification implements ShouldQueue
{
    use Queueable;

    const TYPE_ACTION = 'EMPLOYEE_DONE_';

    private $employee;

    private $event;

    private $to;

    private $typeTask;

    private $lang;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $employee, Event $event, $to = null, $typeTask = null, $lang = null)
    {
        $this->employee = $employee;
        $this->event = $event;
        $this->to = $to;
        $this->typeTask = $typeTask;
        $this->lang = $lang;
    }

    /**
     * Prepare data
     *
     * @return array
     */
    private function getDataNotify()
    {
        if ($this->event->type == config('constant.event.type.task')) {
            return [
                'action' => self::TYPE_ACTION.'TASK',
                'title' => __('notify.employee_done_task_or_project_task_title', ['project_name' => ''], $this->lang ?? 'en'),
                'body' => __('notify.employee_done_task', ['task_name' => $this->event->title, 'employee_name' => $this->employee->name], $this->lang ?? 'en'),
                'user_type' => $this->to ? UserType::PERSONAL : UserType::BUSINESS,
                'avatar' => $this->to ? $this->employee?->company?->logo_url : $this->employee?->avatar_url,
                'route_name' => $this->to ? NotificationAction::ToCalenderPersonal() : NotificationAction::ToCalenderBusiness(),
                'arguments' => date('Y-m-d', strtotime($this->event->start_date)),
            ];
        }
        $project_name = optional($this->event->project)->title ? 'Project '.optional($this->event->project)->title.':' : '';

        return [
            'action' => self::TYPE_ACTION.'PROJECT_TASK',
            'title' => __('notify.employee_done_task_or_project_task_title', ['project_name' => $project_name], $this->lang ?? 'en'),
            'body' => empty($this->typeTask) ? __('notify.user_mark_task_complete_project', ['user_name' => $this->employee->name, 'project_title' => optional($this->event->project)->title, 'task_title' => $this->event->title, 'leader' => optional($this->event->creator)->name]) : __('notify.mark_complete_previous_task_project', ['company_name' => $this->employee?->company?->name, 'project_title' => optional($this->event->project)->title, 'task_title' => $this->event->title, 'leader' => optional($this->event->creator)->name], $this->lang ?? 'en'),
            'user_type' => $this->to ? UserType::PERSONAL : UserType::BUSINESS,
            'avatar' => $this->to ? $this->employee?->company?->logo_url : $this->employee?->avatar_url,
            'route_name' => $this->to ? NotificationAction::ToCalenderPersonal() : NotificationAction::ToCalenderBusiness(),
            'arguments' => date('Y-m-d', strtotime($this->event->start_date)),
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
        if ($this->to) {
            return ['database', FcmChannel::class];
        }

        return ['database', FcmBusinessChannel::class];
    }

    private function getLink()
    {
        $isBusiness = NotifySendTo::BUSINESS;
        if ($this->to) {
            $isBusiness = NotifySendTo::PERSONAL;
        }
        $arr = http_build_query($this->getQueryParams());

        return Util::createDynamicLink($isBusiness, "?$arr");
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    /* public function toMail($notifiable)
    {
        $data = $this->getDataNotify();
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
        return $this->getDataNotify($notifiable);
    }

    // public function toDatabase($notifiable){
    //     $data = $this->getDataNotify();

    //     return [
    //         'event_id' => $this->event->id,
    //         'employee_id' => $this->employee->event,
    //         'msg' => $data['body'],
    //         'action' => $data['action'],
    //         'app' => UserDeviceFcm::BUSINESS_APP
    //     ];
    // }
    private function getQueryParams()
    {
        $data = $this->getDataNotify();

        return [
            'action' => $data['action'],
            'event_id' => $this->event->id.'',
            'employee_id' => $this->employee->id.'',
            'page' => 'project',
            'key' => '1',
            'notify_type' => 'SMS_MESSAGE',
            'route_name' => $data['route_name'],
            'arguments' => $data['arguments'],
        ];
    }

    public function toFcmBusiness($notifiable)
    {
        $data = $this->getDataNotify();
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

    public function toFcm($notifiable)
    {
        return $this->toFcmBusiness($notifiable);
    }

    // public function toTwilio($notifiable)
    // {
    //     $newUrl = $this->getLink();
    //     $data = $this->getDataNotify();
    //     return (new TwilioSmsMessage())
    //         ->content($data['body'] . ": " . $newUrl);
    // }
}
