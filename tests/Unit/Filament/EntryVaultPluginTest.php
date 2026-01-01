<?php

use Filament\Panel;
use Mockery\MockInterface;
use Yannelli\EntryVault\Filament\EntryVaultPlugin;
use Yannelli\EntryVault\Filament\Resources\EntryCategoryResource;
use Yannelli\EntryVault\Filament\Resources\EntryResource;

test('plugin can be instantiated via make method', function () {
    $plugin = new EntryVaultPlugin;

    expect($plugin)->toBeInstanceOf(EntryVaultPlugin::class);
});

test('plugin has correct id', function () {
    $plugin = new EntryVaultPlugin;

    expect($plugin->getId())->toBe('entry-vault');
});

test('plugin has entry resource enabled by default', function () {
    $plugin = new EntryVaultPlugin;

    expect($plugin->hasEntryResource())->toBeTrue();
});

test('plugin has entry category resource enabled by default', function () {
    $plugin = new EntryVaultPlugin;

    expect($plugin->hasEntryCategoryResource())->toBeTrue();
});

test('plugin can disable entry resource', function () {
    $plugin = (new EntryVaultPlugin)
        ->entryResource(false);

    expect($plugin->hasEntryResource())->toBeFalse();
});

test('plugin can disable entry category resource', function () {
    $plugin = (new EntryVaultPlugin)
        ->entryCategoryResource(false);

    expect($plugin->hasEntryCategoryResource())->toBeFalse();
});

test('plugin can set custom entry resource class', function () {
    config()->set('entry-vault.filament.enabled', true);

    $plugin = (new EntryVaultPlugin)
        ->usingEntryResource(CustomEntryResource::class);

    /** @var MockInterface&Panel $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('resources')
        ->once()
        ->with(Mockery::on(function ($resources) {
            return in_array(CustomEntryResource::class, $resources);
        }));

    $plugin->register($panel);
});

test('plugin can set custom entry category resource class', function () {
    config()->set('entry-vault.filament.enabled', true);

    $plugin = (new EntryVaultPlugin)
        ->usingEntryCategoryResource(CustomEntryCategoryResource::class);

    /** @var MockInterface&Panel $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('resources')
        ->once()
        ->with(Mockery::on(function ($resources) {
            return in_array(CustomEntryCategoryResource::class, $resources);
        }));

    $plugin->register($panel);
});

test('plugin can set navigation group', function () {
    $plugin = (new EntryVaultPlugin)
        ->navigationGroup('My Custom Group');

    expect($plugin->getNavigationGroup())->toBe('My Custom Group');
});

test('plugin uses config navigation group when not set explicitly', function () {
    config()->set('entry-vault.filament.navigation_group', 'Config Group');

    $plugin = new EntryVaultPlugin;

    expect($plugin->getNavigationGroup())->toBe('Config Group');
});

test('plugin can set navigation sort', function () {
    $plugin = (new EntryVaultPlugin)
        ->navigationSort(5);

    expect($plugin->getNavigationSort())->toBe(5);
});

test('plugin uses config navigation sort when not set explicitly', function () {
    config()->set('entry-vault.filament.navigation_sort', 10);

    $plugin = new EntryVaultPlugin;

    expect($plugin->getNavigationSort())->toBe(10);
});

test('plugin registers both resources when enabled', function () {
    config()->set('entry-vault.filament.enabled', true);

    $plugin = new EntryVaultPlugin;

    /** @var MockInterface&Panel $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('resources')
        ->once()
        ->with(Mockery::on(function ($resources) {
            return count($resources) === 2
                && in_array(EntryResource::class, $resources)
                && in_array(EntryCategoryResource::class, $resources);
        }));

    $plugin->register($panel);
});

test('plugin registers no resources when both disabled', function () {
    config()->set('entry-vault.filament.enabled', true);

    $plugin = (new EntryVaultPlugin)
        ->entryResource(false)
        ->entryCategoryResource(false);

    /** @var MockInterface&Panel $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('resources')
        ->once()
        ->with([]);

    $plugin->register($panel);
});

test('plugin registers only entry resource when category is disabled', function () {
    config()->set('entry-vault.filament.enabled', true);

    $plugin = (new EntryVaultPlugin)
        ->entryCategoryResource(false);

    /** @var MockInterface&Panel $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('resources')
        ->once()
        ->with([EntryResource::class]);

    $plugin->register($panel);
});

test('plugin registers only category resource when entry is disabled', function () {
    config()->set('entry-vault.filament.enabled', true);

    $plugin = (new EntryVaultPlugin)
        ->entryResource(false);

    /** @var MockInterface&Panel $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('resources')
        ->once()
        ->with([EntryCategoryResource::class]);

    $plugin->register($panel);
});

test('plugin fluent api returns same instance', function () {
    $plugin = new EntryVaultPlugin;

    expect($plugin->entryResource(true))
        ->toBe($plugin)
        ->and($plugin->entryCategoryResource(true))
        ->toBe($plugin)
        ->and($plugin->navigationGroup('Test'))
        ->toBe($plugin)
        ->and($plugin->navigationSort(1))
        ->toBe($plugin)
        ->and($plugin->usingEntryResource(EntryResource::class))
        ->toBe($plugin)
        ->and($plugin->usingEntryCategoryResource(EntryCategoryResource::class))
        ->toBe($plugin);
});

test('plugin isEnabled returns false by default', function () {
    expect(EntryVaultPlugin::isEnabled())->toBeFalse();
});

test('plugin isEnabled respects config setting', function () {
    config()->set('entry-vault.filament.enabled', true);

    expect(EntryVaultPlugin::isEnabled())->toBeTrue();
});

test('plugin registers no resources when disabled via config', function () {
    config()->set('entry-vault.filament.enabled', false);

    $plugin = new EntryVaultPlugin;

    /** @var MockInterface&Panel $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldNotReceive('resources');

    $plugin->register($panel);
});

test('plugin registers resources when enabled via config', function () {
    config()->set('entry-vault.filament.enabled', true);

    $plugin = new EntryVaultPlugin;

    /** @var MockInterface&Panel $panel */
    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('resources')
        ->once()
        ->with(Mockery::on(function ($resources) {
            return count($resources) === 2;
        }));

    $plugin->register($panel);
});

// Stub classes for testing custom resource configuration
class CustomEntryResource extends EntryResource {}
class CustomEntryCategoryResource extends EntryCategoryResource {}
