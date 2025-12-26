<?php

namespace Yannelli\EntryVault\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait HasTeam
{
    public function team(): MorphTo
    {
        return $this->morphTo('team');
    }

    public function scopeForTeam(Builder $query, Model $team): Builder
    {
        return $query->where('team_type', $team->getMorphClass())
            ->where('team_id', $team->getKey());
    }

    public function scopeWithoutTeam(Builder $query): Builder
    {
        return $query->whereNull('team_type')
            ->whereNull('team_id');
    }

    public function hasTeam(): bool
    {
        return ! is_null($this->team_type) && ! is_null($this->team_id);
    }

    public function belongsToTeam(Model $team): bool
    {
        return $this->team_type === $team->getMorphClass()
            && $this->team_id === $team->getKey();
    }
}
