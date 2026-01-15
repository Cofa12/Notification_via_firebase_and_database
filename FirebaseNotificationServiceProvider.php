<?php

namespace Cofa\NotificationViaFirebaseAndDatabase;

use Illuminate\Support\ServiceProvider;

class FirebaseNotificationServiceProvider extends ServiceProvider
{
    public function register():void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/firebase-notification.php',
            'firebase-notification'
        );
    }

    public function boot():void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/firebase-notification.php' =>
                    $this->app->configPath('firebase-notification.php'),
            ], 'firebase-notification-config');
        }
    }

}
