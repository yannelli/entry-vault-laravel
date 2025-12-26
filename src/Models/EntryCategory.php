<?php

namespace Yannelli\EntryVault\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Yannelli\EntryVault\Traits\HasOwner;

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
            if (method_exists($user, 'currentTeam') && $user->currentTeam) {
                $q->orWhere(function (Builder $q2) use ($user) {
                    $q2->where('owner_type', $user->currentTeam->getMorphClass())
                        ->where('owner_id', $user->currentTeam->getKey());
                });
            } elseif (method_exists($user, 'teams')) {
                $q->orWhere(function (Builder $q2) use ($user) {
                    $teamType = $user->teams->first()?->getMorphClass();
                    if ($teamType) {
                        $q2->where('owner_type', $teamType)
                            ->whereIn('owner_id', $user->teams->pluck('id'));
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

        return $query->first();
    }

    public static function findByUuid(string $uuid): ?static
    {
        return static::where('uuid', $uuid)->first();
    }
}
