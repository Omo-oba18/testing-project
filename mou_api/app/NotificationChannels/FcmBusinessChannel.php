<?php

namespace App\NotificationChannels;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Message;
use NotificationChannels\Fcm\Exceptions\CouldNotSendNotification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class FcmBusinessChannel extends FcmChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @return array
     *
     * @throws CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        $token = $notifiable->routeNotificationFor('fcmBusiness');

        if (empty($token)) {
            return [];
        }
        // Get the message from the notification class
        $fcmMessage = $notification->toFcmBusiness($notifiable);

        if (! $fcmMessage instanceof Message) {
            throw new CouldNotSendNotification('The toFcmBusiness() method only accepts instances of '.Message::class);
        }

        $responses = [];

        if (! is_array($token)) {
            if ($fcmMessage instanceof CloudMessage) {
                $fcmMessage = $fcmMessage->withChangedTarget('token', $token);
            }

            if ($fcmMessage instanceof FcmMessage) {
                $fcmMessage->setToken($token);
            }

            $responses[] = $this->sendToFcm($fcmMessage);
        } else {
            // Use multicast because there are multiple recipients
            $partialTokens = array_chunk($token, self::MAX_TOKEN_PER_REQUEST, false);
            foreach ($partialTokens as $tokens) {
                $responses[] = $this->sendToFcmMulticast($fcmMessage, $tokens);
            }
        }

        return $responses;
    }
}
