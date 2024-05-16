<?php

namespace App\Notifications;

use App\Enums\NotifySendTo;
use App\Helpers\Util;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailPhoneChange extends Notification
{
    use Queueable;

    /** @var string token for email verify */
    private string $code;

    private NotifySendTo $sendTo;

    const ACTION = 'PHONE_CHANGE';

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $code, NotifySendTo $sendTo)
    {
        $this->code = $code;
        $this->sendTo = $sendTo;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get query param before send notify
     *
     * @param array
     */
    private function getQueryParams(User $user): array
    {
        return [
            'action' => self::ACTION,
            'code' => $this->code.'',
            'email' => $user->email,
            'phone_number' => $user->phone_number.'',
            'dial_code' => $user->dial_code.'',
        ];
    }

    /**
     * Get data notify to send
     */
    private function getDataNotify(): array
    {
        return [
            'title' => __('change-phone.title_email_verify'),
            'content' => __('change-phone.content_email_verify', ['time_hour' => config('constant.expired_time_email_change_phone')]),
        ];
    }

    /**
     * Get dynamic link for app
     */
    public function getLink(User $user): string
    {
        $queryParams = http_build_query($this->getQueryParams($user));

        return Util::createDynamicLink($this->sendTo->value, "?$queryParams");
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $dataContent = $this->getDataNotify();
        $link = $this->getLink($notifiable);

        return (new MailMessage)->subject(config('app.name').' - '.$dataContent['title'])->markdown('mail.change-phone', compact('notifiable', 'dataContent', 'link'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
