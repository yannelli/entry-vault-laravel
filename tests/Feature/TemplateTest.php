<?php

use Yannelli\EntryVault\Events\EntryCreatedFromTemplate;
use Yannelli\EntryVault\Exceptions\EntryVaultException;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Models\EntryCategory;
use Yannelli\EntryVault\Tests\Models\User;

beforeEach(function () {
    $this->user = User::create(['name' => 'Test User', 'email' => 'test@example.com']);
});

test('can create a template', function () {
    $template = Entry::create([
        'title' => 'My Template',
        'is_template' => true,
    ]);

    expect($template->isTemplate())->toBeTrue();
});

test('can create a featured template', function () {
    $template = Entry::create([
        'title' => 'Featured Template',
        'is_template' => true,
        'is_featured' => true,
    ]);

    expect($template->isFeatured())->toBeTrue();
});

test('can create a system template', function () {
    $template = Entry::create([
        'title' => 'System Template',
        'is_template' => true,
        'owner_type' => null,
        'owner_id' => null,
    ]);

    expect($template->isSystemTemplate())->toBeTrue();
});

test('templates scope returns only templates', function () {
    Entry::create(['title' => 'Regular Entry']);
    Entry::create(['title' => 'Template', 'is_template' => true]);

    expect(Entry::templates()->count())->toBe(1)
        ->and(Entry::notTemplates()->count())->toBe(1);
});

test('systemTemplates scope returns only system templates', function () {
    Entry::create([
        'title' => 'System Template',
        'is_template' => true,
    ]);
    Entry::create([
        'title' => 'User Template',
        'is_template' => true,
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect(Entry::systemTemplates()->count())->toBe(1);
});

test('featured scope returns only featured entries', function () {
    Entry::create(['title' => 'Regular', 'is_template' => true]);
    Entry::create(['title' => 'Featured', 'is_template' => true, 'is_featured' => true]);

    expect(Entry::featured()->count())->toBe(1);
});

test('starters scope returns featured system templates', function () {
    Entry::create(['title' => 'System Not Featured', 'is_template' => true]);
    Entry::create(['title' => 'Starter', 'is_template' => true, 'is_featured' => true]);
    Entry::create([
        'title' => 'User Featured',
        'is_template' => true,
        'is_featured' => true,
        'owner_type' => $this->user->getMorphClass(),
        'owner_id' => $this->user->id,
    ]);

    expect(Entry::starters()->count())->toBe(1)
        ->and(Entry::starters()->first()->title)->toBe('Starter');
});

test('can create entry from template', function () {
    Event::fake();

    $category = EntryCategory::create(['name' => 'Test Category', 'is_system' => true]);

    $template = Entry::create([
        'title' => 'Template Entry',
        'description' => 'Template description',
        'keywords' => ['template', 'test'],
        'is_template' => true,
        'category_id' => $category->id,
    ]);

    $template->contents()->create([
        'type' => 'markdown',
        'body' => '# Template Content',
        'order' => 0,
    ]);

    $entry = Entry::createFromTemplate($template, [
        'title' => 'New Entry from Template',
        'owner' => $this->user,
    ]);

    expect($entry->title)->toBe('New Entry from Template')
        ->and($entry->description)->toBe('Template description')
        ->and($entry->keywords)->toBe(['template', 'test'])
        ->and($entry->template_id)->toBe($template->id)
        ->and($entry->isTemplate())->toBeFalse()
        ->and($entry->isDraft())->toBeTrue()
        ->and($entry->isOwnedBy($this->user))->toBeTrue()
        ->and($entry->category_id)->toBe($category->id)
        ->and($entry->contents)->toHaveCount(1)
        ->and($entry->contents->first()->body)->toBe('# Template Content');

    Event::assertDispatched(EntryCreatedFromTemplate::class);
});

test('createFromTemplate throws exception for non-template', function () {
    $entry = Entry::create(['title' => 'Not a Template']);

    expect(fn () => Entry::createFromTemplate($entry))
        ->toThrow(EntryVaultException::class);
});

test('template tracks derived entries', function () {
    $template = Entry::create(['title' => 'Template', 'is_template' => true]);

    Entry::createFromTemplate($template, ['title' => 'Derived 1', 'owner' => $this->user]);
    Entry::createFromTemplate($template, ['title' => 'Derived 2', 'owner' => $this->user]);

    expect($template->derivedEntries)->toHaveCount(2);
});

test('entry knows if created from template', function () {
    $template = Entry::create(['title' => 'Template', 'is_template' => true]);
    $derived = Entry::createFromTemplate($template, ['title' => 'Derived', 'owner' => $this->user]);
    $regular = Entry::create(['title' => 'Regular']);

    expect($derived->wasCreatedFromTemplate())->toBeTrue()
        ->and($regular->wasCreatedFromTemplate())->toBeFalse();
});

test('can access source template from derived entry', function () {
    $template = Entry::create(['title' => 'Source Template', 'is_template' => true]);
    $derived = Entry::createFromTemplate($template, ['title' => 'Derived', 'owner' => $this->user]);

    expect($derived->template)->not->toBeNull()
        ->and($derived->template->id)->toBe($template->id);
});

test('inCategory scope filters templates by category', function () {
    $category = EntryCategory::create(['name' => 'Test', 'is_system' => true]);

    Entry::create([
        'title' => 'Template in Category',
        'is_template' => true,
        'is_featured' => true,
        'category_id' => $category->id,
    ]);
    Entry::create([
        'title' => 'Template without Category',
        'is_template' => true,
        'is_featured' => true,
    ]);

    expect(Entry::starters()->inCategory($category)->count())->toBe(1);
});
