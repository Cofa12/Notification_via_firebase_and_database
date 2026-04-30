<?php

namespace Cofa\NotificationViaFirebaseAndDatabase\Channels;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use RuntimeException;

class FirebaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFirebase')) {
            return;
        }

        $targets = $notifiable->routeNotificationFor('firebase', $notification);

        if (empty($targets)) {
            return;
        }

        if (! is_array($targets)) {
            $targets = [$targets];
        }

        $payload = $notification->toFirebase($notifiable);
        $messaging = $this->getMessaging();

        $message = CloudMessage::fromArray($payload);

        $messaging->sendMulticast($message, $targets);
    }

    /**
     * Get the Firebase Messaging instance.
     *
     * @return \Kreait\Firebase\Contract\Messaging
     */
    protected function getMessaging(): \Kreait\Firebase\Contract\Messaging
    {
        $credentialsPath = config('firebase-notification.firebase.credentials');

        if (! is_string($credentialsPath) || ! file_exists($credentialsPath)) {
            throw new RuntimeException(
                'Firebase credentials file not found or not configured.'
            );
        }

        return app(Factory::class)
            ->withServiceAccount($credentialsPath)
            ->createMessaging();
    }
}
