<?php

namespace Cofa\NotificationViaFirebaseAndDatabase\Channels;

use Illuminate\Notifications\Notification;

class DatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toDatabase($notifiable);

        return $notifiable->routeNotificationFor('database', $notification)->create([
            'id' => $notification->id,
            'type' => get_class($notification),
            'data' => $data,
            'read_at' => null,
        ]);
    }
}
