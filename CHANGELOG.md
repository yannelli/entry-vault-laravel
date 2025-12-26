# Changelog

All notable changes to `entry-vault` will be documented in this file.

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
