# Firebase and Database Notification Package

A Laravel package for sending notifications via Firebase Cloud Messaging (FCM) and Laravel's database notification system using a clean, channel-based architecture.

## Features

- 🔥 **Firebase Channel**: Send push notifications via Firebase Cloud Messaging using Laravel's standard `notify()` pipeline.
- 💾 **Database Channel**: Store notifications in the database using Laravel's built-in notification system.
- 🏗️ **Channel-Based Architecture**: Fully integrated with Laravel's `Notification` system using `via()`, `toFirebase()`, and `toDatabase()`.
- ✅ **Fully Tested**: Robust test suite using Orchestra Testbench and Mockery.

## Requirements

- PHP ^8.2
- Laravel ^10.0|^11.0|^12.0
- Firebase Admin SDK credentials

## Installation

Install the package via Composer:

```bash
composer require cofa/notification_via_firebase_and_database
```

### Register Service Provider

If you're using Laravel 11+, the service provider will be auto-discovered. For older versions, add to `config/app.php`:

```php
'providers' => [
    // ...
    Cofa\NotificationViaFirebaseAndDatabase\FirebaseNotificationServiceProvider::class,
],
```

### Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=firebase-notification-config
```

This will create `config/firebase-notification.php`.

## Configuration

### Firebase Setup

1. Download your Firebase service account credentials JSON file from the Firebase Console.
2. Place it in your Laravel project (e.g., `storage/app/firebase/credentials.json`).
3. Update `config/firebase-notification.php`:

```php
return [
    'firebase' => [
        'credentials' => storage_path('app/firebase/credentials.json'),
    ],
];
```

### Database Setup

Run the installation command to set up the required database tables:

```bash
php artisan firebase-notification:install
```

This command will automatically:
1. Run `php artisan notifications:table` to create the Laravel notifications table.
2. Publish the `user_device_tokens` table migration.

Then run the migrations:

```bash
php artisan migrate
```

## Usage

### 1. Create a Notification

Extend `FirebaseNotification` to support both Firebase and Database channels.

```php
<?php

namespace App\Notifications;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebaseNotification;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebasePayload;

class OrderShipped extends FirebaseNotification
{
    public function __construct(array $orderData)
    {
        $payload = new FirebasePayload();
        $payload->setData([
            'notification' => [
                'title' => 'Order Shipped',
                'body' => "Your order #{$orderData['order_id']} has been shipped!"
            ],
            'data' => [
                'order_id' => $orderData['order_id'],
                'type' => 'order_shipped'
            ]
        ]);
        
        parent::__construct($payload);
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['firebase', 'database'];
    }

    /**
     * Optional: Define data for database specifically.
     * If not defined, it defaults to the Firebase payload.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'order_id' => $this->payload->getPayload()['data']['order_id'],
            'message' => 'Your order has been shipped'
        ];
    }
}
```

### 2. Prepare the Notifiable Model

Ensure your `User` model uses the `Notifiable` trait and implements `routeNotificationForFirebase`.

```php
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Route notifications for the Firebase channel.
     *
     * @return string|array
     */
    public function routeNotificationForFirebase()
    {
        // Return a single token or an array of tokens
        return $this->deviceTokens()->pluck('device_token')->toArray();
    }
}
```

### 3. Send the Notification

Simply use the `notify()` method:

```php
$user->notify(new OrderShipped($orderData));
```

## Advanced Usage

### Custom Payloads

You can use `FirebasePayload` to set platform-specific configurations:

```php
$payload = new FirebasePayload();
$payload->setData(['key' => 'value']);

// Set Android Configuration
$payload->setAndroidConfiguration([
    'priority' => 'high',
    'notification' => ['sound' => 'default']
]);

// Set iOS Configuration
$payload->setIOSConfiguration([
    'aps' => ['sound' => 'default', 'badge' => 1]
]);
```

### Only Database Notifications

If you only want to save to the database, you can use the `DatabaseNotification` class:

```php
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\DatabaseNotification;

$notification = new DatabaseNotification(['message' => 'Hello Database']);
$user->notify($notification);
```

## Architecture

```
src/
├── Channels/
│   ├── FirebaseChannel.php           # Logic for sending to FCM
│   └── DatabaseChannel.php           # Logic for storing in database
├── Contracts/
│   ├── Payload.php                   # Base payload class
│   ├── FirebasePayload.php           # Firebase-specific payload
│   ├── DatabasePayload.php           # Database-specific payload
│   ├── FirebaseNotification.php      # Base Firebase notification
│   └── DatabaseNotification.php      # Base Database notification
└── FirebaseNotificationServiceProvider.php
```

## Testing

Run the test suite:

```bash
composer test
```

## License

This package is open-source software licensed under the [MIT license](LICENSE).

## Credits

- **Author**: Mahmoud Gamal
- **Email**: mgcofa@gmail.com
