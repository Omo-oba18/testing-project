<?php

namespace App\Notifications;

use App\Enums\NotificationAction;
use App\Enums\RosterAction;
use App\Enums\UserType;
use App\Roster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class NotifyRosterAction extends Notification implements ShouldQueue
{
    use Queueable;

    private array $data;

    private $lang;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Roster $roster, protected string $action, $lang = null)
    {
        $this->data = [
            'uuid' => Str::uuid()->toString(),
            'action' => $this->action,
            'avatar' => $this->action == RosterAction::SEND_CREATOR ? $this->roster->employee?->contact?->userContact?->avatar_url : $this->roster?->creator?->company?->logo_url,
            'user_type' => $this->action == RosterAction::SEND_CREATOR ? UserType::BUSINESS : UserType::PERSONAL,
        ];
        $this->lang = $lang;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [FcmChannel::class, 'database'];
    }

    /**
     * Get data notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    private function getDataNotification($notifiable)
    {
        switch ($this->action) {
            case RosterAction::START:
                $dataNotify['title'] = __('notify.employee_action_roster_title', [], $this->lang ?? 'en');
                $dataNotify['body'] = __('notify.roster_start', ['finish_time' => date('H:i', strtotime($this->roster->end_time)), 'store_name' => $this->roster?->store?->name], $this->lang ?? 'en');
                $dataNotify['route_name'] = NotificationAction::ToCalenderPersonal();
                $dataNotify['arguments'] = date('Y-m-d', strtotime($this->roster->start_time));
                break;
            case RosterAction::EDIT:
                $dataNotify['title'] = __('notify.employee_action_roster_title', [], $this->lang ?? 'en');
                $dataNotify['body'] = __('notify.edit_roster', ['company_name' => $this->roster?->creator?->company?->name, 'start_time' => date('d-F', strtotime($this->roster->start_time)), 'start_hour' => date('H:i', strtotime($this->roster->start_time)), 'finish_time' => date('H:i', strtotime($this->roster->end_time)), 'store_name' => $this->roster?->store?->name], $this->lang ?? 'en');
                $dataNotify['route_name'] = NotificationAction::ToCalenderPersonal();
                $dataNotify['arguments'] = date('Y-m-d', strtotime($this->roster->start_time));
                break;
            case RosterAction::NOT_RESPONSE:
                $dataNotify['title'] = __('notify.employee_action_roster_title', [], $this->lang ?? 'en');
                $dataNotify['body'] = __('notify.not_response_roster', [], $this->lang ?? 'en');
                $dataNotify['route_name'] = NotificationAction::ToEventForYou();
                $dataNotify['arguments'] = '0';
                break;
            case RosterAction::SEND_CREATOR:
                $dataNotify['title'] = __('notify.employee_action_roster_title', [], $this->lang ?? 'en');
                $dataNotify['body'] = __('notify.send_creator_when_not_response_roster', ['user' => $this->roster->employee?->contact?->userContact->name, 'store_name' => $this->roster?->store?->name], $this->lang ?? 'en');
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

        return $dataNotify;
    }

    /**
     * @return FcmMessage
     */
    public function toFcm($notifiable)
    {
        $dataNotify = $this->getDataNotification($notifiable);
        $this->data['route_name'] = $dataNotify['route_name'];
        $this->data['arguments'] = $dataNotify['arguments'];
        $data = $this->data;

        return FcmMessage::create()
            ->setData($data)
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle($dataNotify['title'])
                    ->setBody($dataNotify['body'])
            )
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('android_'.$this->data['action']))
                    ->setNotification(
                        AndroidNotification::create()
                            ->setColor('#f7dc7a')
                            ->setClickAction('FLUTTER_NOTIFICATION_CLICK')

                    )
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('ios_'.$this->data['action']))
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $dataNotify = $this->getDataNotification($notifiable);
        $this->data['title'] = $dataNotify['title'];
        $this->data['body'] = $dataNotify['body'];
        $this->data['route_name'] = $dataNotify['route_name'];
        $this->data['arguments'] = $dataNotify['arguments'];

        return $this->data;
    }
}
