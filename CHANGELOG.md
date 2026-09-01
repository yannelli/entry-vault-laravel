# Changelog

All notable changes to `entry-vault` will be documented in this file.

## v1.5.0 - 2026-09-01

### What's Changed

* Add Cursor Cloud Agent development environment by @yannelli in https://github.com/yannelli/entry-vault-laravel/pull/17
* Stack open PRs and add Laravel 13 / PHP 8.5 support by @yannelli in https://github.com/yannelli/entry-vault-laravel/pull/18

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/compare/v1.4.1...v1.5.0

## Unreleased

### Added

- Laravel 13 support alongside Laravel 12
- PHP 8.5 support (PHP 8.2–8.5)
- CI test matrix coverage for PHP 8.2/8.3/8.4/8.5 and Laravel 12/13
- Entry helpers `publish()`, `unpublish()`, `archive()`, and `restoreToDraft()` that run the registered state transitions

### Changed

- Raised optional Filament admin integration to Filament 5 (from 4), including Schema `Section` / `Get` / `Set` and table `recordActions()` / `toolbarActions()`
- GitHub Actions: `ramsey/composer-install` v4, `dependabot/fetch-metadata` v3.1.0, `actions/checkout` v6
- Composer constraints updated for Laravel 12|13 (`illuminate/*`, `orchestra/testbench` 10|11, `overtrue/laravel-versionable` 5.5|6, Pest 3|4). Pest 5 is not included because it requires PHP 8.4 and `pestphp/pest-plugin-laravel` 5.0.1 supports Laravel 13 only.

### Fixed

- PHPStan workflow now actually runs `phpstan analyse` after installing dependencies, and the package is clean at level 5
- Filament plugin `isEnabled()` fallback now matches the config default (`false`)
- Filament state actions use statement closures so `publish()` / `archive()` return values do not violate `void` action types
- Entry view page includes the Restore to Draft action for archived entries
- Team visibility/authorization now supports Jetstream-style `currentTeam()` relationships (which return a Relation, not a model)
- State machine now uses the package transition classes, so `transitionTo()` sets `published_at` and dispatches lifecycle events
- Bug report template placeholders and GitHub issue contact links no longer use Spatie skeleton stubs

## v1.4.1 - 2025-12-30

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/compare/v1.4.0...v1.4.1

## v1.4.0 - 2025-12-30

### What's Changed

* Upgrade Filament from 3.0 to 4.0 by @yannelli in https://github.com/yannelli/entry-vault-laravel/pull/8

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/compare/v1.3.0...v1.4.0

## v1.3.0 - 2025-12-29

### What's Changed

* Add tests for filament provider plugin by @yannelli in https://github.com/yannelli/entry-vault-laravel/pull/7

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/compare/v1.2.1...v1.3.0

## v1.2.1 - 2025-12-29

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/compare/v1.2.0...v1.2.1

## v1.2.0 - 2025-12-29

### What's Changed

* Add entry content preview feature to Filament plugin by @yannelli in https://github.com/yannelli/entry-vault-laravel/pull/6

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/compare/v1.1.1...v1.2.0

## v1.1.1 - 2025-12-29

### What's Changed

* Fix versions table and migration setup by @yannelli in https://github.com/yannelli/entry-vault-laravel/pull/5

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/compare/v1.1.0...v1.1.1

## v1.1.0 - 2025-12-29

### What's Changed

* Add Laravel Filament 3 admin provider by @yannelli in https://github.com/yannelli/entry-vault-laravel/pull/4

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/compare/v1.0.3...v1.1.0

## v1.0.3 - 2025-12-26

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/compare/v1.0.2...v1.0.3

## v1.0.2 - 2025-12-26

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/compare/v1.0.1...v1.0.2

## v1.0.1 - 2025-12-26

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/compare/v1.0.0...v1.0.1

## v1.0.0 - 2025-12-26

### What's Changed

* Create Laravel package for entry resource library by @yannelli in https://github.com/yannelli/entry-vault-laravel/pull/2
* Claude/entry vault package u i6b u by @yannelli in https://github.com/yannelli/entry-vault-laravel/pull/3

### New Contributors

* @yannelli made their first contribution in https://github.com/yannelli/entry-vault-laravel/pull/2

**Full Changelog**: https://github.com/yannelli/entry-vault-laravel/commits/v1.0.0

## 1.0.0 - 2025-12-26

### Added

- Initial release of Entry Vault package
- Entry management with CRUD operations
- Multi-tenancy support with polymorphic ownership
- Visibility controls (public, private, team)
- State machine with draft, published, and archived states
- Version history integration via overtrue/laravel-versionable
- Category system with system, team, and user-owned categories
- Template system with featured/starter templates
- Comprehensive traits for models (HasEntries, HasEntryCategories, HasEntryContent)
- EntryVault facade for convenient access
- Artisan commands for installation and category seeding
- Full event system for entry lifecycle
- Comprehensive test suite with Pest
