<?php

use Yannelli\EntryVault\Filament\Resources\EntryResource;
use Yannelli\EntryVault\Filament\Resources\EntryResource\Pages\CreateEntry;
use Yannelli\EntryVault\Filament\Resources\EntryResource\Pages\EditEntry;
use Yannelli\EntryVault\Filament\Resources\EntryResource\Pages\ListEntries;
use Yannelli\EntryVault\Filament\Resources\EntryResource\Pages\ViewEntry;
use Yannelli\EntryVault\Filament\Resources\EntryResource\RelationManagers\ContentsRelationManager;
use Yannelli\EntryVault\Models\Entry;

test('entry resource uses correct model', function () {
    expect(EntryResource::getModel())->toBe(Entry::class);
});

test('entry resource uses custom model from config', function () {
    config()->set('entry-vault.models.entry', CustomEntry::class);

    expect(EntryResource::getModel())->toBe(CustomEntry::class);
});

test('entry resource has correct model label', function () {
    config()->set('entry-vault.filament.entry_label', 'Entry');

    expect(EntryResource::getModelLabel())->toBe('Entry');
});

test('entry resource uses custom model label from config', function () {
    config()->set('entry-vault.filament.entry_label', 'Article');

    expect(EntryResource::getModelLabel())->toBe('Article');
});

test('entry resource has correct plural model label', function () {
    config()->set('entry-vault.filament.entry_plural_label', 'Entries');

    expect(EntryResource::getPluralModelLabel())->toBe('Entries');
});

test('entry resource uses custom plural model label from config', function () {
    config()->set('entry-vault.filament.entry_plural_label', 'Articles');

    expect(EntryResource::getPluralModelLabel())->toBe('Articles');
});

test('entry resource has correct pages', function () {
    $pages = EntryResource::getPages();

    expect($pages)->toBeArray()
        ->toHaveCount(4)
        ->toHaveKey('index')
        ->toHaveKey('create')
        ->toHaveKey('view')
        ->toHaveKey('edit');
});

test('entry resource index page is ListEntries', function () {
    $pages = EntryResource::getPages();

    expect($pages['index']->getPage())->toBe(ListEntries::class);
});

test('entry resource create page is CreateEntry', function () {
    $pages = EntryResource::getPages();

    expect($pages['create']->getPage())->toBe(CreateEntry::class);
});

test('entry resource view page is ViewEntry', function () {
    $pages = EntryResource::getPages();

    expect($pages['view']->getPage())->toBe(ViewEntry::class);
});

test('entry resource edit page is EditEntry', function () {
    $pages = EntryResource::getPages();

    expect($pages['edit']->getPage())->toBe(EditEntry::class);
});

test('entry resource has contents relation manager', function () {
    $relations = EntryResource::getRelations();

    expect($relations)->toBeArray()
        ->toContain(ContentsRelationManager::class);
});

test('entry resource has correct navigation icon', function () {
    $reflection = new ReflectionClass(EntryResource::class);
    $property = $reflection->getProperty('navigationIcon');

    expect($property->getDefaultValue())->toBe('heroicon-o-document-text');
});

test('entry resource has default navigation sort', function () {
    $reflection = new ReflectionClass(EntryResource::class);
    $property = $reflection->getProperty('navigationSort');

    expect($property->getDefaultValue())->toBe(1);
});

// Stub class for testing custom model configuration
class CustomEntry extends Entry {}
