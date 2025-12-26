<?php

use Yannelli\EntryVault\Enums\ContentType;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Models\EntryContent;

test('can create entry content', function () {
    $entry = Entry::create(['title' => 'Test Entry']);

    $content = $entry->contents()->create([
        'type' => 'markdown',
        'body' => '# Hello World',
        'order' => 0,
    ]);

    expect($content)->toBeInstanceOf(EntryContent::class)
        ->and($content->entry->id)->toBe($entry->id);
});

test('content types are detected correctly', function () {
    $entry = Entry::create(['title' => 'Test Entry']);

    $markdown = $entry->contents()->create(['type' => 'markdown', 'body' => '# Test']);
    $html = $entry->contents()->create(['type' => 'html', 'body' => '<p>Test</p>']);
    $json = $entry->contents()->create(['type' => 'json', 'body' => '{"key": "value"}']);
    $text = $entry->contents()->create(['type' => 'text', 'body' => 'Plain text']);

    expect($markdown->isMarkdown())->toBeTrue()
        ->and($html->isHtml())->toBeTrue()
        ->and($json->isJson())->toBeTrue()
        ->and($text->isText())->toBeTrue();
});

test('json content can be decoded', function () {
    $entry = Entry::create(['title' => 'Test Entry']);

    $content = $entry->contents()->create([
        'type' => 'json',
        'body' => '{"name": "John", "age": 30}',
    ]);

    $decoded = $content->getDecodedBody();

    expect($decoded)->toBeArray()
        ->and($decoded['name'])->toBe('John')
        ->and($decoded['age'])->toBe(30);
});

test('content metadata is stored as array', function () {
    $entry = Entry::create(['title' => 'Test Entry']);

    $content = $entry->contents()->create([
        'type' => 'text',
        'body' => 'Test',
        'metadata' => ['key' => 'value', 'another' => 123],
    ]);

    expect($content->metadata)->toBeArray()
        ->and($content->metadata['key'])->toBe('value')
        ->and($content->metadata['another'])->toBe(123);
});

test('contents are ordered by order column', function () {
    $entry = Entry::create(['title' => 'Test Entry']);

    $entry->contents()->create(['type' => 'text', 'body' => 'Third', 'order' => 3]);
    $entry->contents()->create(['type' => 'text', 'body' => 'First', 'order' => 1]);
    $entry->contents()->create(['type' => 'text', 'body' => 'Second', 'order' => 2]);

    $contents = $entry->fresh()->contents;

    expect($contents[0]->body)->toBe('First')
        ->and($contents[1]->body)->toBe('Second')
        ->and($contents[2]->body)->toBe('Third');
});

test('ContentType enum has valid check', function () {
    expect(ContentType::isValid('markdown'))->toBeTrue()
        ->and(ContentType::isValid('html'))->toBeTrue()
        ->and(ContentType::isValid('json'))->toBeTrue()
        ->and(ContentType::isValid('text'))->toBeTrue()
        ->and(ContentType::isValid('invalid'))->toBeFalse();
});

test('ContentType enum has labels', function () {
    expect(ContentType::MARKDOWN->label())->toBe('Markdown')
        ->and(ContentType::HTML->label())->toBe('HTML')
        ->and(ContentType::JSON->label())->toBe('JSON')
        ->and(ContentType::TEXT->label())->toBe('Plain Text');
});

test('content is deleted when entry is deleted', function () {
    $entry = Entry::create(['title' => 'Test Entry']);
    $entry->contents()->create(['type' => 'text', 'body' => 'Test']);
    $contentId = $entry->contents->first()->id;

    $entry->forceDelete();

    expect(EntryContent::find($contentId))->toBeNull();
});
