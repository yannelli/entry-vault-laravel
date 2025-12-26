<?php

namespace Yannelli\EntryVault;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Yannelli\EntryVault\Exceptions\EntryVaultException;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Models\EntryCategory;

class EntryVault
{
    /**
     * Create a new entry.
     */
    public function create(array $attributes): Entry
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::create($attributes);
    }

    /**
     * Find an entry by slug.
     */
    public function findBySlug(string $slug, ?Model $owner = null): ?Entry
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::findBySlug($slug, $owner);
    }

    /**
     * Find an entry by UUID.
     */
    public function findByUuid(string $uuid): ?Entry
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::findByUuid($uuid);
    }

    /**
     * Find an entry by UUID or throw an exception.
     */
    public function findByUuidOrFail(string $uuid): Entry
    {
        $entry = $this->findByUuid($uuid);

        if (! $entry) {
            throw EntryVaultException::entryNotFound($uuid);
        }

        return $entry;
    }

    /**
     * Get all templates.
     */
    public function templates(): Builder
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::templates();
    }

    /**
     * Get system templates.
     */
    public function systemTemplates(): Builder
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::systemTemplates();
    }

    /**
     * Get featured system templates (starters).
     */
    public function starters(): Builder
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::starters();
    }

    /**
     * Get starters in a specific category.
     */
    public function startersInCategory(EntryCategory|int|string $category): Builder
    {
        return $this->starters()->inCategory($category);
    }

    /**
     * Get all system categories.
     */
    public function categories(): Builder
    {
        $categoryModel = config('entry-vault.models.category');

        return $categoryModel::system();
    }

    /**
     * Get categories accessible by a user.
     */
    public function categoriesFor(Model $user): Builder
    {
        $categoryModel = config('entry-vault.models.category');

        return $categoryModel::accessibleBy($user)->ordered();
    }

    /**
     * Get the default category.
     */
    public function defaultCategory(): ?EntryCategory
    {
        $categoryModel = config('entry-vault.models.category');

        return $categoryModel::system()->default()->first();
    }

    /**
     * Find a category by slug.
     */
    public function findCategoryBySlug(string $slug, ?Model $owner = null): ?EntryCategory
    {
        $categoryModel = config('entry-vault.models.category');

        return $categoryModel::findBySlug($slug, $owner);
    }

    /**
     * Find a category by UUID.
     */
    public function findCategoryByUuid(string $uuid): ?EntryCategory
    {
        $categoryModel = config('entry-vault.models.category');

        return $categoryModel::findByUuid($uuid);
    }

    /**
     * Get entries accessible by a user.
     */
    public function accessibleBy(Model $user): Builder
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::accessibleBy($user);
    }

    /**
     * Get all public entries.
     */
    public function publicEntries(): Builder
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::public();
    }

    /**
     * Get entries owned by a model.
     */
    public function entriesFor(Model $owner): Builder
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::ownedBy($owner);
    }

    /**
     * Create an entry from a template.
     */
    public function createFromTemplate(Entry $template, array $attributes = []): Entry
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::createFromTemplate($template, $attributes);
    }

    /**
     * Get published entries.
     */
    public function published(): Builder
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::published();
    }

    /**
     * Get draft entries.
     */
    public function drafts(): Builder
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::draft();
    }

    /**
     * Get archived entries.
     */
    public function archived(): Builder
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::archived();
    }

    /**
     * Get entries in a category.
     */
    public function inCategory(EntryCategory|int|string $category): Builder
    {
        $entryModel = config('entry-vault.models.entry');

        return $entryModel::inCategory($category);
    }

    /**
     * Get the Entry model class.
     */
    public function getEntryModel(): string
    {
        return config('entry-vault.models.entry');
    }

    /**
     * Get the EntryCategory model class.
     */
    public function getCategoryModel(): string
    {
        return config('entry-vault.models.category');
    }

    /**
     * Get the EntryContent model class.
     */
    public function getContentModel(): string
    {
        return config('entry-vault.models.content');
    }
}
