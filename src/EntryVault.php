<?php

namespace Yannelli\EntryVault;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Yannelli\EntryVault\Exceptions\EntryVaultException;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Models\EntryCategory;
use Yannelli\EntryVault\Models\EntryContent;

class EntryVault
{
    /**
     * Global authorization callback.
     */
    protected static ?Closure $authorizationCallback = null;

    /**
     * Registered owner resolver.
     */
    protected static ?array $ownerResolver = null;

    /**
     * Registered team resolver.
     */
    protected static ?array $teamResolver = null;

    /**
     * Additional custom resolvers.
     */
    protected static array $customResolvers = [];

    /**
     * Register a global authorization callback.
     *
     * @param  Closure(Entry): bool  $callback
     */
    public function authorize(Closure $callback): static
    {
        static::$authorizationCallback = $callback;

        return $this;
    }

    /**
     * Register an owner model resolver with optional authorization.
     *
     * @param  class-string<Model>  $model
     * @param  Closure(Model, Entry): bool|null  $authorize
     */
    public function resolveOwner(string $model, ?Closure $authorize = null): static
    {
        static::$ownerResolver = [
            'model' => $model,
            'authorize' => $authorize,
        ];

        // Also update the config for the user model
        config(['entry-vault.user_model' => $model]);

        return $this;
    }

    /**
     * Register a team model resolver with optional authorization.
     *
     * @param  class-string<Model>  $model
     * @param  Closure(Model, Entry): bool|null  $authorize
     */
    public function resolveTeam(string $model, ?Closure $authorize = null): static
    {
        static::$teamResolver = [
            'model' => $model,
            'authorize' => $authorize,
        ];

        // Also update the config for the team model
        config(['entry-vault.team_model' => $model]);

        return $this;
    }

    /**
     * Register a custom resolver for additional authorization contexts.
     *
     * @param  Closure(Model, Entry): bool  $authorize
     */
    public function resolveCustom(string $name, string $model, Closure $authorize): static
    {
        static::$customResolvers[$name] = [
            'model' => $model,
            'authorize' => $authorize,
        ];

        return $this;
    }

    /**
     * Check if an entry is authorized using the global callback.
     */
    public function checkAuthorization(Entry $entry): bool
    {
        if (static::$authorizationCallback === null) {
            return true;
        }

        return call_user_func(static::$authorizationCallback, $entry);
    }

    /**
     * Check if a user/owner can access an entry.
     */
    public function checkOwnerAuthorization(Model $owner, Entry $entry): bool
    {
        if (static::$ownerResolver === null || static::$ownerResolver['authorize'] === null) {
            // Fall back to default ownership check
            return $entry->owner_type === $owner->getMorphClass()
                && $entry->owner_id === $owner->getKey();
        }

        return call_user_func(static::$ownerResolver['authorize'], $owner, $entry);
    }

    /**
     * Check if a team can access an entry.
     */
    public function checkTeamAuthorization(Model $team, Entry $entry): bool
    {
        if (static::$teamResolver === null || static::$teamResolver['authorize'] === null) {
            // Fall back to default team check
            return $entry->team_type === $team->getMorphClass()
                && $entry->team_id === $team->getKey();
        }

        return call_user_func(static::$teamResolver['authorize'], $team, $entry);
    }

    /**
     * Check a custom resolver authorization.
     */
    public function checkCustomAuthorization(string $name, Model $model, Entry $entry): bool
    {
        if (! isset(static::$customResolvers[$name])) {
            return false;
        }

        return call_user_func(static::$customResolvers[$name]['authorize'], $model, $entry);
    }

    /**
     * Get the registered owner model class.
     */
    public function getOwnerModel(): ?string
    {
        return static::$ownerResolver['model'] ?? config('entry-vault.user_model');
    }

    /**
     * Get the registered team model class.
     */
    public function getTeamModel(): ?string
    {
        return static::$teamResolver['model'] ?? config('entry-vault.team_model');
    }

    /**
     * Check if an owner resolver is registered.
     */
    public function hasOwnerResolver(): bool
    {
        return static::$ownerResolver !== null;
    }

    /**
     * Check if a team resolver is registered.
     */
    public function hasTeamResolver(): bool
    {
        return static::$teamResolver !== null;
    }

    /**
     * Check if a custom resolver is registered.
     */
    public function hasCustomResolver(string $name): bool
    {
        return isset(static::$customResolvers[$name]);
    }

    /**
     * Get all registered custom resolvers.
     */
    public function getCustomResolvers(): array
    {
        return static::$customResolvers;
    }

