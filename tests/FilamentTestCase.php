<?php

namespace Yannelli\EntryVault\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Yannelli\EntryVault\EntryVaultServiceProvider;

/**
 * Simplified test case for Filament configuration tests.
 * Does not set up database migrations.
 */
class FilamentTestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            EntryVaultServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set up Entry Vault config without database
        config()->set('entry-vault.user_model', 'Yannelli\\EntryVault\\Tests\\Models\\User');
        config()->set('entry-vault.team_model', 'Yannelli\\EntryVault\\Tests\\Models\\Team');
    }
}
