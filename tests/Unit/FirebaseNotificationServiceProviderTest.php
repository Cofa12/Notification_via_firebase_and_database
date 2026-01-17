<?php

namespace Tests\Unit;

use Cofa\NotificationViaFirebaseAndDatabase\FirebaseNotificationServiceProvider;
use Illuminate\Console\Application;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\TestCase;

class FirebaseNotificationServiceProviderTest extends TestCase
{
    private FirebaseNotificationServiceProvider $provider;
    private $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = $this->createMock(Application::class);
        $this->provider = new FirebaseNotificationServiceProvider($this->app);
    }

    public function test_extends_service_provider(): void
    {
        $this->assertInstanceOf(ServiceProvider::class, $this->provider);
    }

    public function test_register_method_exists(): void
    {
        $this->assertTrue(method_exists($this->provider, 'register'));
    }

    public function test_boot_method_exists(): void
    {
        $this->assertTrue(method_exists($this->provider, 'boot'));
    }

    public function test_register_method_can_be_called(): void
    {
        // Mock the mergeConfigFrom method
        $this->provider = $this->getMockBuilder(FirebaseNotificationServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->onlyMethods(['mergeConfigFrom'])
            ->getMock();

        $this->provider->expects($this->once())
            ->method('mergeConfigFrom');

        $this->provider->register();
    }

    public function test_boot_publishes_config_when_running_in_console(): void
    {
        $this->app->expects($this->once())
            ->method('runningInConsole')
            ->willReturn(true);

        $this->app->expects($this->once())
            ->method('configPath')
            ->with('firebase-notification.php')
            ->willReturn('/path/to/config/firebase-notification.php');

        $this->provider = $this->getMockBuilder(FirebaseNotificationServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->onlyMethods(['publishes'])
            ->getMock();

        $this->provider->expects($this->once())
            ->method('publishes');

        $this->provider->boot();
    }

    public function test_boot_does_not_publish_when_not_running_in_console(): void
    {
        $this->app->expects($this->once())
            ->method('runningInConsole')
            ->willReturn(false);

        $this->provider = $this->getMockBuilder(FirebaseNotificationServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->onlyMethods(['publishes'])
            ->getMock();

        $this->provider->expects($this->never())
            ->method('publishes');

        $this->provider->boot();
    }
}
