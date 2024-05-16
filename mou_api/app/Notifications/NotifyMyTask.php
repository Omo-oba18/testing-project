<?php

namespace App\Notifications;

use App\Enums\NotificationAction;
use App\Enums\NotifySendTo;
use App\Enums\TaskAndProjectAction;
use App\Enums\UserType;
use App\Event;
use App\Helpers\Util;
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
use NotificationChannels\Twilio\TwilioSmsMessage;

class NotifyMyTask extends Notification implements ShouldQueue
{
    use Queueable;

    const ACTION = 'ADD_MY_TASK';

    private $title;

    private $content;

    private $lang;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(protected Event $task, protected string $type, protected string $action, protected $sendTo = null, $lang = null)
    {
        $this->title = __('notify.employee_create_task_title', ['task_name' => $this->task->title], $lang ?? 'en');
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

    /**
     * Get data notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    private function getDataNotification($notifiable)
    {
        if ($this->type == config('constant.event.type.task')) {
            switch ($this->action) {
                case TaskAndProjectAction::TASK_CREATE:
                    $dataNotify['title'] = $this->title;
                    $dataNotify['body'] = __('notify.create_task', ['company_name' => optional($this->task->company)->name, 'task_title' => $this->task->title, 'date' => now()->format('d-F'), 'store_name' => optional($this->task->store)->name], $this->lang ?? 'en');
                    $dataNotify['route_name'] = NotificationAction::ToEventForYou();
                    $dataNotify['arguments'] = '0';
                    break;
                case TaskAndProjectAction::TASK_EDIT:
                    $dataNotify['title'] = $this->title;
                    $dataNotify['body'] = __('notify.edit_task', ['company_name' => optional($this->task->company)->name, 'task_title' => $this->task->title, 'date' => now()->format('d-F'), 'store_name' => optional($this->task->store)->name], $this->lang ?? 'en');
                    $dataNotify['route_name'] = NotificationAction::ToCalenderPersonal();
                    $dataNotify['arguments'] = date('Y-m-d', strtotime($this->task->start_date));
                    break;
                case TaskAndProjectAction::TASK_NOT_RESPONSE:
                    $dataNotify['title'] = $this->title;
                    $dataNotify['body'] = __('notify.not_response_task', ['task_title' => $this->task->title, 'store_name' => optional($this->task->store)->name], $this->lang ?? 'en');
                    $dataNotify['route_name'] = NotificationAction::ToEventForYou();
                    $dataNotify['arguments'] = '0';
                    break;
                case TaskAndProjectAction::SEND_CREATOR:
                    $dataNotify['title'] = $this->title;
                    $dataNotify['body'] = __('notify.send_creator_when_not_response_task', ['user' => $this->sendTo, 'task_title' => $this->task->title], $this->lang ?? 'en');
                    $dataNotify['route_name'] = NotificationAction::ToEventWaiting();
                    $dataNotify['arguments'] = '1';
                    break;
                default:
                    $dataNotify = [
                        'title' => 'UNKNOWN',
                        'body' => 'UNKNOWN',
                        'route_name' => null,
                        'arguments' => null,
                    ];
            }
        } else {
            switch ($this->action) {
                case TaskAndProjectAction::PROJECT_CREATE:
                    $dataNotify['title'] = $this->title;
                    $dataNotify['body'] = __('notify.create_task_in_project', ['company_name' => optional($this->task->company)->name, 'task_title' => $this->task->title, 'date' => now()->format('d-F'), 'project_title' => optional($this->task->project)->title, 'leader' => optional($this->task->project)?->employeeResponsible?->contact?->name], $this->lang ?? 'en');
                    $dataNotify['route_name'] = NotificationAction::ToEventForYou();
                    $dataNotify['arguments'] = '0';
                    break;
                case TaskAndProjectAction::PROJECT_EDIT:
                    $dataNotify['title'] = $this->title;
                    $dataNotify['body'] = __('notify.edit_task_in_project', ['company_name' => optional($this->task->company)->name, 'task_title' => $this->task->title, 'date' => now()->format('d-F'), 'project_title' => optional($this->task->project)->title, 'leader' => optional($this->task->project)?->employeeResponsible?->contact?->name], $this->lang ?? 'en');
                    $dataNotify['route_name'] = NotificationAction::ToCalenderPersonal();
                    $dataNotify['arguments'] = date('Y-m-d', strtotime(optional($this->task->project)->start_date));
                    break;
                case TaskAndProjectAction::PROJECT_NOT_RESPONSE:
                    $dataNotify['title'] = $this->title;
                    $dataNotify['body'] = __('notify.not_response_project', ['project_title' => optional($this->task->project)->title, 'task_title' => $this->task->title, 'leader' => optional($this->task->project)?->employeeResponsible?->contact?->name], $this->lang ?? 'en');
                    $dataNotify['route_name'] = NotificationAction::ToEventWaiting();
                    $dataNotify['arguments'] = '1';
                    break;
                case TaskAndProjectAction::SEND_CREATOR:
                    $dataNotify['title'] = $this->title;
                    $dataNotify['body'] = __('notify.send_creator_when_not_response_project', ['user' => $this->sendTo, 'task_title' => $this->task->title, 'project_title' => optional($this->task->project)->title, 'leader' => optional($this->task->project)?->employeeResponsible?->contact?->name], $this->lang ?? 'en');
                    $dataNotify['route_name'] = NotificationAction::ToEventWaiting();
                    $dataNotify['arguments'] = '1';
                    break;
                default:
                    $dataNotify = [
                        'title' => 'UNKNOWN',
                        'body' => 'UNKNOWN',
                        'route_name' => null,
                        'arguments' => null,
                    ];
            }
        }

        return $dataNotify;
    }

    private function getLink()
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
        $link = $this->getLink();
        $content = $this->content;

        return (new MailMessage)
            ->subject(config('app.name').' - '.$this->title)
            ->markdown('mail.event.new-employee', compact('user', 'content', 'link'));
    } */

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $dataNotify = $this->getDataNotification($notifiable);

        return [
            'action' => self::ACTION,
            'title' => $dataNotify['title'],
            'body' => $dataNotify['body'],
            'user_type' => $this->action == TaskAndProjectAction::SEND_CREATOR ? UserType::BUSINESS : UserType::PERSONAL,
            'avatar' => $this->task?->company?->logo_url,
            'route_name' => $dataNotify['route_name'],
            'arguments' => $dataNotify['arguments'],
        ];
    }

    private function getQueryParams()
    {
        return [
            'action' => self::ACTION,
            'event_id' => $this->task->id.'',
            'page' => 'event',
            'key' => '0',
            'notify_type' => 'SMS_MESSAGE',
        ];
    }

    public function toFcm($notifiable)
    {
        $data = $this->toArray($notifiable);
        $dataNotify = $this->getDataNotification($notifiable);

        return FcmMessage::create()
            ->setData($data)
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($dataNotify['title'])
                ->setBody($dataNotify['body']))
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
    //    $newUrl = $this->getLink();

    //     return (new TwilioSmsMessage())
    //         ->content($this->content . ": " . $newUrl);
    // }
}
