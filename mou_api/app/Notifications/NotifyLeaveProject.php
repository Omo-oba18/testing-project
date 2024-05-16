<?php

namespace App\Notifications;

use App\Enums\NotifySendTo;
use App\Enums\UserType;
use App\Helpers\Util;
use App\NotificationChannels\FcmBusinessChannel;
use App\Project;
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

class NotifyLeaveProject extends Notification implements ShouldQueue
{
    use Queueable;

    const ACTION = 'LEAVE_PROJECT_';

    private $project;

    private $type_person;

    private $employee;

    private $user;

    private $lang;

    /**
     * Create a new notification instance.
     *
     * @param $project type Project model
     * @param $type_person in responsible or employee
     * @param $employee if employee leave team must pass this parameters
     * @return void
     */
    public function __construct(Project $project, $type_person, $user, $employee = '', $lang = null)
    {
        $this->project = $project;
        $this->type_person = $type_person;
        $this->employee = $employee;
        $this->user = $user;
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
        return ['database', FcmBusinessChannel::class];
    }

    private function getDataNotify()
    {
        if ($this->type_person == 'responsible') {
            $data['action'] = self::ACTION.'RESPONSIBLE';
            $data['title'] = __('notify.person_responsible_leave_project_title', ['project_name' => $this->project->title], $this->lang ?? 'en');
            $data['body'] = __('notify.person_responsible_leave_project_body', [], $this->lang ?? 'en');
            $data['user_type'] = UserType::BUSINESS;
            $data['avatar'] = $this->user?->avatar_url;
        } else {
            $data['action'] = self::ACTION.'EMPLOYEE';
            $data['title'] = __('notify.employee_leave_project_title', ['project_name' => $this->project->title], $this->lang ?? 'en');
            $data['body'] = __('notify.employee_leave_project_body', ['employee_name' => $this->employee], $this->lang ?? 'en');
            $data['user_type'] = UserType::BUSINESS;
            $data['avatar'] = $this->user?->avatar_url;
        }

        return $data;
    }

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
        return $this->getDataNotify();
    }

    private function getQueryParams()
    {
        $data = $this->getDataNotify();

        return [
            'action' => $data['action'],
            'project_id' => $this->project->id.'',
            'page' => 'project_detail',
            'name' => $this->project->title,
            'notify_type' => 'SMS_MESSAGE',
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
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('invite_leave_project_notify_android'))
                    ->setNotification(AndroidNotification::create()->setColor('#f7dc7a')->setClickAction('FLUTTER_NOTIFICATION_CLICK'))
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('invite_leave_project_notify_ios'))
            );
    }

    // public function toTwilio($notifiable)
    // {
    //     $newUrl = $this->getLink();

    //     $data = $this->getDataNotify();
    //     return (new TwilioSmsMessage())
    //         ->content($data['content'] . ": " . $newUrl);
    // }
}
