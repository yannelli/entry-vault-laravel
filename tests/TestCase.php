<?php

namespace Yannelli\EntryVault\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Yannelli\EntryVault\EntryVaultServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Yannelli\\EntryVault\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            EntryVaultServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        // Set up Entry Vault config
        config()->set('entry-vault.user_model', 'Yannelli\\EntryVault\\Tests\\Models\\User');
        config()->set('entry-vault.team_model', 'Yannelli\\EntryVault\\Tests\\Models\\Team');

        // Set up versionable config
        config()->set('versionable.version_model', \Overtrue\LaravelVersionable\Version::class);
        config()->set('versionable.user_model', 'Yannelli\\EntryVault\\Tests\\Models\\User');
        config()->set('versionable.user_foreign_key', 'user_id');
        config()->set('versionable.keep_versions', 0);
        config()->set('versionable.uuid', false);
    }

    protected function defineDatabaseMigrations(): void
    {
        // Create users table for testing
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        // Create teams table for testing
        $this->app['db']->connection()->getSchemaBuilder()->create('teams', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Create team_user pivot table
        $this->app['db']->connection()->getSchemaBuilder()->create('team_user', function ($table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Run versionable migration
        $this->app['db']->connection()->getSchemaBuilder()->create('versions', function ($table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->morphs('versionable');
            $table->json('contents')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Run package migrations
        (include __DIR__.'/../database/migrations/create_entry_categories_table.php.stub')->up();
        (include __DIR__.'/../database/migrations/create_entries_table.php.stub')->up();
        (include __DIR__.'/../database/migrations/create_entry_contents_table.php.stub')->up();
    }
}
