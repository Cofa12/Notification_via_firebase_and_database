<?php

namespace Tests\Unit\Contracts;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebaseNotification;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebasePayload;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\Notification;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FirebaseNotificationTest extends TestCase
{
    private FirebasePayload $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payload = new FirebasePayload();
        $this->payload->setData([
            'notification' => [
                'title' => 'Test',
                'body' => 'Test message'
            ]
        ]);
    }

    public function test_implements_notification_interface(): void
    {
        $notification = $this->createMockNotification();

        $this->assertInstanceOf(Notification::class, $notification);
    }

    public function test_constructor_accepts_firebase_payload(): void
    {
        $notification = $this->createMockNotification();

        $this->assertInstanceOf(FirebaseNotification::class, $notification);
    }

    public function test_send_notification_throws_exception_when_credentials_not_configured(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Firebase credentials file not found or not configured.');

        // Mock config to return null
        if (!function_exists('config')) {
            function config($key) {
                return null;
            }
        }

        $notification = $this->createMockNotification();
        $notification->sendNotification(['token1', 'token2']);
    }

    public function test_send_notification_throws_exception_when_credentials_file_does_not_exist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Firebase credentials file not found or not configured.');

        // Mock config to return non-existent path
        if (!function_exists('Tests\Unit\Contracts\config')) {
            function config($key) {
                return '/non/existent/path.json';
            }
        }

        $notification = $this->createMockNotification();
        $notification->sendNotification(['token1', 'token2']);
    }

    public function test_payload_is_stored_in_property(): void
    {
        $notification = $this->createMockNotification();

        $reflection = new \ReflectionClass($notification);
        $property = $reflection->getProperty('payload');
        $property->setAccessible(true);

        $this->assertInstanceOf(FirebasePayload::class, $property->getValue($notification));
    }

    private function createMockNotification(): FirebaseNotification
    {
        return new class($this->payload) extends FirebaseNotification {};
    }
}
