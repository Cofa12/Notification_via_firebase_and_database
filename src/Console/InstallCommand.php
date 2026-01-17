<?php

namespace Cofa\NotificationViaFirebaseAndDatabase\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase-notification:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Firebase Notification package migrations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Firebase Notification Package...');

        // Create Laravel notifications table
        $this->createNotificationsTable();

        // Publish migrations
        $this->publishMigrations();

        $this->info('Firebase Notification Package installed successfully!');
        $this->line('');
        $this->comment('Next steps:');
        $this->line('1. Run: php artisan migrate');
        $this->line('2. Configure Firebase credentials in config/firebase-notification.php');
        $this->line('3. Run: php artisan vendor:publish --tag=firebase-notification-config');

        return self::SUCCESS;
    }

    /**
     * Create Laravel notifications table.
     */
    protected function createNotificationsTable(): void
    {
        $this->info('Creating notifications table...');

        $this->call('notifications:table');
    }

    /**
     * Publish package migrations.
     */
    protected function publishMigrations(): void
    {
        $this->info('Publishing migrations...');

        $timestamp = date('Y_m_d_His');
        $stubPath = __DIR__ . '/../../database/migrations/create_user_device_tokens_table.php.stub';
        $migrationPath = database_path("migrations/{$timestamp}_create_user_device_tokens_table.php");

        if (!File::exists($migrationPath)) {
            File::copy($stubPath, $migrationPath);
            $this->info('Migration published: ' . basename($migrationPath));
        } else {
            $this->warn('Migration already exists: create_user_device_tokens_table.php');
        }
    }
}
