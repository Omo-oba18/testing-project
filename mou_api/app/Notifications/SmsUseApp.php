<?php

namespace App\Notifications;

use App\Enums\NotifySendTo;
use App\Helpers\Util;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SmsUseApp extends Notification implements ShouldQueue
{
    use Queueable;

    private $content;

    private $params;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($name, $name_friend, $locate, $params = '', $event = null, $lang = null)
    {
        if (! empty($event)) {
            $this->content = __('notify.sms_event_join_app', ['user_invite' => $name_friend, 'event_title' => $event?->title, 'date' => now()->format('d-F'), 'hour' => now()->format('H:i'), 'place' => $event->place], $locate);
        } else {
            $this->content = __('notify.sms_join_app', ['name' => $name, 'name_friend' => $name_friend], $locate);
        }
        $this->params = $params;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', TwilioChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    /* public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
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
            'contact_id' => $notifiable->id,
            'msg' => $this->content,
        ];
    }

    public function toTwilio($notifiable)
    {
        $newUrl = Util::createDynamicLink(NotifySendTo::PERSONAL, $this->params);

        return (new TwilioSmsMessage())
            ->content($this->content.': '.$newUrl);
    }
}
