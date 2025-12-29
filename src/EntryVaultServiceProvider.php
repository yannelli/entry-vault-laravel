<?php

namespace Yannelli\EntryVault;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Yannelli\EntryVault\Commands\InstallEntryVaultCommand;
use Yannelli\EntryVault\Commands\SeedCategoriesCommand;

class EntryVaultServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('entry-vault')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_entry_versions_table',
                'create_entry_categories_table',
                'create_entries_table',
                'create_entry_contents_table',
            ])
            ->hasCommands([
                InstallEntryVaultCommand::class,
                SeedCategoriesCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(EntryVault::class, function () {
            return new EntryVault;
        });
    }

    public function packageBooted(): void
    {
        // Set the versionable package to use our custom version model
        config(['versionable.version_model' => config('entry-vault.models.version')]);
    }
}
