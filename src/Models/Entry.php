<?php

namespace Yannelli\EntryVault\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;
use Spatie\ModelStates\HasStates;
use Yannelli\EntryVault\Enums\EntryVisibility;
use Yannelli\EntryVault\Events\EntryCreated;
use Yannelli\EntryVault\Events\EntryCreatedFromTemplate;
use Yannelli\EntryVault\Events\EntryDeleted;
use Yannelli\EntryVault\Events\EntryUpdated;
use Yannelli\EntryVault\Exceptions\EntryVaultException;
use Yannelli\EntryVault\States\Archived;
use Yannelli\EntryVault\States\Draft;
use Yannelli\EntryVault\States\EntryState;
use Yannelli\EntryVault\States\Published;
use Yannelli\EntryVault\Traits\HasOwner;
use Yannelli\EntryVault\Traits\HasTeam;
use Yannelli\EntryVault\Traits\HasVisibility;

class Entry extends Model
{
    use HasFactory;
    use HasOwner;
    use HasStates;
    use HasTeam;
    use HasVisibility;
    use SoftDeletes;
    use Versionable;

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'description',
        'keywords',
        'state',
        'visibility',
        'category_id',
        'is_template',
        'is_featured',
        'template_id',
        'owner_type',
        'owner_id',
        'team_type',
        'team_id',
        'created_by',
        'updated_by',
        'display_order',
        'published_at',
    ];

    protected array $versionable = [
        'title',
        'description',
        'keywords',
        'state',
        'visibility',
        'category_id',
    ];

    protected VersionStrategy $versionStrategy = VersionStrategy::SNAPSHOT;

    protected $casts = [
        'state' => EntryState::class,
        'keywords' => 'array',
        'is_template' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'display_order' => 'integer',
    ];

    protected $dispatchesEvents = [
        'created' => EntryCreated::class,
        'updated' => EntryUpdated::class,
        'deleted' => EntryDeleted::class,
    ];

    public function getTable(): string
    {
        return config('entry-vault.tables.entries', 'entries');
    }

    protected static function booted(): void
    {
        static::creating(function (Entry $entry) {
            if (empty($entry->uuid)) {
                $entry->uuid = (string) Str::uuid();
            }

            if (empty($entry->slug)) {
                $entry->slug = static::generateUniqueSlug($entry->title, $entry);
            }

            if (empty($entry->visibility)) {
                $entry->visibility = config('entry-vault.default_visibility', 'private');
            }

            if (empty($entry->state)) {
                $entry->state = config('entry-vault.default_state', 'draft');
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?Entry $entry = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        $query = static::query()->where('slug', $slug);

        if ($entry) {
            $query->where('owner_type', $entry->owner_type)
                ->where('owner_id', $entry->owner_id);

            if ($entry->exists) {
                $query->where('id', '!=', $entry->id);
            }
        }

        while ($query->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;

            $query = static::query()->where('slug', $slug);

            if ($entry) {
                $query->where('owner_type', $entry->owner_type)
                    ->where('owner_id', $entry->owner_id);

                if ($entry->exists) {
                    $query->where('id', '!=', $entry->id);
                }
            }
        }

        return $slug;
    }

    // Relationships
    public function contents(): HasMany
    {
        return $this->hasMany(config('entry-vault.models.content'), 'entry_id')
            ->orderBy('order');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(config('entry-vault.models.category'), 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('entry-vault.user_model'), 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(config('entry-vault.user_model'), 'updated_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(static::class, 'template_id');
    }

    public function derivedEntries(): HasMany
    {
        return $this->hasMany(static::class, 'template_id');
    }

    // State scopes
    public function scopeDraft(Builder $query): Builder
    {
        return $query->whereState('state', Draft::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereState('state', Published::class);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereState('state', Archived::class);
    }

    // Template scopes
    public function scopeTemplates(Builder $query): Builder
    {
        return $query->where('is_template', true);
    }

    public function scopeNotTemplates(Builder $query): Builder
    {
        return $query->where('is_template', false);
    }

    public function scopeSystemTemplates(Builder $query): Builder
    {
        return $query->where('is_template', true)
            ->whereNull('owner_type')
            ->whereNull('owner_id');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeStarters(Builder $query): Builder
    {
        return $query->systemTemplates()->featured();
    }

    // Category scopes
    public function scopeInCategory(Builder $query, EntryCategory|int|string $category): Builder
    {
        if ($category instanceof EntryCategory) {
            return $query->where('category_id', $category->id);
        }

        if (is_int($category)) {
            return $query->where('category_id', $category);
        }

        // String: lookup by slug or uuid
        return $query->whereHas('category', function (Builder $q) use ($category) {
            $q->where('slug', $category)->orWhere('uuid', $category);
        });
    }

    public function scopeUncategorized(Builder $query): Builder
    {
        return $query->whereNull('category_id');
    }

    // Ordering
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order', 'asc')
            ->orderBy('title', 'asc');
    }

    // Accessibility scope
    public function scopeAccessibleBy(Builder $query, Model $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            // Public entries
            $q->where('visibility', EntryVisibility::PUBLIC->value);

            // User's own entries
            $q->orWhere(function (Builder $q2) use ($user) {
                $q2->where('owner_type', $user->getMorphClass())
                    ->where('owner_id', $user->getKey());
            });

            // Team entries
            $currentTeam = method_exists($user, 'currentTeam') ? $user->currentTeam() : null;
            if ($currentTeam) {
                $q->orWhere(function (Builder $q2) use ($currentTeam) {
                    $q2->where('visibility', EntryVisibility::TEAM->value)
                        ->where('team_type', $currentTeam->getMorphClass())
                        ->where('team_id', $currentTeam->getKey());
                });
            } elseif (method_exists($user, 'teams')) {
                $q->orWhere(function (Builder $q2) use ($user) {
                    $teamType = $user->teams->first()?->getMorphClass();
                    if ($teamType) {
                        $q2->where('visibility', EntryVisibility::TEAM->value)
                            ->where('team_type', $teamType)
                            ->whereIn('team_id', $user->teams->pluck('id'));
                    }
                });
            }

            // System templates (always accessible for viewing)
            $q->orWhere(function (Builder $q2) {
                $q2->where('is_template', true)
                    ->whereNull('owner_type')
                    ->whereNull('owner_id');
            });
        });
    }

    // Template operations
    public static function createFromTemplate(Entry $template, array $attributes = []): static
    {
        if (! $template->is_template) {
            throw EntryVaultException::notATemplate($template->uuid);
        }

        $entry = new static;

        $entry->fill([
            'title' => $attributes['title'] ?? $template->title,
            'description' => $attributes['description'] ?? $template->description,
            'keywords' => $attributes['keywords'] ?? $template->keywords,
            'visibility' => $attributes['visibility'] ?? config('entry-vault.default_visibility', 'private'),
            'category_id' => $attributes['category_id'] ?? $template->category_id,
            'template_id' => $template->id,
            'is_template' => false,
            'is_featured' => false,
            'display_order' => $attributes['display_order'] ?? 0,
        ]);

        // Set owner if provided
        if (isset($attributes['owner'])) {
            $entry->owner()->associate($attributes['owner']);
        }

        // Set team if provided
        if (isset($attributes['team'])) {
            $entry->team()->associate($attributes['team']);
        }

        // Set creator if provided
        if (isset($attributes['created_by'])) {
            $entry->created_by = $attributes['created_by'] instanceof Model
                ? $attributes['created_by']->getKey()
                : $attributes['created_by'];
        }

        $entry->save();

        // Copy contents from template
        foreach ($template->contents as $content) {
            $entry->contents()->create([
                'type' => $content->type,
                'body' => $content->body,
                'metadata' => $content->metadata,
                'order' => $content->order,
            ]);
        }

        event(new EntryCreatedFromTemplate($entry, $template));

        return $entry;
    }

    // Finders
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

    // State helpers
    public function isDraft(): bool
    {
        return $this->state instanceof Draft || $this->state === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->state instanceof Published || $this->state === 'published';
    }

    public function isArchived(): bool
    {
        return $this->state instanceof Archived || $this->state === 'archived';
    }

    // Template helpers
    public function isTemplate(): bool
    {
        return $this->is_template;
    }

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    public function isSystemTemplate(): bool
    {
        return $this->is_template && $this->isSystem();
    }

    public function wasCreatedFromTemplate(): bool
    {
        return ! is_null($this->template_id);
    }
}
