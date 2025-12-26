<?php

use Yannelli\EntryVault\Facades\EntryVault;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Models\EntryCategory;
use Yannelli\EntryVault\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create(['name' => 'Test User', 'email' => 'test@example.com']);
});

test('facade can create entry', function () {
    $entry = EntryVault::create([
        'title' => 'Facade Entry',
        'description' => 'Created via facade',
    ]);

    expect($entry)->toBeInstanceOf(Entry::class)
        ->and($entry->title)->toBe('Facade Entry');
});

test('facade can find by slug', function () {
    Entry::create(['title' => 'Find Me']);

    $found = EntryVault::findBySlug('find-me');

    expect($found)->not->toBeNull()
        ->and($found->title)->toBe('Find Me');
});

test('facade can find by uuid', function () {
    $entry = Entry::create(['title' => 'UUID Entry']);

    $found = EntryVault::findByUuid($entry->uuid);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($entry->id);
});

test('facade can get templates', function () {
    Entry::create(['title' => 'Regular']);
    Entry::create(['title' => 'Template', 'is_template' => true]);

    $templates = EntryVault::templates()->get();

    expect($templates)->toHaveCount(1)
        ->and($templates->first()->title)->toBe('Template');
});

test('facade can get system templates', function () {
    Entry::create(['title' => 'System Template', 'is_template' => true]);
    Entry::create([
        'title' => 'User Template',
        'is_template' => true,
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    $systemTemplates = EntryVault::systemTemplates()->get();

    expect($systemTemplates)->toHaveCount(1);
});

test('facade can get starters', function () {
    Entry::create(['title' => 'Starter', 'is_template' => true, 'is_featured' => true]);
    Entry::create(['title' => 'Not Featured', 'is_template' => true]);

    $starters = EntryVault::starters()->get();

    expect($starters)->toHaveCount(1)
        ->and($starters->first()->title)->toBe('Starter');
});

test('facade can get categories', function () {
    EntryCategory::create(['name' => 'System Cat', 'is_system' => true]);
    EntryCategory::create([
        'name' => 'User Cat',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    $categories = EntryVault::categories()->get();

    expect($categories)->toHaveCount(1);
});

test('facade can get categories for user', function () {
    EntryCategory::create(['name' => 'System', 'is_system' => true]);
    EntryCategory::create([
        'name' => 'User',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    $categories = EntryVault::categoriesFor($this->user)->get();

    expect($categories)->toHaveCount(2);
});

test('facade can get default category', function () {
    EntryCategory::create(['name' => 'Regular', 'is_system' => true]);
    EntryCategory::create(['name' => 'Default', 'is_system' => true, 'is_default' => true]);

    $default = EntryVault::defaultCategory();

    expect($default)->not->toBeNull()
        ->and($default->name)->toBe('Default');
});

test('facade can get accessible entries', function () {
    Entry::create([
        'title' => 'User Entry',
        'visibility' => 'private',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);
    Entry::create(['title' => 'Public Entry', 'visibility' => 'public']);

    $accessible = EntryVault::accessibleBy($this->user)->get();

    expect($accessible)->toHaveCount(2);
});

test('facade can get public entries', function () {
    Entry::create(['title' => 'Public', 'visibility' => 'public']);
    Entry::create(['title' => 'Private', 'visibility' => 'private']);

    $public = EntryVault::publicEntries()->get();

    expect($public)->toHaveCount(1);
});

test('facade can create from template', function () {
    $template = Entry::create(['title' => 'Template', 'is_template' => true]);

    $entry = EntryVault::createFromTemplate($template, [
        'title' => 'From Template',
        'owner' => $this->user,
    ]);

    expect($entry->title)->toBe('From Template')
        ->and($entry->template_id)->toBe($template->id);
});

test('facade returns correct model classes', function () {
    expect(EntryVault::getEntryModel())->toBe(\Yannelli\EntryVault\Models\Entry::class)
        ->and(EntryVault::getCategoryModel())->toBe(\Yannelli\EntryVault\Models\EntryCategory::class)
        ->and(EntryVault::getContentModel())->toBe(\Yannelli\EntryVault\Models\EntryContent::class);
});
