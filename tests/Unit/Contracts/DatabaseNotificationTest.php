<?php

namespace Tests\Unit\Contracts;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\DatabaseNotification;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\Notification;
use Illuminate\Notifications\Notification as LaravelNotification;
use PHPUnit\Framework\TestCase;

class DatabaseNotificationTest extends TestCase
{
    private array $testData;
    private DatabaseNotification $notification;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testData = [
            'type' => 'message',
            'content' => 'Test notification',
            'user_id' => 123
        ];
        $this->notification = new DatabaseNotification($this->testData);
    }

    public function test_extends_laravel_notification(): void
    {
        $this->assertInstanceOf(LaravelNotification::class, $this->notification);
    }

    public function test_implements_notification_interface(): void
    {
        $this->assertInstanceOf(Notification::class, $this->notification);
    }

    public function test_constructor_accepts_data_array(): void
    {
        $data = ['key' => 'value'];
        $notification = new DatabaseNotification($data);

        $this->assertInstanceOf(DatabaseNotification::class, $notification);
    }

    public function test_via_method_returns_database_channel(): void
    {
        $notifiable = $this->createMock(\stdClass::class);

        $channels = $this->notification->via($notifiable);

        $this->assertIsArray($channels);
        $this->assertContains('database', $channels);
        $this->assertCount(1, $channels);
    }

    public function test_to_database_method_returns_stored_data(): void
    {
        $notifiable = $this->createMock(\stdClass::class);

        $result = $this->notification->toDatabase($notifiable);

        $this->assertEquals($this->testData, $result);
    }

    public function test_send_notification_calls_notify_on_each_target(): void
    {
        $target1 = $this->createMock(\stdClass::class);
        $target1->expects($this->once())
            ->method('notify')
            ->with($this->notification);

        $target2 = $this->createMock(\stdClass::class);
        $target2->expects($this->once())
            ->method('notify')
            ->with($this->notification);

        $this->notification->sendNotification([$target1, $target2]);
    }

    public function test_send_notification_with_empty_targets(): void
    {
        // Should not throw any exceptions
        $this->notification->sendNotification([]);

        $this->assertTrue(true);
    }

    public function test_send_notification_with_single_target(): void
    {
        $target = $this->createMock(\stdClass::class);
        $target->expects($this->once())
            ->method('notify')
            ->with($this->notification);

        $this->notification->sendNotification([$target]);
    }
}
