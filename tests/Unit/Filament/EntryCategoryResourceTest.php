<?php

use Yannelli\EntryVault\Filament\Resources\EntryCategoryResource;
use Yannelli\EntryVault\Filament\Resources\EntryCategoryResource\Pages\CreateEntryCategory;
use Yannelli\EntryVault\Filament\Resources\EntryCategoryResource\Pages\EditEntryCategory;
use Yannelli\EntryVault\Filament\Resources\EntryCategoryResource\Pages\ListEntryCategories;
use Yannelli\EntryVault\Filament\Resources\EntryCategoryResource\Pages\ViewEntryCategory;
use Yannelli\EntryVault\Models\EntryCategory;

test('entry category resource uses correct model', function () {
    expect(EntryCategoryResource::getModel())->toBe(EntryCategory::class);
});

test('entry category resource uses custom model from config', function () {
    config()->set('entry-vault.models.category', CustomCategory::class);

    expect(EntryCategoryResource::getModel())->toBe(CustomCategory::class);
});

test('entry category resource has correct model label', function () {
    config()->set('entry-vault.filament.category_label', 'Category');

    expect(EntryCategoryResource::getModelLabel())->toBe('Category');
});

test('entry category resource uses custom model label from config', function () {
    config()->set('entry-vault.filament.category_label', 'Section');

    expect(EntryCategoryResource::getModelLabel())->toBe('Section');
});

test('entry category resource has correct plural model label', function () {
    config()->set('entry-vault.filament.category_plural_label', 'Categories');

    expect(EntryCategoryResource::getPluralModelLabel())->toBe('Categories');
});

test('entry category resource uses custom plural model label from config', function () {
    config()->set('entry-vault.filament.category_plural_label', 'Sections');

    expect(EntryCategoryResource::getPluralModelLabel())->toBe('Sections');
});

test('entry category resource has correct pages', function () {
    $pages = EntryCategoryResource::getPages();

    expect($pages)->toBeArray()
        ->toHaveCount(4)
        ->toHaveKey('index')
        ->toHaveKey('create')
        ->toHaveKey('view')
        ->toHaveKey('edit');
});

test('entry category resource index page is ListEntryCategories', function () {
    $pages = EntryCategoryResource::getPages();

    expect($pages['index']->getPage())->toBe(ListEntryCategories::class);
});

test('entry category resource create page is CreateEntryCategory', function () {
    $pages = EntryCategoryResource::getPages();

    expect($pages['create']->getPage())->toBe(CreateEntryCategory::class);
});

test('entry category resource view page is ViewEntryCategory', function () {
    $pages = EntryCategoryResource::getPages();

    expect($pages['view']->getPage())->toBe(ViewEntryCategory::class);
});

test('entry category resource edit page is EditEntryCategory', function () {
    $pages = EntryCategoryResource::getPages();

    expect($pages['edit']->getPage())->toBe(EditEntryCategory::class);
});

test('entry category resource has no relation managers', function () {
    $relations = EntryCategoryResource::getRelations();

    expect($relations)->toBeArray()
        ->toBeEmpty();
});

test('entry category resource has correct navigation icon', function () {
    $reflection = new ReflectionClass(EntryCategoryResource::class);
    $property = $reflection->getProperty('navigationIcon');

    expect($property->getDefaultValue())->toBe('heroicon-o-folder');
});

test('entry category resource has default navigation sort', function () {
    $reflection = new ReflectionClass(EntryCategoryResource::class);
    $property = $reflection->getProperty('navigationSort');

    expect($property->getDefaultValue())->toBe(2);
});

// Stub class for testing custom model configuration
class CustomCategory extends EntryCategory {}
