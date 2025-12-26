<?php

use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Models\EntryCategory;
use Yannelli\EntryVault\Tests\Models\Team;
use Yannelli\EntryVault\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create(['name' => 'Test User', 'email' => 'test@example.com']);
    $this->team = Team::create(['name' => 'Test Team']);
    $this->user->teams()->attach($this->team);
});

test('can create a system category', function () {
    $category = EntryCategory::create([
        'name' => 'System Category',
        'is_system' => true,
    ]);

    expect($category->isSystem())->toBeTrue()
        ->and($category->owner)->toBeNull();
});

test('can create a user category', function () {
    $category = EntryCategory::create([
        'name' => 'User Category',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect($category->isSystem())->toBeFalse()
        ->and($category->isOwnedBy($this->user))->toBeTrue();
});

test('can create a team category', function () {
    $category = EntryCategory::create([
        'name' => 'Team Category',
        'owner_type' => $this->team->getMorphClass(),
        'owner_id' => $this->team->id,
    ]);

    expect($category->isOwnedBy($this->team))->toBeTrue();
});

test('generates unique slug per owner scope', function () {
    $systemCat = EntryCategory::create(['name' => 'General', 'is_system' => true]);
    $userCat = EntryCategory::create([
        'name' => 'General',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect($systemCat->slug)->toBe('general')
        ->and($userCat->slug)->toBe('general');
});

test('system scope returns only system categories', function () {
    EntryCategory::create(['name' => 'System', 'is_system' => true]);
    EntryCategory::create([
        'name' => 'User',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect(EntryCategory::system()->count())->toBe(1);
});

test('ownedBy scope returns categories for owner', function () {
    EntryCategory::create(['name' => 'System', 'is_system' => true]);
    EntryCategory::create([
        'name' => 'User Cat 1',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);
    EntryCategory::create([
        'name' => 'User Cat 2',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect(EntryCategory::ownedBy($this->user)->count())->toBe(2);
});

test('accessibleBy scope returns system and owned categories', function () {
    EntryCategory::create(['name' => 'System', 'is_system' => true]);
    EntryCategory::create([
        'name' => 'User Category',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    $otherUser = User::create(['name' => 'Other', 'email' => 'other@example.com']);
    EntryCategory::create([
        'name' => 'Other User Category',
        'owner_type' => $otherUser->getMorphClass(),
        'owner_id' => $otherUser->id,
    ]);

    $accessible = EntryCategory::accessibleBy($this->user)->get();

    expect($accessible)->toHaveCount(2)
        ->and($accessible->pluck('name')->toArray())
        ->toContain('System', 'User Category')
        ->not->toContain('Other User Category');
});

test('ordered scope sorts by display_order', function () {
    EntryCategory::create(['name' => 'Third', 'is_system' => true, 'display_order' => 3]);
    EntryCategory::create(['name' => 'First', 'is_system' => true, 'display_order' => 1]);
    EntryCategory::create(['name' => 'Second', 'is_system' => true, 'display_order' => 2]);

    $ordered = EntryCategory::ordered()->get();

    expect($ordered[0]->name)->toBe('First')
        ->and($ordered[1]->name)->toBe('Second')
        ->and($ordered[2]->name)->toBe('Third');
});

test('default scope returns default category', function () {
    EntryCategory::create(['name' => 'Regular', 'is_system' => true, 'is_default' => false]);
    EntryCategory::create(['name' => 'Default', 'is_system' => true, 'is_default' => true]);

    $default = EntryCategory::default()->first();

    expect($default->name)->toBe('Default');
});

test('category has entries relationship', function () {
    $category = EntryCategory::create(['name' => 'Test', 'is_system' => true]);
    Entry::create(['title' => 'Entry 1', 'category_id' => $category->id]);
    Entry::create(['title' => 'Entry 2', 'category_id' => $category->id]);

    expect($category->entries)->toHaveCount(2);
});

test('findBySlug returns correct category', function () {
    $systemCat = EntryCategory::create(['name' => 'General', 'is_system' => true]);
    $userCat = EntryCategory::create([
        'name' => 'General',
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    $foundSystem = EntryCategory::findBySlug('general');
    $foundUser = EntryCategory::findBySlug('general', $this->user);

    expect($foundSystem->id)->toBe($systemCat->id)
        ->and($foundUser->id)->toBe($userCat->id);
});

test('findByUuid returns correct category', function () {
    $category = EntryCategory::create(['name' => 'Find Me', 'is_system' => true]);

    $found = EntryCategory::findByUuid($category->uuid);

    expect($found->id)->toBe($category->id);
});

test('can soft delete category', function () {
    $category = EntryCategory::create(['name' => 'To Delete', 'is_system' => true]);
    $category->delete();

    expect(EntryCategory::find($category->id))->toBeNull()
        ->and(EntryCategory::withTrashed()->find($category->id))->not->toBeNull();
});
