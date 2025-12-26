<?php

namespace Yannelli\EntryVault\Commands;

use Illuminate\Console\Command;
use Yannelli\EntryVault\Database\Seeders\DefaultCategoriesSeeder;

class SeedCategoriesCommand extends Command
{
    public $signature = 'entry-vault:seed-categories {--force : Overwrite existing system categories}';

    public $description = 'Seed default system categories for Entry Vault';

    public function handle(): int
    {
        $this->info('Seeding default categories...');

        $seeder = new DefaultCategoriesSeeder;
        $seeder->setCommand($this);
        $seeder->run($this->option('force'));

        $this->info('Default categories have been seeded!');

        return self::SUCCESS;
    }
}
