<?php

use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Models\EntryCategory;
use Yannelli\EntryVault\Tests\Models\Team;
use Yannelli\EntryVault\Tests\Models\User;
use Yannelli\EntryVault\Transitions\ArchiveTransition;
use Yannelli\EntryVault\Transitions\PublishTransition;

beforeEach(function () {
    $this->user = User::create(['name' => 'Test User', 'email' => 'test@example.com']);
    $this->team = Team::create(['name' => 'Test Team']);
});

test('HasEntries trait provides entries relationship', function () {
    Entry::create([
        'title' => 'User Entry 1',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);
    Entry::create([
        'title' => 'User Entry 2',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect($this->user->entries)->toHaveCount(2);
});

test('HasEntries trait provides draft entries', function () {
    Entry::create([
        'title' => 'Draft Entry',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);
    $published = Entry::create([
        'title' => 'Published Entry',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);
    (new PublishTransition($published))->handle();

    expect($this->user->draftEntries)->toHaveCount(1)
        ->and($this->user->publishedEntries)->toHaveCount(1);
});

test('HasEntries trait provides archived entries', function () {
    $entry = Entry::create([
        'title' => 'To Archive',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);
    (new ArchiveTransition($entry))->handle();

    expect($this->user->archivedEntries)->toHaveCount(1);
});

test('HasEntries trait provides entry templates', function () {
    Entry::create([
        'title' => 'Regular Entry',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);
    Entry::create([
        'title' => 'Template Entry',
        'is_template' => true,
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect($this->user->entryTemplates)->toHaveCount(1);
});

test('HasEntryCategories trait provides categories relationship', function () {
    EntryCategory::create([
        'name' => 'User Category 1',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);
    EntryCategory::create([
        'name' => 'User Category 2',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect($this->user->entryCategories)->toHaveCount(2);
});

test('HasEntryCategories provides default category', function () {
    EntryCategory::create([
        'name' => 'Regular',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);
    EntryCategory::create([
        'name' => 'Default',
        'is_default' => true,
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect($this->user->defaultEntryCategory()->name)->toBe('Default');
});

test('Team can have entries', function () {
    Entry::create([
        'title' => 'Team Entry',
        'owner_type' => $this->team->getMorphClass(),
        'owner_id' => $this->team->id,
    ]);

    expect($this->team->entries)->toHaveCount(1);
});

test('Team can have categories', function () {
    EntryCategory::create([
        'name' => 'Team Category',
        'owner_type' => $this->team->getMorphClass(),
        'owner_id' => $this->team->id,
    ]);

    expect($this->team->entryCategories)->toHaveCount(1);
});

test('HasOwner trait isOwnedBy works correctly', function () {
    $entry = Entry::create([
        'title' => 'Owned Entry',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    $otherUser = User::create(['name' => 'Other', 'email' => 'other@example.com']);

    expect($entry->isOwnedBy($this->user))->toBeTrue()
        ->and($entry->isOwnedBy($otherUser))->toBeFalse();
});

test('HasOwner trait isSystem works correctly', function () {
    $systemEntry = Entry::create(['title' => 'System Entry']);
    $userEntry = Entry::create([
        'title' => 'User Entry',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect($systemEntry->isSystem())->toBeTrue()
        ->and($userEntry->isSystem())->toBeFalse();
});

test('HasTeam trait works correctly', function () {
    $entry = Entry::create([
        'title' => 'Team Entry',
        'team_type' => $this->team->getMorphClass(),
        'team_id' => $this->team->id,
    ]);

    expect($entry->hasTeam())->toBeTrue()
        ->and($entry->belongsToTeam($this->team))->toBeTrue();

    $otherTeam = Team::create(['name' => 'Other Team']);
    expect($entry->belongsToTeam($otherTeam))->toBeFalse();
});
