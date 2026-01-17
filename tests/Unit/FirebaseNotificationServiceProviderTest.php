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

        $this->app = new class {
            public function runningInConsole(): bool
            {
                return false;
            }
            public function configPath(string $path = ''): string
            {
                return '/fake/path/config';
            }
        };
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
        $app = new class {
            public int $runningInConsoleCallCount = 0;
            public int $configPathCallCount = 0;

            public function runningInConsole(): bool
            {
                $this->runningInConsoleCallCount++;
                return true;
            }

            public function configPath(string $path = ''): string
            {
                $this->configPathCallCount++;
                return '/path/to/config/firebase-notification.php';
            }
        };

        $provider = $this->getMockBuilder(FirebaseNotificationServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['publishes'])
            ->getMock();

        $provider->expects($this->once())
            ->method('publishes');

        $provider->boot();

        $this->assertEquals(1, $app->runningInConsoleCallCount);
        $this->assertEquals(1, $app->configPathCallCount);
    }

    public function test_boot_does_not_publish_when_not_running_in_console(): void
    {
        $app = new class {
            public int $runningInConsoleCallCount = 0;

            public function runningInConsole(): bool
            {
                $this->runningInConsoleCallCount++;
                return false;
            }

            public function configPath(string $path = ''): string
            {
                return '/fake/path/config';
            }
        };

        $provider = $this->getMockBuilder(FirebaseNotificationServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['publishes'])
            ->getMock();

        $provider->expects($this->never())
            ->method('publishes');

        $provider->boot();

        $this->assertEquals(1, $app->runningInConsoleCallCount);
    }
}
