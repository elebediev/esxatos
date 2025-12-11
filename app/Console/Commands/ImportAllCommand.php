<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportAllCommand extends Command
{
    protected $signature = 'import:all {--fresh : Clear all data before import}';
    protected $description = 'Import all data from Drupal database';

    public function handle(): int
    {
        $this->info('=== Starting full import from Drupal ===');
        $this->newLine();

        if ($this->option('fresh')) {
            if ($this->confirm('This will delete all existing data. Are you sure?')) {
                $this->info('Clearing existing data...');
                $this->call('migrate:fresh', ['--force' => true]);
                $this->newLine();
            } else {
                $this->info('Cancelled.');
                return Command::SUCCESS;
            }
        }

        // Step 1: Import users
        $this->info('Step 1/3: Importing users...');
        $this->call('import:users');
        $this->newLine();

        // Step 2: Import categories
        $this->info('Step 2/3: Importing categories...');
        $this->call('import:categories');
        $this->newLine();

        // Step 3: Import books
        $this->info('Step 3/3: Importing books...');
        $this->call('import:books');
        $this->newLine();

        $this->info('=== Import completed! ===');

        // Show summary
        $this->table(
            ['Entity', 'Count'],
            [
                ['Users', \App\Models\User::count()],
                ['Categories', \App\Models\Category::count()],
                ['Books', \App\Models\Book::count()],
                ['Book Files', \App\Models\BookFile::count()],
                ['URL Redirects', \App\Models\UrlRedirect::count()],
            ]
        );

        return Command::SUCCESS;
    }
}
