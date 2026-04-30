<?php

namespace Tests;

use Cofa\NotificationViaFirebaseAndDatabase\Channels\FirebaseChannel;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebaseNotification;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebasePayload;
use Cofa\NotificationViaFirebaseAndDatabase\FirebaseNotificationServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase;

class FirebaseChannelTest extends TestCase
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

    public function test_send_calls_to_firebase_and_dispatches_to_fcm()
    {
        $notifiable = Mockery::mock();
        $notifiable->shouldReceive('routeNotificationFor')
            ->with('firebase', Mockery::any())
            ->andReturn(['token1']);

        $payload = new FirebasePayload();
        $payload->setData(['key' => 'value']);
        
        $notification = Mockery::mock(FirebaseNotification::class, [$payload])->makePartial();
        $notification->shouldReceive('toFirebase')
            ->once()
            ->with($notifiable)
            ->andReturn(['data' => ['key' => 'value']]);

        // Mock Firebase Messaging Contract
        $messaging = Mockery::mock(\Kreait\Firebase\Contract\Messaging::class);
        $messaging->shouldReceive('sendMulticast')
            ->once()
            ->with(Mockery::any(), ['token1'])
            ->andReturn(\Kreait\Firebase\Messaging\MulticastSendReport::withItems([]));

        // Use a partial mock of the channel to intercept getMessaging()
        $channel = Mockery::mock(FirebaseChannel::class)->makePartial();
        $channel->shouldAllowMockingProtectedMethods();
        $channel->shouldReceive('getMessaging')->andReturn($messaging);

        $channel->send($notifiable, $notification);
        
        $this->assertTrue(true);
    }
}
