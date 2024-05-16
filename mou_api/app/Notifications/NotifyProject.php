<?php

namespace App\Notifications;

use App\Enums\NotificationAction;
use App\Enums\NotifySendTo;
use App\Enums\UserType;
use App\Helpers\Util;
use App\Project;
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

class NotifyProject extends Notification implements ShouldQueue
{
    use Queueable;

    const ACTION = 'PROJECT_EMPLOYEE_';

    private $project;

    private $content;

    private $title;

    private $action;

    private $navigation;

    private $argument;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Project $project, $action, $type = '', $lang = null)
    {
        $this->project = $project;

        // content notify employee responsible project
        if ($type == 'responsible') {
            $this->navigation = NotificationAction::ToCalenderPersonal();
            $this->argument = date('Y-m-d', strtotime($project->start_date));
            // action create
            if ($action == 'create') {
                $this->action = self::ACTION.'CREATE';
                $this->title = __('notify.employee_responsible_create_project_title', ['project_name' => $project->title], $lang ?? 'en');
                $this->content = __('notify.assign_leader', ['company_name' => optional($project->company)->name, 'project_title' => $project->title], $lang ?? 'en');
            }
            // action update
            else {
                $this->action = self::ACTION.'UPDATE';
                $this->title = __('notify.employee_responsible_edit_project_title', ['project_name' => $project->title], $lang ?? 'en');
                $this->content = __('notify.employee_responsible_edit_project_body', ['creator_name' => optional($project->creator)->name], $lang ?? 'en');
            }
            // content notify employee in new teams
        } else {
            if ($action == 'create') {
                $this->action = self::ACTION.'CREATE';
            } else {
                $this->action = self::ACTION.'UPDATE';
            }
            $this->title = __('notify.employee_join_project_title', ['project_name' => $project->title], $lang ?? 'en');
            $this->content = __('notify.employee_join_project_body', ['creator_name' => optional($project->creator)->name], $lang ?? 'en');
        }
    }

    private function getLink()
    {
        $queryParams = http_build_query($this->getQueryParams());

        return Util::createDynamicLink(NotifySendTo::PERSONAL, "?$queryParams");
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
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    /* public function toMail($notifiable)
    {
        $content = $this->content;
        $link = $this->getLink();

        return (new MailMessage)
            ->subject(config('app.name').' - '.$this->title)
            ->markdown('mail.event.notify', compact('notifiable', 'content', 'link'));
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
            'title' => $this->title,
            'body' => $this->content,
            'action' => $this->action,
            'user_type' => UserType::PERSONAL,
            'avatar' => $this->project?->company?->logo_url,
            'route_name' => $this->navigation,
            'arguments' => $this->argument,
        ];
    }
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    // public function toDatabase($notifiable)
    // {
    //     return [
    //         'project_id' => $this->project->id,
    //         'msg' => $this->content,
    //         'action' => $this->action,
    //         'created_at' => $this->project->created_at
    //     ];
    // }

    private function getQueryParams()
    {
        return [
            'project_id' => $this->project->id.'',
            'action' => $this->action,
            'created_at' => $this->project->created_at.'',
            'page' => 'event',
            'key' => '0',
            'notify_type' => 'SMS_MESSAGE',
        ];
    }

    public function toFcm($notifiable)
    {
        $data = $this->toArray($notifiable);

        return FcmMessage::create()
            ->setData($data)
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($this->title)
                ->setBody($this->content))
            //                ->setImage('http://example.com/url-to-image-here.png'));
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('join_project_notify_android'))
                    ->setNotification(AndroidNotification::create()->setColor('#f7dc7a')->setClickAction('FLUTTER_NOTIFICATION_CLICK'))
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('join_project_notify_ios'))
            );
    }

    // public function toTwilio($notifiable)
    // {
    //     $newUrl = $this->getLink();

    //     return (new TwilioSmsMessage())
    //         ->content($this->content . ": " . $newUrl);
    // }

}
