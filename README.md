# Firebase and Database Notification Package

A Laravel package for sending notifications via Firebase Cloud Messaging (FCM) and Laravel's database notification system with a clean, structured code architecture.

## Features

- 🔥 Send push notifications via Firebase Cloud Messaging
- 💾 Store notifications in database using Laravel's notification system
- 🎯 Send to single or multiple targets
- 🏗️ Clean, extensible architecture with contracts and interfaces
- ✅ Fully tested with PHPUnit

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

1. Download your Firebase service account credentials JSON file from the Firebase Console
2. Place it in your Laravel project (e.g., `storage/app/firebase/credentials.json`)
3. Update `config/firebase-notification.php`:

```php
return [
    'firebase' => [
        'credentials' => storage_path('app/firebase/credentials.json'),
    ],
];
```

### Database Setup

#### Install Package Tables

Run the installation command to set up the required database tables:

```bash
php artisan firebase-notification:install
```

This command will automatically:
1. Run `php artisan notifications:table` to create the Laravel notifications table
2. Publish the `user_device_tokens` table migration

Then run the migrations:

```bash
php artisan migrate
```

The installation creates two tables:
- `user_device_tokens` - Stores FCM device tokens for users
- `notifications` - Laravel's default notifications table (if not already exists)

#### User Device Tokens Table Structure

The `user_device_tokens` table includes:
- `user_id` - Foreign key to users table
- `device_token` - Unique FCM device token
- `device_type` - Device platform (android, ios, web)
- `device_name` - Optional device name
- `is_active` - Token active status
- `last_used_at` - Last time token was used

## Usage

### 1. Firebase Notifications

#### Step 1: Create the Firebase Payload

```php
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebasePayload;

$payload = new FirebasePayload();

// Set Notification Data
$payload->setData([
    'notification' => [
        'title' => 'Order Shipped',
        'body' => 'Your order #12345 has been shipped!'
    ],
    'data' => [
        'order_id' => '12345',
        'tracking_number' => 'TRK123456789',
        'type' => 'order_shipped'
    ]
]);

// Set Android Configuration
$payload->setAndroidConfiguration([
    'priority' => 'high',
    'notification' => [
        'sound' => 'default',
        'color' => '#ff0000'
    ]
]);

// Set iOS Configuration
$payload->setIOSConfiguration([
    'headers' => [
        'apns-priority' => '10'
    ],
    'payload' => [
        'aps' => [
            'sound' => 'default',
            'badge' => 1
        ]
    ]
]);
```

#### Step 2: Create a Custom Firebase Notification Class

```php
<?php

namespace App\Notifications;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebaseNotification;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebasePayload;

class OrderShippedFirebaseNotification extends FirebaseNotification
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
                'tracking_number' => $orderData['tracking_number'],
                'type' => 'order_shipped'
            ]
        ]);
        
        // Optional: Set platform specific configs
        $payload->setAndroidConfiguration(['priority' => 'high']);

        parent::__construct($payload);
    }
}
```

#### Step 3: Send the Firebase Notification

```php
use App\Notifications\OrderShippedFirebaseNotification;

$orderData = [
    'order_id' => 12345,
    'tracking_number' => 'TRK123456789'
];

$notification = new OrderShippedFirebaseNotification($orderData);

// Device FCM tokens (from your database or user model)
$tokens = [
    'device_token_1',
    'device_token_2',
    'device_token_3'
];

$notification->sendNotification($tokens);
```

### 2. Database Notifications

#### Step 1: Prepare the Notification Data

```php
$notificationData = [
    'type' => 'order_shipped',
    'title' => 'Order Shipped',
    'message' => 'Your order has been shipped',
    'order_id' => 12345,
    'tracking_number' => 'TRK123456789',
    'timestamp' => now()
];
```

#### Step 2: Create and Send the Database Notification

```php
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\DatabaseNotification;

$notification = new DatabaseNotification($notificationData);

// Send to User models (must use Notifiable trait)
$users = User::whereIn('id', [1, 2, 3])->get();

$notification->sendNotification($users->toArray());
```

#### Step 3: Retrieve User Notifications

```php
// Get unread notifications
$unreadNotifications = $user->unreadNotifications;

// Get all notifications
$allNotifications = $user->notifications;

// Mark as read
$user->unreadNotifications->markAsRead();
```

### 3. Combined Notifications

Send both Firebase and database notifications:

```php
use App\Notifications\OrderShippedFirebaseNotification;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\DatabaseNotification;

$orderData = [
    'order_id' => 12345,
    'tracking_number' => 'TRK123456789'
];

$users = User::whereIn('id', [1, 2, 3])->get();
$fcmTokens = $users->pluck('fcm_token')->toArray();

// Send Firebase notification
$firebaseNotification = new OrderShippedFirebaseNotification($orderData);
$firebaseNotification->sendNotification($fcmTokens);

// Send database notification
$databaseNotification = new DatabaseNotification([
    'type' => 'order_shipped',
    'message' => "Order #{$orderData['order_id']} shipped",
    'order_id' => $orderData['order_id']
]);
$databaseNotification->sendNotification($users->toArray());
```

### 4. Custom Payload Builder

Create custom payloads for complex scenarios:

```php
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebasePayload;

$payload = new FirebasePayload();
$payload->setData([
    'notification' => [
        'title' => 'Special Offer',
        'body' => 'Get 50% off on your next purchase!'
    ],
    'data' => [
        'promotion_id' => 'PROMO123',
        'discount' => 50,
        'valid_until' => '2026-12-31',
        'deep_link' => 'app://promotions/PROMO123'
    ]
]);

$payload->setAndroidConfiguration([
    'priority' => 'high'
]);

$payload->setIOSConfiguration([
    'headers' => [
        'apns-priority' => '10'
    ]
]);
```

## Extending the Package

### Create Custom Notification Classes

```php
<?php

namespace App\Notifications;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\Notification;

class CustomNotification implements Notification
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sendNotification(array $targets): void
    {
        // Your custom notification logic
    }
}
```

### Create Custom Payload Classes

```php
<?php

namespace App\Payloads;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\Payload;

class CustomPayload extends Payload
{
    public function setTitle(string $title): self
    {
        $this->payload['notification']['title'] = $title;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->payload['notification']['body'] = $body;
        return $this;
    }
}
```

## Testing

Run the test suite:

```bash
composer test
```

Run with coverage:

```bash
vendor/bin/phpunit --coverage-html coverage
```

Run static analysis:

```bash
composer psalm
```

## Architecture

```
src/
└── Contracts/
    ├── Notification.php              # Base notification interface
    ├── Payload.php                   # Base payload class
    ├── FirebasePayload.php           # Firebase-specific payload
    ├── DatabasePayload.php           # Database-specific payload
    ├── FirebaseNotification.php      # Firebase notification implementation
    └── DatabaseNotification.php      # Database notification implementation
```

## Error Handling

The package throws exceptions for common errors:

```php
try {
    $notification->sendNotification($tokens);
} catch (\RuntimeException $e) {
    // Handle missing credentials or configuration errors
    Log::error('Firebase notification failed: ' . $e->getMessage());
}
```

## Security

- Never commit your Firebase credentials file to version control
- Add credentials path to `.gitignore`
- Use environment variables for sensitive paths
- Validate user input before creating notifications

## Contributing

Contributions are welcome! Please submit pull requests or open issues on GitHub.

## License

This package is open-source software licensed under the MIT license.

## Credits

- **Author**: Mahmoud Gamal
- **Email**: mgcofa@gmail.com

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/Cofa12/notification_via_firebase_and_database).