    /**
     * Reset all resolvers (useful for testing).
     */
    public function flushResolvers(): static
    {
        static::$authorizationCallback = null;
        static::$ownerResolver = null;
        static::$teamResolver = null;
        static::$customResolvers = [];

        return $this;
    }

    /**
     * Create a new entry.
     */
    public function create(array $attributes): Entry
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::create($attributes);
    }

    /**
     * Find an entry by slug.
     */
    public function findBySlug(string $slug, ?Model $owner = null): ?Entry
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::findBySlug($slug, $owner);
    }

    /**
     * Find an entry by UUID.
     */
    public function findByUuid(string $uuid): ?Entry
    {
        $entryModel = $this->entryModelClass();

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
        $entryModel = $this->entryModelClass();

        return $entryModel::templates();
    }

    /**
     * Get system templates.
     */
    public function systemTemplates(): Builder
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::systemTemplates();
    }

    /**
     * Get featured system templates (starters).
     */
    public function starters(): Builder
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::starters();
    }

    /**
     * Get starters in a specific category.
     */
    public function startersInCategory(EntryCategory|int|string $category): Builder
    {
        return $this->entryModelClass()::starters()->inCategory($category);
    }

    /**
     * Get all system categories.
     */
    public function categories(): Builder
    {
        $categoryModel = $this->categoryModelClass();

        return $categoryModel::system();
    }

    /**
     * Get categories accessible by a user.
     */
    public function categoriesFor(Model $user): Builder
    {
        $categoryModel = $this->categoryModelClass();

        return $categoryModel::accessibleBy($user)->ordered();
    }

    /**
     * Get the default category.
     */
    public function defaultCategory(): ?EntryCategory
    {
        $categoryModel = $this->categoryModelClass();

        return $categoryModel::system()->default()->first();
    }

    /**
     * Find a category by slug.
     */
    public function findCategoryBySlug(string $slug, ?Model $owner = null): ?EntryCategory
    {
        $categoryModel = $this->categoryModelClass();

        return $categoryModel::findBySlug($slug, $owner);
    }

    /**
     * Find a category by UUID.
     */
    public function findCategoryByUuid(string $uuid): ?EntryCategory
    {
        $categoryModel = $this->categoryModelClass();

        return $categoryModel::findByUuid($uuid);
    }

    /**
     * Get entries accessible by a user.
     */
    public function accessibleBy(Model $user): Builder
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::accessibleBy($user);
    }

    /**
     * Get all public entries.
     */
    public function publicEntries(): Builder
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::public();
    }

    /**
     * Get entries owned by a model.
     */
    public function entriesFor(Model $owner): Builder
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::ownedBy($owner);
    }

    /**
     * Create an entry from a template.
     */
    public function createFromTemplate(Entry $template, array $attributes = []): Entry
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::createFromTemplate($template, $attributes);
    }

    /**
     * Get published entries.
     */
    public function published(): Builder
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::published();
    }

    /**
     * Get draft entries.
     */
    public function drafts(): Builder
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::draft();
    }

    /**
     * Get archived entries.
     */
    public function archived(): Builder
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::archived();
    }

    /**
     * Get entries in a category.
     */
    public function inCategory(EntryCategory|int|string $category): Builder
    {
        $entryModel = $this->entryModelClass();

        return $entryModel::inCategory($category);
    }

    /**
     * Get the Entry model class.
     *
     * @return class-string<Entry>
     */
    public function getEntryModel(): string
    {
        return $this->entryModelClass();
    }

    /**
     * Get the EntryCategory model class.
     *
     * @return class-string<EntryCategory>
     */
    public function getCategoryModel(): string
    {
        return $this->categoryModelClass();
    }

    /**
     * Get the EntryContent model class.
     *
     * @return class-string<EntryContent>
     */
    public function getContentModel(): string
    {
        return $this->contentModelClass();
    }

    /**
     * @return class-string<Entry>
     */
    protected function entryModelClass(): string
    {
        $class = config('entry-vault.models.entry', Entry::class);

        return is_string($class) && is_a($class, Entry::class, true)
            ? $class
            : Entry::class;
    }

    /**
     * @return class-string<EntryCategory>
     */
    protected function categoryModelClass(): string
    {
        $class = config('entry-vault.models.category', EntryCategory::class);

        return is_string($class) && is_a($class, EntryCategory::class, true)
            ? $class
            : EntryCategory::class;
    }

    /**
     * @return class-string<EntryContent>
     */
    protected function contentModelClass(): string
    {
        $class = config('entry-vault.models.content', EntryContent::class);

        return is_string($class) && is_a($class, EntryContent::class, true)
            ? $class
            : EntryContent::class;
    }
}
