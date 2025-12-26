<?php

use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Models\EntryCategory;
use Yannelli\EntryVault\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

test('can create an entry', function () {
    $entry = Entry::create([
        'title' => 'Test Entry',
        'description' => 'Test description',
    ]);

    expect($entry)->toBeInstanceOf(Entry::class)
        ->and($entry->title)->toBe('Test Entry')
        ->and($entry->description)->toBe('Test description')
        ->and($entry->uuid)->not->toBeNull()
        ->and($entry->slug)->toBe('test-entry');
});

test('generates unique slug for duplicate titles', function () {
    $entry1 = Entry::create(['title' => 'My Entry']);
    $entry2 = Entry::create(['title' => 'My Entry']);

    expect($entry1->slug)->toBe('my-entry')
        ->and($entry2->slug)->toBe('my-entry-1');
});

test('can create entry with owner', function () {
    $entry = Entry::create([
        'title' => 'User Entry',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect($entry->owner)->toBeInstanceOf(User::class)
        ->and($entry->owner->id)->toBe($this->user->id);
});

test('can update an entry', function () {
    $entry = Entry::create(['title' => 'Original Title']);
    $entry->update(['title' => 'Updated Title']);

    expect($entry->fresh()->title)->toBe('Updated Title');
});

test('can soft delete an entry', function () {
    $entry = Entry::create(['title' => 'To Delete']);
    $entry->delete();

    expect(Entry::find($entry->id))->toBeNull()
        ->and(Entry::withTrashed()->find($entry->id))->not->toBeNull();
});

test('can restore a soft deleted entry', function () {
    $entry = Entry::create(['title' => 'To Restore']);
    $entry->delete();
    $entry->restore();

    expect(Entry::find($entry->id))->not->toBeNull();
});

test('can assign entry to category', function () {
    $category = EntryCategory::create([
        'name' => 'Test Category',
        'is_system' => true,
    ]);

    $entry = Entry::create([
        'title' => 'Categorized Entry',
        'category_id' => $category->id,
    ]);

    expect($entry->category)->toBeInstanceOf(EntryCategory::class)
        ->and($entry->category->id)->toBe($category->id);
});

test('can add content to entry', function () {
    $entry = Entry::create(['title' => 'Entry with Content']);

    $content = $entry->contents()->create([
        'type' => 'markdown',
        'body' => '# Hello World',
        'order' => 0,
    ]);

    expect($entry->contents)->toHaveCount(1)
        ->and($content->body)->toBe('# Hello World');
});

test('can add multiple contents with ordering', function () {
    $entry = Entry::create(['title' => 'Multi Content Entry']);

    $entry->contents()->create(['type' => 'text', 'body' => 'First', 'order' => 0]);
    $entry->contents()->create(['type' => 'text', 'body' => 'Second', 'order' => 1]);
    $entry->contents()->create(['type' => 'text', 'body' => 'Third', 'order' => 2]);

    $contents = $entry->fresh()->contents;

    expect($contents)->toHaveCount(3)
        ->and($contents[0]->body)->toBe('First')
        ->and($contents[1]->body)->toBe('Second')
        ->and($contents[2]->body)->toBe('Third');
});

test('can find entry by slug', function () {
    $entry = Entry::create(['title' => 'Find Me']);

    $found = Entry::findBySlug('find-me');

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($entry->id);
});

test('can find entry by uuid', function () {
    $entry = Entry::create(['title' => 'UUID Entry']);

    $found = Entry::findByUuid($entry->uuid);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($entry->id);
});

test('slug is unique per owner scope', function () {
    $user2 = User::create(['name' => 'User 2', 'email' => 'user2@example.com']);

    $entry1 = Entry::create([
        'title' => 'Same Title',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    $entry2 = Entry::create([
        'title' => 'Same Title',
        'owner_type' => $user2->getMorphClass(),
        'owner_id' => $user2->id,
    ]);

    expect($entry1->slug)->toBe('same-title')
        ->and($entry2->slug)->toBe('same-title');
});

test('entry has keywords as array', function () {
    $entry = Entry::create([
        'title' => 'Keywords Entry',
        'keywords' => ['laravel', 'php', 'api'],
    ]);

    expect($entry->keywords)->toBeArray()
        ->and($entry->keywords)->toContain('laravel', 'php', 'api');
});
