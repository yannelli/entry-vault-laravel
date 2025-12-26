<?php

use Yannelli\EntryVault\Facades\EntryVault;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Tests\Models\Team;
use Yannelli\EntryVault\Tests\Models\User;

beforeEach(function () {
    // Flush any existing resolvers before each test
    EntryVault::flushResolvers();

    $this->user = User::create(['name' => 'Test User', 'email' => 'test@example.com']);
    $this->otherUser = User::create(['name' => 'Other User', 'email' => 'other@example.com']);
    $this->team = Team::create(['name' => 'Test Team']);
    $this->user->teams()->attach($this->team);
});

afterEach(function () {
    EntryVault::flushResolvers();
});

test('can register global authorization callback', function () {
    EntryVault::authorize(function (Entry $entry) {
        return $entry->owner_id === 1;
    });

    $entry1 = Entry::create([
        'title' => 'Entry 1',
        'visibility' => 'public',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => 1,
    ]);

    $entry2 = Entry::create([
        'title' => 'Entry 2',
        'visibility' => 'public',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => 2,
    ]);

    expect(EntryVault::checkAuthorization($entry1))->toBeTrue()
        ->and(EntryVault::checkAuthorization($entry2))->toBeFalse();
});

test('global authorization callback affects isAccessibleBy', function () {
    // Deny access to all entries via global callback
    EntryVault::authorize(fn (Entry $entry) => false);

    $entry = Entry::create([
        'title' => 'Public Entry',
        'visibility' => 'public',
    ]);

    // Even public entries should be denied
    expect($entry->isAccessibleBy($this->user))->toBeFalse();
});

test('can register owner resolver', function () {
    EntryVault::resolveOwner(
        model: User::class,
        authorize: function (User $user, Entry $entry) {
            // Custom logic: allow if user ID matches or user is admin (id=1)
            return $user->id === $entry->owner_id || $user->id === 1;
        }
    );

    expect(EntryVault::hasOwnerResolver())->toBeTrue()
        ->and(EntryVault::getOwnerModel())->toBe(User::class);
});

test('owner resolver affects accessibility check', function () {
    // Create an admin user with id=1
    $adminUser = User::find(1) ?? $this->user;

    EntryVault::resolveOwner(
        model: User::class,
        authorize: function (User $user, Entry $entry) {
            // Allow if user is the owner OR if user id is 1 (admin)
            return $user->id === $entry->owner_id || $user->id === 1;
        }
    );

    $entry = Entry::create([
        'title' => 'Other User Entry',
        'visibility' => 'private',
        'owner_type' => $this->otherUser->getMorphClass(),
        'owner_id' => $this->otherUser->id,
    ]);

    // First user (id=1) should have access as admin
    expect($entry->isAccessibleBy($this->user))->toBeTrue();

    // But the actual owner should also have access
    expect($entry->isAccessibleBy($this->otherUser))->toBeTrue();
});

test('can register team resolver', function () {
    EntryVault::resolveTeam(
        model: Team::class,
        authorize: function (Team $team, Entry $entry) {
            return $team->id === $entry->team_id;
        }
    );

    expect(EntryVault::hasTeamResolver())->toBeTrue()
        ->and(EntryVault::getTeamModel())->toBe(Team::class);
});

test('team resolver affects team visibility check', function () {
    EntryVault::resolveTeam(
        model: Team::class,
        authorize: function (Team $team, Entry $entry) {
            return $team->id === $entry->team_id;
        }
    );

    $entry = Entry::create([
        'title' => 'Team Entry',
        'visibility' => 'team',
        'team_type' => $this->team->getMorphClass(),
        'team_id' => $this->team->id,
        'owner_type' => $this->otherUser->getMorphClass(),
        'owner_id' => $this->otherUser->id,
    ]);

    // User is member of the team
    expect($entry->isAccessibleBy($this->user))->toBeTrue();
});

test('can register custom resolver', function () {
    EntryVault::resolveCustom(
        name: 'organization',
        model: Team::class,
        authorize: function (Team $org, Entry $entry) {
            return $org->id === $entry->team_id;
        }
    );

    expect(EntryVault::hasCustomResolver('organization'))->toBeTrue()
        ->and(EntryVault::hasCustomResolver('nonexistent'))->toBeFalse();
});

test('custom resolver can be checked', function () {
    EntryVault::resolveCustom(
        name: 'special_access',
        model: User::class,
        authorize: function (User $user, Entry $entry) {
            return $user->email === 'test@example.com';
        }
    );

    $entry = Entry::create(['title' => 'Test Entry']);

    expect(EntryVault::checkCustomAuthorization('special_access', $this->user, $entry))->toBeTrue()
        ->and(EntryVault::checkCustomAuthorization('special_access', $this->otherUser, $entry))->toBeFalse();
});

test('flushResolvers clears all resolvers', function () {
    EntryVault::authorize(fn () => true);
    EntryVault::resolveOwner(User::class, fn () => true);
    EntryVault::resolveTeam(Team::class, fn () => true);
    EntryVault::resolveCustom('test', User::class, fn () => true);

    EntryVault::flushResolvers();

    expect(EntryVault::hasOwnerResolver())->toBeFalse()
        ->and(EntryVault::hasTeamResolver())->toBeFalse()
        ->and(EntryVault::hasCustomResolver('test'))->toBeFalse()
        ->and(EntryVault::getCustomResolvers())->toBeEmpty();
});

test('resolver methods are chainable', function () {
    $result = EntryVault::authorize(fn () => true)
        ->resolveOwner(User::class, fn () => true)
        ->resolveTeam(Team::class, fn () => true);

    expect($result)->toBeInstanceOf(\Yannelli\EntryVault\EntryVault::class);
});

test('owner resolver without authorize callback uses default check', function () {
    // Register owner without authorize callback
    EntryVault::resolveOwner(User::class);

    $entry = Entry::create([
        'title' => 'User Entry',
        'visibility' => 'private',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    // Should use default ownership check
    expect(EntryVault::checkOwnerAuthorization($this->user, $entry))->toBeTrue()
        ->and(EntryVault::checkOwnerAuthorization($this->otherUser, $entry))->toBeFalse();
});

test('team resolver without authorize callback uses default check', function () {
    EntryVault::resolveTeam(Team::class);

    $entry = Entry::create([
        'title' => 'Team Entry',
        'team_type' => $this->team->getMorphClass(),
        'team_id' => $this->team->id,
    ]);

    expect(EntryVault::checkTeamAuthorization($this->team, $entry))->toBeTrue();

    $otherTeam = Team::create(['name' => 'Other Team']);
    expect(EntryVault::checkTeamAuthorization($otherTeam, $entry))->toBeFalse();
});

test('isAuthorizedFor checks all resolvers', function () {
    EntryVault::resolveOwner(
        model: User::class,
        authorize: fn (User $user, Entry $entry) => $user->id === $entry->owner_id
    );

    EntryVault::resolveCustom(
        name: 'admin_override',
        model: User::class,
        authorize: fn (User $user, Entry $entry) => $user->id === 1
    );

    $entry = Entry::create([
        'title' => 'Private Entry',
        'visibility' => 'private',
        'owner_type' => $this->otherUser->getMorphClass(),
        'owner_id' => $this->otherUser->id,
    ]);

    // First user (id=1) should have access via custom resolver
    expect($entry->isAuthorizedFor($this->user))->toBeTrue();
});
