<?php

namespace Cofa\NotificationViaFirebaseAndDatabase;

use Cofa\NotificationViaFirebaseAndDatabase\Channels\DatabaseChannel;
use Cofa\NotificationViaFirebaseAndDatabase\Channels\FirebaseChannel;
use Cofa\NotificationViaFirebaseAndDatabase\Console\InstallCommand;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class FirebaseNotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/firebase-notification.php',
            'firebase-notification'
        );

        $this->app->bind(\Kreait\Firebase\Factory::class, function ($app) {
            return new \Kreait\Firebase\Factory();
        });
    }

    public function boot(): void
    {
        Notification::extend('firebase', function ($app) {
            return new FirebaseChannel();
        });

        Notification::extend('database', function ($app) {
            return new DatabaseChannel();
        });

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
