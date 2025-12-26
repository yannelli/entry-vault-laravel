# Changelog

All notable changes to `entry-vault` will be documented in this file.

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
