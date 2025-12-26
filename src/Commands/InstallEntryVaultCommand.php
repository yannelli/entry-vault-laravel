<?php

namespace Yannelli\EntryVault\Commands;

use Illuminate\Console\Command;

class InstallEntryVaultCommand extends Command
{
    public $signature = 'entry-vault:install {--force : Overwrite existing files}';

    public $description = 'Install Entry Vault package';

    public function handle(): int
    {
        $this->info('Installing Entry Vault...');

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'entry-vault-config',
            '--force' => $this->option('force'),
        ]);

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'entry-vault-migrations',
            '--force' => $this->option('force'),
        ]);

        // Ask to run migrations
        if ($this->confirm('Would you like to run the migrations now?', true)) {
            $this->call('migrate');
        }

        // Ask to seed default categories
        if (config('entry-vault.seed_default_categories', true)) {
            if ($this->confirm('Would you like to seed the default categories?', true)) {
                $this->call('entry-vault:seed-categories');
            }
        }

        $this->newLine();
        $this->info('Entry Vault has been installed successfully!');

        return self::SUCCESS;
    }
}
