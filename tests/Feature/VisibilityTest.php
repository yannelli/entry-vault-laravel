<?php

use Yannelli\EntryVault\Enums\EntryVisibility;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Tests\Models\Team;
use Yannelli\EntryVault\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create(['name' => 'Test User', 'email' => 'test@example.com']);
    $this->otherUser = User::create(['name' => 'Other User', 'email' => 'other@example.com']);
    $this->team = Team::create(['name' => 'Test Team']);
    $this->user->teams()->attach($this->team);
});

test('can create public entry', function () {
    $entry = Entry::create([
        'title' => 'Public Entry',
        'visibility' => EntryVisibility::PUBLIC->value,
    ]);

    expect($entry->isPublic())->toBeTrue()
        ->and($entry->isPrivate())->toBeFalse();
});

test('can create private entry', function () {
    $entry = Entry::create([
        'title' => 'Private Entry',
        'visibility' => EntryVisibility::PRIVATE->value,
    ]);

    expect($entry->isPrivate())->toBeTrue()
        ->and($entry->isPublic())->toBeFalse();
});

test('can create team visible entry', function () {
    $entry = Entry::create([
        'title' => 'Team Entry',
        'visibility' => EntryVisibility::TEAM->value,
        'team_type' => $this->team->getMorphClass(),
        'team_id' => $this->team->id,
    ]);

    expect($entry->isTeamVisible())->toBeTrue();
});

test('visibility scopes filter correctly', function () {
    Entry::create(['title' => 'Public', 'visibility' => 'public']);
    Entry::create(['title' => 'Private', 'visibility' => 'private']);
    Entry::create(['title' => 'Team', 'visibility' => 'team']);

    expect(Entry::public()->count())->toBe(1)
        ->and(Entry::private()->count())->toBe(1)
        ->and(Entry::teamVisible()->count())->toBe(1);
});

test('public entries are accessible by everyone', function () {
    $entry = Entry::create([
        'title' => 'Public Entry',
        'visibility' => 'public',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect($entry->isAccessibleBy($this->otherUser))->toBeTrue();
});

test('private entries are only accessible by owner', function () {
    $entry = Entry::create([
        'title' => 'Private Entry',
        'visibility' => 'private',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect($entry->isAccessibleBy($this->user))->toBeTrue()
        ->and($entry->isAccessibleBy($this->otherUser))->toBeFalse();
});

test('team entries are accessible by team members', function () {
    $entry = Entry::create([
        'title' => 'Team Entry',
        'visibility' => 'team',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
        'team_type' => $this->team->getMorphClass(),
        'team_id' => $this->team->id,
    ]);

    // User is a team member
    expect($entry->isAccessibleBy($this->user))->toBeTrue();

    // Other user is not a team member
    expect($entry->isAccessibleBy($this->otherUser))->toBeFalse();
});

test('visibleTo scope returns correct entries', function () {
    // Public entry owned by other user
    Entry::create([
        'title' => 'Public by Other',
        'visibility' => 'public',
        'owner_type' => $this->otherUser->getMorphClass(),
        'owner_id' => $this->otherUser->id,
    ]);

    // Private entry owned by current user
    Entry::create([
        'title' => 'Private by User',
        'visibility' => 'private',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    // Private entry owned by other user (should not be visible)
    Entry::create([
        'title' => 'Private by Other',
        'visibility' => 'private',
        'owner_type' => $this->otherUser->getMorphClass(),
        'owner_id' => $this->otherUser->id,
    ]);

    $visibleEntries = Entry::visibleTo($this->user)->get();

    expect($visibleEntries)->toHaveCount(2)
        ->and($visibleEntries->pluck('title')->toArray())
        ->toContain('Public by Other', 'Private by User');
});

test('default visibility is private', function () {
    config()->set('entry-vault.default_visibility', 'private');

    $entry = Entry::create(['title' => 'Default Visibility']);

    expect($entry->visibility)->toBe('private');
});

test('visibility enum has labels', function () {
    expect(EntryVisibility::PUBLIC->label())->toBe('Public')
        ->and(EntryVisibility::PRIVATE->label())->toBe('Private')
        ->and(EntryVisibility::TEAM->label())->toBe('Team');
});
