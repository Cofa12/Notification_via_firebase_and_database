<?php

namespace Cofa\NotificationViaFirebaseAndDatabase\Contracts;

use Illuminate\Notifications\Notification as LaravelNotification;

abstract class FirebaseNotification extends LaravelNotification
{
    protected FirebasePayload $payload;

    public function __construct(FirebasePayload $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase($notifiable): array
    {
        return $this->payload->getPayload();
    }
}
