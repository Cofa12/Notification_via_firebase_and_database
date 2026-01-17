<?php

namespace Tests\Feature;

use Cofa\NotificationViaFirebaseAndDatabase\FirebaseNotificationServiceProvider;
use Illuminate\Console\Application;
use PHPUnit\Framework\TestCase;

class ServiceProviderIntegrationTest extends TestCase
{
    public function test_service_provider_registration_flow(): void
    {
        $app = $this->createMockApplication();

        $provider = new FirebaseNotificationServiceProvider($app);

        $this->assertInstanceOf(FirebaseNotificationServiceProvider::class, $provider);

        $provider = $this->getMockBuilder(FirebaseNotificationServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['mergeConfigFrom'])
            ->getMock();

        $provider->expects($this->once())
            ->method('mergeConfigFrom');

        $provider->register();
    }

    public function test_service_provider_boot_in_console(): void
    {
        $app = $this->createMockApplication(true);

        $provider = $this->getMockBuilder(FirebaseNotificationServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['publishes'])
            ->getMock();

        $provider->expects($this->once())
            ->method('publishes')
            ->with(
                $this->callback(function ($config) {
                    return is_array($config) && count($config) === 1;
                }),
                'firebase-notification-config'
            );

        $provider->boot();
    }

    public function test_service_provider_boot_in_http_request(): void
    {
        $app = $this->createMockApplication(false);

        $provider = $this->getMockBuilder(FirebaseNotificationServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['publishes'])
            ->getMock();

        $provider->expects($this->never())
            ->method('publishes');

        $provider->boot();
    }

    public function test_config_file_structure(): void
    {
        $configPath = __DIR__ . '/../../config/firebase-notification.php';

        $this->assertFileExists($configPath);

        $config = include $configPath;

        $this->assertIsArray($config);
        $this->assertArrayHasKey('firebase', $config);
        $this->assertArrayHasKey('credentials', $config['firebase']);
    }

//    public function test_service_provider_lifecycle(): void
//    {
//        $app = $this->createMockApplication();
//
//        $provider = $this->getMockBuilder(FirebaseNotificationServiceProvider::class)
//            ->setConstructorArgs([$app])
//            ->onlyMethods(['mergeConfigFrom', 'publishes'])
//            ->getMock();
//
//        $provider->expects($this->once())
//            ->method('mergeConfigFrom');
//
//        $provider->register();
//
//        $provider->expects($this->once())
//            ->method('runningInConsole')
//            ->willReturn(false);
//
//        $provider->boot();
//
//        $this->assertTrue(true);
//    }

    private function createMockApplication(bool $runningInConsole = false)
    {
        return new class ($runningInConsole) {
            private bool $runningInConsole;

            public function __construct(bool $runningInConsole)
            {
                $this->runningInConsole = $runningInConsole;
            }

            public function runningInConsole(): bool
            {
                return $this->runningInConsole;
            }

            public function configPath(string $path = ''): string
            {
                return '/fake/path/config';
            }
        };
    }
}
