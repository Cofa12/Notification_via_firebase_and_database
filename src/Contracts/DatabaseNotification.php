<?php

namespace Cofa\NotificationViaFirebaseAndDatabase\Contracts;

use Illuminate\Notifications\Notification as LaravelNotification;

class DatabaseNotification extends LaravelNotification implements Notification
{
    protected array $data;

    public function __construct(DatabasePayload|array $databasePayload)
    {
        if (is_array($databasePayload)) {
            $this->data = $databasePayload;
        } else {
            $this->data = $databasePayload->getData();
        }
    }

    /**
     * Channels
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Stored in notifications table
     */
    public function toDatabase($notifiable): array
    {
        return $this->data;
    }

    public function sendNotification(array $targets): void
    {
        foreach ($targets as $target) {
            $target->notify($this);
        }
    }
}
