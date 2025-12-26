# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Entry Vault is a Laravel 12 backend-only package (`yannelli/entry-vault`) for building entry/resource library systems. It provides:

- Full CRUD operations on entries with metadata (title, description, keywords)
- Multi-tenancy via polymorphic ownership (user, team, or custom model)
- Visibility controls (public, private, team-based)
- State machine (Draft → Published → Archived) via Spatie ModelStates
- Built-in versioning with revert capabilities via overtrue/laravel-versionable
- Category system (system, team, or user-owned)
- Template system for starter/featured templates

This is a reusable Composer package, not a full Laravel application. No UI components—purely API/backend focused.

## Common Commands

```bash
# Run tests (Pest PHP)
composer test

# Run tests with coverage
composer test-coverage

# Static analysis (PHPStan level 5)
composer analyse

# Format code (Laravel Pint)
composer format
```

To run a single test file:
```bash
./vendor/bin/pest tests/Feature/YourTest.php
```

To run a specific test:
```bash
./vendor/bin/pest --filter="test name"
```

## Architecture

### Directory Structure

```
/src
  ├── Models/         Entry, EntryContent, EntryCategory
  ├── States/         Draft, Published, Archived (Spatie ModelStates)
  ├── Transitions/    State change handlers
  ├── Traits/         HasEntries, HasOwner, HasTeam, HasVisibility, HasEntryCategories, HasEntryContent
  ├── Commands/       Artisan: InstallEntryVaultCommand, SeedCategoriesCommand
  ├── Events/         EntryCreated, EntryUpdated, EntryDeleted, EntryPublished, etc.
  ├── Exceptions/     EntryVaultException, InvalidStateTransition
  ├── Enums/          ContentType (markdown/html/json/text), EntryVisibility
  ├── Contracts/      EntryAdminResolver interface
  ├── Facades/        EntryVault facade
  └── EntryVault.php  Main service class with authorization resolver system
```

### Key Patterns

**State Machine:** Uses Spatie's `spatie/laravel-model-states` for entry lifecycle (Draft/Published/Archived) with immutable state transitions and event dispatch.

**Authorization System:** Flexible resolver system in `EntryVault.php`:
- Global authorization callback
- Owner model resolver with custom auth
- Team model resolver with custom auth
- Custom resolvers for complex scenarios

**Polymorphic Relationships:** Owner and team relationships are polymorphic, allowing any model type to own entries.

**Trait Composition:** Heavy use of traits (`HasEntries`, `HasOwner`, `HasTeam`, `HasVisibility`) for flexible model composition.

**Versioning:** Snapshot strategy via `overtrue/laravel-versionable` for entry content history.

### Database Tables

- `entries` - Main entry table with state, visibility, metadata
- `entry_contents` - Flexible content storage (markdown/HTML/JSON/text)
- `entry_categories` - Hierarchical category system

### Configuration

Main config file: `config/entry-vault.php`
- Table names, model class overrides, user/team model configuration
- Default visibility/state settings, versioning strategy
- Content types allowed, admin resolver registration
- Soft delete toggle, seeding options

## Testing

- **Framework:** Pest PHP v3 with Laravel and Arch plugins
- **Location:** `/tests` directory
- **CI:** GitHub Actions tests against PHP 8.3/8.4 and Laravel 11.*/12.*

## Code Quality Requirements

- PHP 8.2+ required
- PHPStan level 5 (strict static analysis)
- Laravel Pint formatting
- Tests must pass on multiple PHP/Laravel version combinations
