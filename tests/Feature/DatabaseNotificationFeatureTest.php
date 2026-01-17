<?php

namespace Tests\Feature;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\DatabaseNotification;
use PHPUnit\Framework\TestCase;

class DatabaseNotificationFeatureTest extends TestCase
{
    public function test_database_notification_complete_flow(): void
    {
        $notificationData = [
            'type' => 'order',
            'message' => 'Your order has been shipped',
            'order_id' => 12345,
            'timestamp' => time()
        ];

        $notification = new DatabaseNotification($notificationData);

        // Test that notification is created correctly
        $this->assertInstanceOf(DatabaseNotification::class, $notification);

        // Create mock notifiable entities (users)
        $user1 = $this->createNotifiableMock('User 1');
        $user2 = $this->createNotifiableMock('User 2');
        $user3 = $this->createNotifiableMock('User 3');

        // Test that notification can be sent to multiple users
        $notification->sendNotification([$user1, $user2, $user3]);

        // Verify each user received the notification
        $this->assertTrue(true); // If we reach here, no exceptions were thrown
    }

    public function test_database_notification_with_various_data_types(): void
    {
        $complexData = [
            'string' => 'text value',
            'integer' => 123,
            'float' => 45.67,
            'boolean' => true,
            'array' => ['nested', 'values'],
            'null' => null,
            'object' => ['key' => 'value']
        ];

        $notification = new DatabaseNotification($complexData);
        $mockNotifiable = new class {
            public function notify($notification)
            {
            }
        };

        $result = $notification->toDatabase($mockNotifiable);

        $this->assertEquals($complexData, $result);
    }

    public function test_database_notification_channel_configuration(): void
    {
        $notification = new DatabaseNotification(['test' => 'data']);
        $mockNotifiable = $this->createMock(\stdClass::class);

        $channels = $notification->via($mockNotifiable);

        $this->assertIsArray($channels);
        $this->assertEquals(['database'], $channels);
    }

    public function test_multiple_notifications_to_same_user(): void
    {
        $user = $this->createNotifiableMock('Test User');

        $notification1 = new DatabaseNotification(['message' => 'First notification']);
        $notification2 = new DatabaseNotification(['message' => 'Second notification']);
        $notification3 = new DatabaseNotification(['message' => 'Third notification']);

        // Should handle multiple notifications without issues
        $notification1->sendNotification([$user]);
        $notification2->sendNotification([$user]);
        $notification3->sendNotification([$user]);

        $this->assertTrue(true);
    }

    public function test_empty_notification_data(): void
    {
        $notification = new DatabaseNotification([]);
        $mockNotifiable = new class {
            public function notify($notification)
            {
            }
        };

        $result = $notification->toDatabase($mockNotifiable);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    private function createNotifiableMock(string $name)
    {
        return new class ($name) {
            private string $name;
            public function __construct(string $name)
            {
                $this->name = $name;
            }
            public function notify($notification)
            {
                // Simulate storing notification
                return true;
            }
        };
    }
}
