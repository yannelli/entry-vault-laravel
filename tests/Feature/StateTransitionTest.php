<?php

use Yannelli\EntryVault\Events\EntryArchived;
use Yannelli\EntryVault\Events\EntryPublished;
use Yannelli\EntryVault\Events\EntryUnpublished;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\States\Archived;
use Yannelli\EntryVault\States\Draft;
use Yannelli\EntryVault\States\Published;
use Yannelli\EntryVault\Transitions\ArchiveTransition;
use Yannelli\EntryVault\Transitions\PublishTransition;
use Yannelli\EntryVault\Transitions\RestoreTransition;
use Yannelli\EntryVault\Transitions\UnpublishTransition;

test('entry starts in draft state', function () {
    $entry = Entry::create(['title' => 'Draft Entry']);

    expect($entry->state)->toBeInstanceOf(Draft::class)
        ->and($entry->isDraft())->toBeTrue();
});

test('can transition from draft to published', function () {
    $entry = Entry::create(['title' => 'To Publish']);

    Event::fake();

    $transition = new PublishTransition($entry);
    $transition->handle();

    expect($entry->fresh()->state)->toBeInstanceOf(Published::class)
        ->and($entry->fresh()->isPublished())->toBeTrue()
        ->and($entry->fresh()->published_at)->not->toBeNull();

    Event::assertDispatched(EntryPublished::class);
});

test('can transition from published to draft (unpublish)', function () {
    $entry = Entry::create(['title' => 'To Unpublish']);
    (new PublishTransition($entry))->handle();

    Event::fake();

    $transition = new UnpublishTransition($entry->fresh());
    $transition->handle();

    expect($entry->fresh()->state)->toBeInstanceOf(Draft::class)
        ->and($entry->fresh()->isDraft())->toBeTrue()
        ->and($entry->fresh()->published_at)->toBeNull();

    Event::assertDispatched(EntryUnpublished::class);
});

test('can transition from draft to archived', function () {
    $entry = Entry::create(['title' => 'To Archive']);

    Event::fake();

    $transition = new ArchiveTransition($entry);
    $transition->handle();

    expect($entry->fresh()->state)->toBeInstanceOf(Archived::class)
        ->and($entry->fresh()->isArchived())->toBeTrue();

    Event::assertDispatched(EntryArchived::class);
});

test('can transition from published to archived', function () {
    $entry = Entry::create(['title' => 'Published to Archive']);
    (new PublishTransition($entry))->handle();

    Event::fake();

    $transition = new ArchiveTransition($entry->fresh());
    $transition->handle();

    expect($entry->fresh()->state)->toBeInstanceOf(Archived::class);

    Event::assertDispatched(EntryArchived::class);
});

test('can transition from archived to draft (restore)', function () {
    $entry = Entry::create(['title' => 'Archived to Restore']);
    (new ArchiveTransition($entry))->handle();

    $transition = new RestoreTransition($entry->fresh());
    $transition->handle();

    expect($entry->fresh()->state)->toBeInstanceOf(Draft::class)
        ->and($entry->fresh()->isDraft())->toBeTrue();
});

test('state scopes filter correctly', function () {
    $draft = Entry::create(['title' => 'Draft']);
    $published = Entry::create(['title' => 'Published']);
    (new PublishTransition($published))->handle();
    $archived = Entry::create(['title' => 'Archived']);
    (new ArchiveTransition($archived))->handle();

    expect(Entry::draft()->count())->toBe(1)
        ->and(Entry::published()->count())->toBe(1)
        ->and(Entry::archived()->count())->toBe(1);
});

test('state has label and color', function () {
    $draft = new Draft(new Entry);
    $published = new Published(new Entry);
    $archived = new Archived(new Entry);

    expect($draft->label())->toBe('Draft')
        ->and($draft->color())->toBe('gray')
        ->and($published->label())->toBe('Published')
        ->and($published->color())->toBe('green')
        ->and($archived->label())->toBe('Archived')
        ->and($archived->color())->toBe('red');
});
