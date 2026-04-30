<?php

namespace Tests;

use Cofa\NotificationViaFirebaseAndDatabase\Channels\DatabaseChannel;
use Cofa\NotificationViaFirebaseAndDatabase\FirebaseNotificationServiceProvider;
use Illuminate\Notifications\Notification;
use Mockery;
use Orchestra\Testbench\TestCase;

class DatabaseChannelTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [FirebaseNotificationServiceProvider::class];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_send_calls_to_database_and_saves_to_table()
    {
        $notifiable = Mockery::mock();
        $notification = Mockery::mock(Notification::class);
        $notification->id = 'test-id';
        
        $notification->shouldReceive('toDatabase')
            ->once()
            ->with($notifiable)
            ->andReturn(['test' => 'data']);

        $builder = Mockery::mock();
        $builder->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['id'] === 'test-id' && 
                       $data['type'] === get_class(Mockery::mock(Notification::class)) && 
                       $data['data'] === ['test' => 'data'];
            }));

        $notifiable->shouldReceive('routeNotificationFor')
            ->once()
            ->with('database', $notification)
            ->andReturn($builder);

        $channel = new DatabaseChannel();
        $channel->send($notifiable, $notification);
        
        $this->assertTrue(true);
    }
}
