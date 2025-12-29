<?php

use Yannelli\EntryVault\Filament\Resources\EntryResource\RelationManagers\ContentsRelationManager;

test('contents relation manager has correct relationship', function () {
    $reflection = new ReflectionClass(ContentsRelationManager::class);

    $property = $reflection->getProperty('relationship');

    expect($property->getDefaultValue())->toBe('contents');
});

test('contents relation manager has correct title', function () {
    $reflection = new ReflectionClass(ContentsRelationManager::class);

    $property = $reflection->getProperty('title');

    expect($property->getDefaultValue())->toBe('Content Blocks');
});

test('contents relation manager has correct record title attribute', function () {
    $reflection = new ReflectionClass(ContentsRelationManager::class);

    $property = $reflection->getProperty('recordTitleAttribute');

    expect($property->getDefaultValue())->toBe('type');
});

test('contents relation manager class exists and extends relation manager', function () {
    expect(ContentsRelationManager::class)
        ->toExtend(\Filament\Resources\RelationManagers\RelationManager::class);
});

test('contents relation manager has form method', function () {
    $reflection = new ReflectionClass(ContentsRelationManager::class);

    expect($reflection->hasMethod('form'))->toBeTrue();
});

test('contents relation manager has table method', function () {
    $reflection = new ReflectionClass(ContentsRelationManager::class);

    expect($reflection->hasMethod('table'))->toBeTrue();
});
