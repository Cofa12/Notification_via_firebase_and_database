# Firebase & Database Notifications for Laravel

![CI](https://github.com/Cofa12/Notification_via_firebase_and_database/actions/workflows/testing.yml/badge.svg)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/cofa/notification_via_firebase_and_database.svg)](https://packagist.org/packages/cofa/notification_via_firebase_and_database)
[![Total Downloads](https://img.shields.io/packagist/dt/cofa/notification_via_firebase_and_database.svg)](https://packagist.org/packages/cofa/notification_via_firebase_and_database)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue)](https://www.php.net/)

A Laravel package for sending push notifications via **Firebase Cloud Messaging (FCM)** and storing them in the **database** — using Laravel's native notification pipeline with a clean, channel-based architecture.

---

## Features

- 🔥 **Firebase Channel** — Send push notifications via FCM using Laravel's standard `notify()` pipeline
- 💾 **Database Channel** — Store notifications using Laravel's built-in notification system
- 🏗️ **Channel-Based Architecture** — Fully integrated with `via()`, `toFirebase()`, and `toDatabase()`
- 📱 **Platform-Specific Configs** — Android and iOS payload configuration support
- 🔑 **Device Token Management** — Built-in `user_device_tokens` table with active/inactive tracking
- ✅ **Fully Tested** — Test suite using Orchestra Testbench and Mockery
- 🔍 **Static Analysis** — Psalm integration for type safety

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | `^8.2` |
| Laravel | `^10.0 \| ^11.0 \| ^12.0` |
| kreait/firebase-php | `^7.0 \| ^8.0` |

---

## Installation

Install via Composer:

```bash
composer require cofa/notification_via_firebase_and_database
```

### Register the Service Provider

**Laravel 11+** — auto-discovered, no action needed.

**Laravel 10** — add to `config/app.php`:

```php
'providers' => [
    Cofa\NotificationViaFirebaseAndDatabase\FirebaseNotificationServiceProvider::class,
],
```

---

## Configuration

### 1. Publish the Config File

```bash
php artisan vendor:publish --tag=firebase-notification-config
```

This creates `config/firebase-notification.php`:

```php
return [
    'firebase' => [
        'credentials' => storage_path('app/firebase/credentials.json'),
    ],
];
```

### 2. Add Your Firebase Credentials

1. Go to [Firebase Console](https://console.firebase.google.com) → Project Settings → Service Accounts
2. Click **Generate new private key** and download the JSON file
3. Place it in your project (recommended: `storage/app/firebase/credentials.json`)
4. Add the path to `.gitignore` — **never commit credentials to version control**

```gitignore
storage/app/firebase/
```

### 3. Set Up the Database

```bash
php artisan firebase-notification:install
php artisan migrate
```

This creates two tables:

- `notifications` — Laravel's standard notifications table
- `user_device_tokens` — Stores FCM tokens per user and device

#### `user_device_tokens` Schema

| Column | Type | Description |
|---|---|---|
| `user_id` | foreignId | References `users.id` |
| `device_token` | string | Unique FCM device token |
| `device_type` | enum | `android`, `ios`, `web` |
| `device_name` | string (nullable) | Optional device label |
| `is_active` | boolean | Whether token is active |
| `last_used_at` | timestamp (nullable) | Last delivery timestamp |

---

## Usage

### 1. Prepare Your User Model

Add the `Notifiable` trait and implement `routeNotificationForFirebase`:

```php
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public function routeNotificationForFirebase(): array
    {
        return $this->deviceTokens()
            ->where('is_active', true)
            ->pluck('device_token')
            ->toArray();
    }
}
```

---

### 2. Firebase + Database Notification (Combined)

Extend `FirebaseNotification` to send both push and database notifications at once:

```php
<?php

namespace App\Notifications;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebaseNotification;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebasePayload;

class OrderShipped extends FirebaseNotification
{
    public function __construct(private array $orderData)
    {
        $payload = new FirebasePayload();
        $payload->setData([
            'notification' => [
                'title' => 'Order Shipped',
                'body'  => "Your order #{$orderData['order_id']} has been shipped!",
            ],
            'data' => [
                'order_id' => (string) $orderData['order_id'],
                'type'     => 'order_shipped',
            ],
        ]);

        $payload->setAndroidConfiguration(['priority' => 'high']);

        $payload->setIOSConfiguration([
            'headers' => ['apns-priority' => '10'],
            'payload' => ['aps' => ['sound' => 'default', 'badge' => 1]],
        ]);

        parent::__construct($payload);
    }

    public function via($notifiable): array
    {
        return ['firebase', 'database'];
    }

    // Optional: customize what gets stored in the database
    public function toDatabase($notifiable): array
    {
        return [
            'order_id' => $this->orderData['order_id'],
            'message'  => 'Your order has been shipped',
        ];
    }
}
```

Send it:

```php
$user->notify(new OrderShipped(['order_id' => 12345]));
```

---

### 3. Firebase-Only Notification

```php
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebaseNotification;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebasePayload;

class PromoAlert extends FirebaseNotification
{
    public function __construct(string $message)
    {
        $payload = new FirebasePayload();
        $payload->setData([
            'notification' => ['title' => 'Special Offer', 'body' => $message],
        ]);
        parent::__construct($payload);
    }

    public function via($notifiable): array
    {
        return ['firebase'];
    }
}

$user->notify(new PromoAlert('Get 50% off today only!'));
```

---

### 4. Database-Only Notification

```php
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\DatabaseNotification;

$user->notify(new DatabaseNotification([
    'type'    => 'welcome',
    'message' => 'Welcome to the platform!',
]));
```

---

### 5. Reading Notifications

```php
// Get all notifications
$notifications = $user->notifications;

// Get only unread
$unread = $user->unreadNotifications;

// Mark all as read
$user->unreadNotifications->markAsRead();
```

---

## Advanced Usage

### Platform-Specific Payload Configuration

```php
$payload = new FirebasePayload();

$payload->setData([
    'notification' => ['title' => 'Hello', 'body' => 'World'],
    'data'         => ['deep_link' => 'app://home'],
]);

// Android
$payload->setAndroidConfiguration([
    'priority'     => 'high',
    'notification' => ['sound' => 'default', 'color' => '#FF5722'],
]);

// iOS
$payload->setIOSConfiguration([
    'headers' => ['apns-priority' => '10'],
    'payload' => [
        'aps' => ['sound' => 'default', 'badge' => 1],
    ],
]);
```

---

### Error Handling

Always wrap notifications in a try/catch for production use:

```php
try {
    $user->notify(new OrderShipped($orderData));
} catch (\RuntimeException $e) {
    Log::error('Notification failed', [
        'user_id' => $user->id,
        'error'   => $e->getMessage(),
    ]);
}
```

Common failure reasons:
- **Missing credentials** — credentials file path is wrong or file doesn't exist
- **Invalid FCM token** — token has expired or the app was uninstalled
- **Network error** — FCM service unreachable

> **Tip:** When an FCM token returns a `NOT_REGISTERED` error from Firebase, delete it from `user_device_tokens` to keep your token table clean.

---

### Extending the Package

You can implement the `Notification` interface to create fully custom channels:

```php
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\Notification;

class SmsNotification implements Notification
{
    public function __construct(private array $data) {}

    public function sendNotification(array $targets): void
    {
        // your SMS logic here
    }
}
```

---

## Architecture

```
src/
├── Channels/
│   ├── FirebaseChannel.php               # Sends to FCM
│   └── DatabaseChannel.php              # Stores in database
├── Contracts/
│   ├── Notification.php                 # Base notification interface
│   ├── Payload.php                      # Base payload class
│   ├── FirebasePayload.php              # Firebase-specific payload
│   ├── DatabasePayload.php              # Database-specific payload
│   ├── FirebaseNotification.php         # Firebase notification implementation
│   └── DatabaseNotification.php        # Database notification implementation
└── FirebaseNotificationServiceProvider.php
```

---

## Testing

```bash
# Run the test suite
composer test

# Run with coverage report
vendor/bin/phpunit --coverage-html coverage

# Run static analysis
composer psalm
```

---

## Security

- ❌ Never commit your Firebase credentials JSON to version control
- ✅ Add the credentials path to `.gitignore`
- ✅ Use environment variables to define the credentials path
- ✅ Validate all user input before building notification payloads
- ✅ Regularly rotate Firebase service account keys

---

## Contributing

Contributions, issues, and feature requests are welcome! Feel free to:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes (`git commit -m 'feat: add my feature'`)
4. Push to the branch (`git push origin feature/my-feature`)
5. Open a Pull Request

Please make sure your PR includes tests and passes `composer test` and `composer psalm`.

---

## Changelog

### v2.0.0
- Refactored to use Laravel's native notification channel architecture
- Added `via()`, `toFirebase()`, `toDatabase()` channel support
- Improved service provider with auto-discovery for Laravel 11+
- Removed `tymon/jwt-auth` conflict
- Added `tymon/jwt-auth` to dev dependencies to verify compatibility

### v1.1.2
- Added conflict declaration with `tymon/jwt-auth`

### v1.1.1
- Added `setAndroidConfiguration()` and `setIOSConfiguration()` to payload

### v1.1.0
- Improved payload contract structure

### v1.0.x
- Initial releases

---

## License

This package is open-source software licensed under the [MIT license](LICENSE).

---

## Credits

**Author:** Mahmoud Gamal
**Email:** [mgcofa@gmail.com](mailto:mgcofa@gmail.com)
**GitHub:** [github.com/Cofa12](https://github.com/Cofa12)

---

## Support

For bugs, questions, or feature requests, please [open an issue](https://github.com/Cofa12/Notification_via_firebase_and_database/issues) on GitHub.