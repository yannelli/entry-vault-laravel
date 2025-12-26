<?php

namespace Yannelli\EntryVault\Facades;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\Models\EntryCategory;

/**
 * @method static \Yannelli\EntryVault\EntryVault authorize(Closure $callback)
 * @method static \Yannelli\EntryVault\EntryVault resolveOwner(string $model, ?Closure $authorize = null)
 * @method static \Yannelli\EntryVault\EntryVault resolveTeam(string $model, ?Closure $authorize = null)
 * @method static \Yannelli\EntryVault\EntryVault resolveCustom(string $name, string $model, Closure $authorize)
 * @method static bool checkAuthorization(Entry $entry)
 * @method static bool checkOwnerAuthorization(Model $owner, Entry $entry)
 * @method static bool checkTeamAuthorization(Model $team, Entry $entry)
 * @method static bool checkCustomAuthorization(string $name, Model $model, Entry $entry)
 * @method static string|null getOwnerModel()
 * @method static string|null getTeamModel()
 * @method static bool hasOwnerResolver()
 * @method static bool hasTeamResolver()
 * @method static bool hasCustomResolver(string $name)
 * @method static array getCustomResolvers()
 * @method static \Yannelli\EntryVault\EntryVault flushResolvers()
 * @method static Entry create(array $attributes)
 * @method static Entry|null findBySlug(string $slug, ?Model $owner = null)
 * @method static Entry|null findByUuid(string $uuid)
 * @method static Entry findByUuidOrFail(string $uuid)
 * @method static Builder templates()
 * @method static Builder systemTemplates()
 * @method static Builder starters()
 * @method static Builder startersInCategory(EntryCategory|int|string $category)
 * @method static Builder categories()
 * @method static Builder categoriesFor(Model $user)
 * @method static EntryCategory|null defaultCategory()
 * @method static EntryCategory|null findCategoryBySlug(string $slug, ?Model $owner = null)
 * @method static EntryCategory|null findCategoryByUuid(string $uuid)
 * @method static Builder accessibleBy(Model $user)
 * @method static Builder publicEntries()
 * @method static Builder entriesFor(Model $owner)
 * @method static Entry createFromTemplate(Entry $template, array $attributes = [])
 * @method static Builder published()
 * @method static Builder drafts()
 * @method static Builder archived()
 * @method static Builder inCategory(EntryCategory|int|string $category)
 * @method static string getEntryModel()
 * @method static string getCategoryModel()
 * @method static string getContentModel()
 *
 * @see \Yannelli\EntryVault\EntryVault
 */
class EntryVault extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Yannelli\EntryVault\EntryVault::class;
    }
}
