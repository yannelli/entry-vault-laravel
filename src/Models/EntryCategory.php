<?php

namespace Yannelli\EntryVault\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Yannelli\EntryVault\Support\CurrentTeam;
use Yannelli\EntryVault\Traits\HasOwner;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $color
 * @property bool $is_system
 * @property bool $is_default
 * @property string|null $owner_type
 * @property int|null $owner_id
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class EntryCategory extends Model
{
    use HasFactory;
    use HasOwner;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_system',
        'is_default',
        'owner_type',
        'owner_id',
        'display_order',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_default' => 'boolean',
        'display_order' => 'integer',
    ];

    public function getTable(): string
    {
        return config('entry-vault.tables.categories', 'entry_categories');
    }

    protected static function booted(): void
    {
        static::creating(function (EntryCategory $category) {
            if (empty($category->uuid)) {
                $category->uuid = (string) Str::uuid();
            }

            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name, $category);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?EntryCategory $category = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        $query = static::query()
            ->where('slug', $slug);

        if ($category) {
            $query->where('owner_type', $category->owner_type)
                ->where('owner_id', $category->owner_id);

            if ($category->exists) {
                $query->where('id', '!=', $category->id);
            }
        }

        while ($query->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;

            $query = static::query()
                ->where('slug', $slug);

            if ($category) {
                $query->where('owner_type', $category->owner_type)
                    ->where('owner_id', $category->owner_id);

                if ($category->exists) {
                    $query->where('id', '!=', $category->id);
                }
            }
        }

        return $slug;
    }

    public function entries(): HasMany
    {
        return $this->hasMany(config('entry-vault.models.entry'), 'category_id');
    }

    public function scopeAccessibleBy(Builder $query, Model $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            // System categories
            $q->where('is_system', true);

            // User's own categories
            $q->orWhere(function (Builder $q2) use ($user) {
                $q2->where('owner_type', $user->getMorphClass())
                    ->where('owner_id', $user->getKey());
            });

            // User's team categories (if applicable)
            $currentTeam = CurrentTeam::for($user);
            if ($currentTeam) {
                $q->orWhere(function (Builder $q2) use ($currentTeam) {
                    $q2->where('owner_type', $currentTeam->getMorphClass())
                        ->where('owner_id', $currentTeam->getKey());
                });
            } else {
                $q->orWhere(function (Builder $q2) use ($user) {
                    $teams = CurrentTeam::memberships($user);
                    $teamType = $teams->first()?->getMorphClass();
                    if ($teamType) {
                        $q2->where('owner_type', $teamType)
                            ->whereIn('owner_id', $teams->pluck('id'));
                    }
                });
            }
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order', 'asc')
            ->orderBy('name', 'asc');
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public static function findBySlug(string $slug, ?Model $owner = null): ?static
    {
        $query = static::query()->where('slug', $slug);

        if ($owner) {
            $query->where('owner_type', $owner->getMorphClass())
                ->where('owner_id', $owner->getKey());
        } else {
            $query->whereNull('owner_type')
                ->whereNull('owner_id');
        }

        $category = $query->first();

        return $category instanceof static ? $category : null;
    }

    public static function findByUuid(string $uuid): ?static
    {
        $category = static::query()->where('uuid', $uuid)->first();

        return $category instanceof static ? $category : null;
    }
}
