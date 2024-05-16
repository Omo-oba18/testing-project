<?php

namespace App\Notifications;

use App\Enums\NotificationAction;
use App\Enums\NotifySendTo;
use App\Enums\UserType;
use App\Event;
use App\Helpers\Util;
use App\NotificationChannels\FcmBusinessChannel;
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

class NotifyEmployeeActionTask extends Notification implements ShouldQueue
{
    use Queueable;

    const ACTION = 'EMPLOYEE';

    private $event;

    private $status;

    private $list_name;

    private $user;

    private $lang;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Event $event, $status, $list_name, $user, $lang = null)
    {
        $this->event = $event;
        $this->status = $status;
        $this->list_name = $list_name;
        $this->user = $user;
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
            $typeTask = 'TASK';
            $data['title'] = __('notify.employee_action_task_title', [], $this->lang ?? 'en');
            if ($this->status == config('constant.event.status.confirm')) {
                $statusAction = 'CONFIRM';
                $data['body'] = __(
                    'notify.user_accept_task',
                    ['user_name' => $this->user->name, 'task_title' => $this->event->title, 'date' => now()->format('d-F'), 'store_name' => optional($this->event->store)->name ? __('common.at').' '.optional($this->event->store)->name : null],
                    $this->lang ?? 'en'
                );
            }
            if ($this->status == config('constant.event.status.deny')) {
                $statusAction = 'DENY';
                $data['body'] = __(
                    'notify.user_decline_accept_task',
                    ['user_name' => $this->user->name, 'task_title' => $this->event->title, 'date' => now()->format('d-F'), 'store_name' => optional($this->event->store)->name ? __('common.at').' '.optional($this->event->store)->name : null],
                    $this->lang ?? 'en'
                );
            }
        } else {
            $typeTask = 'PROJECT';
            // nếu là project thì hiển thị tên project trong tiêu đề
            $project_name = $this->event->project ? $this->event->project->title : '';
            $data['title'] = __('notify.employee_action_project_task_title', ['project_name' => $project_name], $this->lang ?? 'en');
            if ($this->status == config('constant.event.status.confirm')) {
                $statusAction = 'CONFIRM';
                $data['body'] = __(
                    'notify.user_accept_task_project',
                    ['user_name' => $this->user->name, 'task_title' => $this->event->title, 'date' => now()->format('d-F'), 'project_title' => $project_name, 'leader' => optional($this->event->creator)->name],
                    $this->lang ?? 'en'
                );
            }
            if ($this->status == config('constant.event.status.deny')) {
                $statusAction = 'LEAVE';
                $data['body'] = __(
                    'notify.user_decline_accept_task_project',
                    ['user_name' => $this->user->name, 'task_title' => $this->event->title, 'date' => now()->format('d-F'), 'project_title' => $project_name, 'leader' => optional($this->event->creator)->name],
                    $this->lang ?? 'en'
                );
            }
        }
        $data['route_name'] = NotificationAction::ToCalenderBusiness();
        $data['arguments'] = date('Y-m-d', strtotime($this->event->start_date));
        $data['action'] = self::ACTION.'_'.$statusAction.'_'.$typeTask;

        $data['user_type'] = UserType::BUSINESS;
        $data['avatar'] = $this->user->avatar_url;

        return $data;
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
        $arr = [
            'action' => $data['action'],
            'event_id' => $this->event->id.'',
            'title' => $this->event->title,
            'creator_id' => optional($this->event->creator)->id.'',
            'notify_type' => 'SMS_MESSAGE',
            'route_name' => $data['route_name'],
            'arguments' => $data['arguments'],
        ];
        if ($this->event->type == config('constant.event.type.task')) {
            $arr['page'] = 'task';
            $arr['key'] = '1';
        } else {
            $arr['page'] = 'project_detail';
            $arr['id'] = (optional($this->event->project)->id ?? '').'';
            $arr['name'] = optional($this->event->project)->title ?? '';
        }

        return $arr;
    }

    public function toFcmBusiness($notifiable)
    {
        $data = $this->getDataNotify();
        $queryParams = $this->getQueryParams();

        return FcmMessage::create()
            ->setData($queryParams)
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

    //     $data = $this->getDataNotify();
    //     return (new TwilioSmsMessage())
    //         ->content($data['content'] . ": " . $newUrl);
    // }
}
