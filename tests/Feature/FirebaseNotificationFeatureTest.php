<?php

namespace Tests\Feature;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebaseNotification;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebasePayload;
use PHPUnit\Framework\TestCase;

class FirebaseNotificationFeatureTest extends TestCase
{
    public function test_firebase_notification_creation_with_payload(): void
    {
        $payload = new FirebasePayload();
        $payload->setData([
            'notification' => [
                'title' => 'Test Notification',
                'body' => 'This is a test notification body'
            ],
            'data' => [
                'key1' => 'value1',
                'key2' => 'value2'
            ]
        ]);

        $notification = new class($payload) extends FirebaseNotification {};

        $this->assertInstanceOf(FirebaseNotification::class, $notification);
    }

    public function test_firebase_notification_payload_structure(): void
    {
        $payload = new FirebasePayload();
        $notificationData = [
            'notification' => [
                'title' => 'Order Update',
                'body' => 'Your order #12345 has been shipped'
            ],
            'data' => [
                'order_id' => '12345',
                'status' => 'shipped',
                'tracking_number' => 'TRK123456789'
            ]
        ];

        $payload->setData($notificationData);

        $result = $payload->getData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($notificationData, $result['data']);
    }

    public function test_firebase_notification_fails_without_credentials(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Firebase credentials file not found or not configured.');

        $payload = new FirebasePayload();
        $payload->setData([
            'notification' => [
                'title' => 'Test',
                'body' => 'Test message'
            ]
        ]);

        // Create concrete implementation for testing.yml
        $notification = new class($payload) extends FirebaseNotification {};

        // This should throw exception as credentials are not configured
        $notification->sendNotification(['token1', 'token2']);
    }

    public function test_firebase_payload_with_complex_data(): void
    {
        $payload = new FirebasePayload();
        $complexData = [
            'notification' => [
                'title' => 'Complex Notification',
                'body' => 'Notification with complex data'
            ],
            'data' => [
                'user_id' => 123,
                'metadata' => [
                    'source' => 'app',
                    'priority' => 'high',
                    'tags' => ['urgent', 'action_required']
                ],
                'timestamp' => time()
            ]
        ];

        $payload->setData($complexData);
        $result = $payload->getData();

        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($complexData, $result['data']);
    }

    public function test_firebase_notification_with_multiple_tokens(): void
    {
        $this->expectException(\RuntimeException::class);

        $payload = new FirebasePayload();
        $payload->setData([
            'notification' => [
                'title' => 'Broadcast',
                'body' => 'Message to multiple devices'
            ]
        ]);

        $notification = new class($payload) extends FirebaseNotification {};

        $tokens = [
            'device_token_1',
            'device_token_2',
            'device_token_3',
            'device_token_4',
            'device_token_5'
        ];

        $notification->sendNotification($tokens);
    }

    public function test_firebase_payload_can_be_updated(): void
    {
        $payload = new FirebasePayload();

        $initialData = [
            'notification' => [
                'title' => 'Initial Title',
                'body' => 'Initial body'
            ]
        ];

        $payload->setData($initialData);
        $result1 = $payload->getData();

        $updatedData = [
            'notification' => [
                'title' => 'Updated Title',
                'body' => 'Updated body'
            ]
        ];

        $payload->setData($updatedData);
        $result2 = $payload->getData();

        $this->assertNotEquals($result1['data'], $result2['data']);
        $this->assertEquals($updatedData, $result2['data']);
    }
}
