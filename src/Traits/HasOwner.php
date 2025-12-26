<?php

namespace Yannelli\EntryVault\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait HasOwner
{
    public function owner(): MorphTo
    {
        return $this->morphTo('owner');
    }

    public function scopeOwnedBy(Builder $query, Model $owner): Builder
    {
        return $query->where('owner_type', $owner->getMorphClass())
            ->where('owner_id', $owner->getKey());
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->whereNull('owner_type')
            ->whereNull('owner_id');
    }

    public function scopeNotSystem(Builder $query): Builder
    {
        return $query->whereNotNull('owner_type')
            ->whereNotNull('owner_id');
    }

    public function isOwnedBy(Model $owner): bool
    {
        return $this->owner_type === $owner->getMorphClass()
            && $this->owner_id === $owner->getKey();
    }

    public function isSystem(): bool
    {
        return is_null($this->owner_type) && is_null($this->owner_id);
    }
}
