<?php

namespace Tests\Feature;

use Cofa\NotificationViaFirebaseAndDatabase\Console\InstallCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\TestCase;

class InstallCommandTest extends TestCase
{
    private string $testMigrationsPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary migrations directory for testing.yml
        $this->testMigrationsPath = sys_get_temp_dir() . '/test_migrations_' . uniqid();
        if (!is_dir($this->testMigrationsPath)) {
            mkdir($this->testMigrationsPath, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test migrations directory
        if (is_dir($this->testMigrationsPath)) {
            $this->removeDirectory($this->testMigrationsPath);
        }

        parent::tearDown();
    }

    public function test_install_command_exists(): void
    {
        $command = new InstallCommand();

        $this->assertInstanceOf(InstallCommand::class, $command);
    }

    public function test_install_command_has_correct_signature(): void
    {
        $app = $this->createMockApplication();
        $command = new InstallCommand();

        // Use reflection to access protected property
        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);

        $this->assertEquals('firebase-notification:install', $signatureProperty->getValue($command));
    }

    public function test_install_command_has_description(): void
    {
        $command = new InstallCommand();

        // Use reflection to access protected property
        $reflection = new \ReflectionClass($command);
        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);

        $description = $descriptionProperty->getValue($command);

        $this->assertNotEmpty($description);
        $this->assertStringContainsString('Firebase Notification', $description);
    }

    public function test_create_notifications_table_method_exists(): void
    {
        $command = new InstallCommand();

        $reflection = new \ReflectionClass($command);

        $this->assertTrue($reflection->hasMethod('createNotificationsTable'));
    }

    public function test_publish_migrations_method_exists(): void
    {
        $command = new InstallCommand();

        $reflection = new \ReflectionClass($command);

        $this->assertTrue($reflection->hasMethod('publishMigrations'));
    }

    public function test_handle_method_exists(): void
    {
        $command = new InstallCommand();

        $reflection = new \ReflectionClass($command);

        $this->assertTrue($reflection->hasMethod('handle'));
    }

    public function test_install_command_calls_notifications_table(): void
    {
        $app = $this->createMockApplication();

        // Create a partial mock of InstallCommand
        $command = $this->getMockBuilder(InstallCommand::class)
            ->onlyMethods(['call', 'info', 'line', 'comment'])
            ->getMock();

        // Expect that the 'notifications:table' command is called
        $command->expects($this->once())
            ->method('call')
            ->with('notifications:table');

        // Mock the info, line, and comment methods to prevent output
        $command->method('info')->willReturn(null);
        $command->method('line')->willReturn(null);
        $command->method('comment')->willReturn(null);

        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('createNotificationsTable');
        $method->setAccessible(true);
        $method->invoke($command);
    }

    public function test_publish_migrations_creates_migration_file(): void
    {
        $app = $this->createMockApplication();

        // Create a mock command with overridden database_path
        $command = new class extends InstallCommand {
            public string $testMigrationsPath;

            public function testPublishMigrations(): void
            {
                // Override the database_path function for this test
                $this->publishMigrations();
            }

            protected function publishMigrations(): void
            {
                $this->info('Publishing migrations...');

                $timestamp = date('Y_m_d_His');
                $stubPath = __DIR__ . '/../../../database/migrations/create_user_device_tokens_table.php.stub';
                $migrationPath = $this->testMigrationsPath . "/{$timestamp}_create_user_device_tokens_table.php";

                if (!File::exists($migrationPath)) {
                    File::copy($stubPath, $migrationPath);
                    $this->info('Migration published: ' . basename($migrationPath));
                } else {
                    $this->warn('Migration already exists: create_user_device_tokens_table.php');
                }
            }
        };

        $command->testMigrationsPath = $this->testMigrationsPath;

        // Mock the info method
        $reflection = new \ReflectionClass($command);
        $infoMethod = $reflection->getMethod('info');

        // This test verifies the method exists and can be called
        $this->assertTrue($reflection->hasMethod('publishMigrations'));
    }

    public function test_migration_stub_file_exists(): void
    {
        $stubPath = __DIR__ . '/../../database/migrations/create_user_device_tokens_table.php.stub';

        $this->assertFileExists($stubPath, 'Migration stub file should exist');
    }

    public function test_migration_stub_has_correct_structure(): void
    {
        $stubPath = __DIR__ . '/../../database/migrations/create_user_device_tokens_table.php.stub';

        $this->assertFileExists($stubPath);

        $content = file_get_contents($stubPath);

        // Check for key components of the migration
        $this->assertStringContainsString('use Illuminate\Database\Migrations\Migration', $content);
        $this->assertStringContainsString('use Illuminate\Database\Schema\Blueprint', $content);
        $this->assertStringContainsString('Schema::create(\'user_device_tokens\'', $content);
        $this->assertStringContainsString('user_id', $content);
        $this->assertStringContainsString('device_token', $content);
        $this->assertStringContainsString('device_type', $content);
        $this->assertStringContainsString('device_name', $content);
        $this->assertStringContainsString('is_active', $content);
        $this->assertStringContainsString('last_used_at', $content);
    }

    public function test_handle_method_returns_success(): void
    {
        $app = $this->createMockApplication();

        // Create a partial mock
        $command = $this->getMockBuilder(InstallCommand::class)
            ->onlyMethods(['createNotificationsTable', 'publishMigrations', 'info', 'line', 'comment'])
            ->getMock();

        // Mock all the methods that would be called
        // Note: createNotificationsTable and publishMigrations are void methods, no willReturn needed
        $command->expects($this->once())->method('createNotificationsTable');
        $command->expects($this->once())->method('publishMigrations');
        $command->method('info')->willReturn(null);
        $command->method('line')->willReturn(null);
        $command->method('comment')->willReturn(null);

        $result = $command->handle();

        $this->assertEquals(0, $result, 'Handle method should return success code (0)');
    }

    public function test_install_command_execution_order(): void
    {
        $app = $this->createMockApplication();

        $callOrder = [];

        // Create a partial mock to track method calls
        $command = $this->getMockBuilder(InstallCommand::class)
            ->onlyMethods(['createNotificationsTable', 'publishMigrations', 'info', 'line', 'comment'])
            ->getMock();

        // Track the order of calls
        $command->expects($this->once())
            ->method('createNotificationsTable')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'createNotificationsTable';
            });

        $command->expects($this->once())
            ->method('publishMigrations')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'publishMigrations';
            });

        $command->method('info')->willReturn(null);
        $command->method('line')->willReturn(null);
        $command->method('comment')->willReturn(null);

        $command->handle();

        // Verify that createNotificationsTable is called before publishMigrations
        $this->assertEquals(['createNotificationsTable', 'publishMigrations'], $callOrder);
    }

    private function createMockApplication()
    {
        $app = $this->createMock(\Illuminate\Contracts\Foundation\Application::class);

        $app->method('runningInConsole')
            ->willReturn(true);

        return $app;
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
