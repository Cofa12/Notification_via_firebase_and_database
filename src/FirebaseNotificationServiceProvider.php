<?php

namespace Cofa\NotificationViaFirebaseAndDatabase;

use Cofa\NotificationViaFirebaseAndDatabase\Console\InstallCommand;
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
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/firebase-notification.php' =>
                    $this->app->configPath('firebase-notification.php'),
            ], 'firebase-notification-config');

            // Register commands
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

}
