<?php

namespace Cofa\NotificationViaFirebaseAndDatabase\Contracts;

use Illuminate\Notifications\Notification as LaravelNotification;

class DatabaseNotification extends LaravelNotification
{
    protected array $data;

    public function __construct(DatabasePayload|array $databasePayload)
    {
        if (is_array($databasePayload)) {
            $this->data = $databasePayload;
        } else {
            $this->data = $databasePayload->getPayload();
        }
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return $this->data;
    }
}
