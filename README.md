# Entry Vault

A Laravel 12 package for building a backend-only entry/resource library system with multi-tenancy, versioning, and state management.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/yannelli/entry-vault.svg?style=flat-square)](https://packagist.org/packages/yannelli/entry-vault)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/yannelli/entry-vault/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/yannelli/entry-vault/actions?query=workflow%3Arun-tests+branch%3Amain)

## Features

- **Entry Management** - Full CRUD operations with metadata (title, description, keywords)
- **Flexible Content Storage** - Separate content table with support for multiple content types (markdown, HTML, JSON, text)
- **Multi-tenancy Support** - Polymorphic ownership (user, team, or custom model)
- **Visibility Controls** - Public, private, and team visibility options
- **Draft/Published Workflow** - State machine with draft, published, and archived states
- **Version History** - Built-in versioning with revert capabilities
- **Category System** - System, team, or user-owned categories
- **Template System** - Create entries from templates with featured/starter templates

**No UI components are included.** This is a pure backend/API package.

## Installation

Install the package via Composer:

```bash
composer require yannelli/entry-vault
```

Run the installation command:

```bash
php artisan entry-vault:install
```

This will:
1. Publish the configuration file
2. Publish and run migrations
3. Optionally seed default categories

### Manual Installation

If you prefer manual installation:

```bash
# Publish config
php artisan vendor:publish --tag="entry-vault-config"

# Publish migrations
php artisan vendor:publish --tag="entry-vault-migrations"

# Run migrations
php artisan migrate

# Seed default categories (optional)
php artisan entry-vault:seed-categories
```

## Configuration

The configuration file is published to `config/entry-vault.php`:

```php
return [
    // Table names
    'tables' => [
        'entries' => 'entries',
        'contents' => 'entry_contents',
        'categories' => 'entry_categories',
    ],

    // Model classes (for extending)
    'models' => [
        'entry' => \Yannelli\EntryVault\Models\Entry::class,
        'content' => \Yannelli\EntryVault\Models\EntryContent::class,
        'category' => \Yannelli\EntryVault\Models\EntryCategory::class,
    ],

    // User and team models
    'user_model' => \App\Models\User::class,
    'team_model' => null,

    // Defaults
    'default_visibility' => 'private',
    'default_state' => 'draft',

    // Versioning
    'versioning' => [
        'enabled' => true,
        'strategy' => 'snapshot',
        'keep_versions' => 50,
    ],
];
```

## Basic Usage

### Creating Entries

```php
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Facades\EntryVault;

// Create a basic entry
$entry = Entry::create([
    'title' => 'My First Entry',
    'description' => 'A sample entry',
    'keywords' => ['sample', 'first'],
]);

// Create with the facade
$entry = EntryVault::create([
    'title' => 'Another Entry',
    'visibility' => 'public',
]);

// Create with an owner
$entry = Entry::create([
    'title' => 'User Entry',
    'owner_type' => $user->getMorphClass(),
    'owner_id' => $user->id,
]);
```

### Adding Content

```php
$entry->contents()->create([
    'type' => 'markdown',
    'body' => '# Hello World\n\nThis is my content.',
    'order' => 0,
]);

// Multiple content sections
$entry->contents()->create([
    'type' => 'html',
    'body' => '<p>Additional content</p>',
    'order' => 1,
]);
```

### State Management

```php
use Yannelli\EntryVault\Transitions\PublishTransition;
use Yannelli\EntryVault\Transitions\UnpublishTransition;
use Yannelli\EntryVault\Transitions\ArchiveTransition;

// Publish an entry
$transition = new PublishTransition($entry);
$transition->handle();

// Unpublish (back to draft)
$transition = new UnpublishTransition($entry);
$transition->handle();

// Archive
$transition = new ArchiveTransition($entry);
$transition->handle();

// Check state
$entry->isDraft();      // true/false
$entry->isPublished();  // true/false
$entry->isArchived();   // true/false
```

### Visibility

```php
// Create with visibility
$entry = Entry::create([
    'title' => 'Team Entry',
    'visibility' => 'team',
    'team_type' => $team->getMorphClass(),
    'team_id' => $team->id,
]);

// Query by visibility
Entry::public()->get();
Entry::private()->get();
Entry::teamVisible()->get();

// Get entries visible to a user
Entry::visibleTo($user)->get();
EntryVault::accessibleBy($user)->get();

// Check access
$entry->isAccessibleBy($user); // true/false
```

### Categories

```php
use Yannelli\EntryVault\Models\EntryCategory;

// Create a system category
$category = EntryCategory::create([
    'name' => 'Documentation',
    'is_system' => true,
    'is_default' => true,
]);

// Create a user category
$category = EntryCategory::create([
    'name' => 'My Personal Category',
    'owner_type' => $user->getMorphClass(),
    'owner_id' => $user->id,
]);

// Assign entry to category
$entry->update(['category_id' => $category->id]);

// Query by category
Entry::inCategory($category)->get();
Entry::inCategory('documentation')->get(); // by slug

// Get accessible categories for user
EntryCategory::accessibleBy($user)->ordered()->get();
EntryVault::categoriesFor($user)->get();
```

### Templates

```php
// Create a template
$template = Entry::create([
    'title' => 'Blog Post Template',
    'is_template' => true,
    'is_featured' => true, // Make it a starter
]);

$template->contents()->create([
    'type' => 'markdown',
    'body' => '# Title\n\n## Introduction\n\n...',
]);

// Create entry from template
$entry = Entry::createFromTemplate($template, [
    'title' => 'My Blog Post',
    'owner' => $user,
]);

// Query templates
Entry::templates()->get();
Entry::systemTemplates()->get();
Entry::starters()->get(); // Featured system templates

// Via facade
EntryVault::templates()->get();
EntryVault::starters()->get();
EntryVault::startersInCategory('onboarding')->get();
```

## Adding Traits to Your Models

### HasEntries

Add to models that own entries (User, Team, etc.):

```php
use Yannelli\EntryVault\Traits\HasEntries;

class User extends Model
{
    use HasEntries;
}

// Usage
$user->entries;
$user->draftEntries;
$user->publishedEntries;
$user->entryTemplates;
```

### HasEntryCategories

Add to models that own categories:

```php
use Yannelli\EntryVault\Traits\HasEntryCategories;

class User extends Model
{
    use HasEntryCategories;
}

// Usage
$user->entryCategories;
$user->defaultEntryCategory();
```

### HasEntryContent

Add to models that can be associated with entry content:

```php
use Yannelli\EntryVault\Traits\HasEntryContent;

class Document extends Model
{
    use HasEntryContent;
}

// Usage
$document->entryContent;
$document->entryContents;
```

## Events

The package dispatches the following events:

- `EntryCreated` - When an entry is created
- `EntryUpdated` - When an entry is updated
- `EntryDeleted` - When an entry is deleted
- `EntryPublished` - When an entry is published
- `EntryUnpublished` - When an entry is unpublished
- `EntryArchived` - When an entry is archived
- `EntryRestored` - When an entry is restored from archive
- `EntryCreatedFromTemplate` - When an entry is created from a template
- `EntryCategoryCreated` - When a category is created
- `EntryCategoryUpdated` - When a category is updated
- `EntryCategoryDeleted` - When a category is deleted

## Extending Models

You can extend the default models by updating the config:

```php
// config/entry-vault.php
'models' => [
    'entry' => \App\Models\Entry::class,
    'content' => \App\Models\EntryContent::class,
    'category' => \App\Models\EntryCategory::class,
],
```

```php
// app/Models/Entry.php
namespace App\Models;

use Yannelli\EntryVault\Models\Entry as BaseEntry;

class Entry extends BaseEntry
{
    // Your customizations
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Ryan Yannelli](https://github.com/yannelli)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
