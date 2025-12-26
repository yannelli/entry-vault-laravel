<?php

namespace Yannelli\EntryVault\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Yannelli\EntryVault\Enums\EntryVisibility;

trait HasVisibility
{
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', EntryVisibility::PUBLIC->value);
    }

    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('visibility', EntryVisibility::PRIVATE->value);
    }

    public function scopeTeamVisible(Builder $query): Builder
    {
        return $query->where('visibility', EntryVisibility::TEAM->value);
    }

    public function scopeVisibleTo(Builder $query, Model $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            // Public entries are always visible
            $q->where('visibility', EntryVisibility::PUBLIC->value);

            // Entries owned by this user are always visible
            $q->orWhere(function (Builder $q2) use ($user) {
                $q2->where('owner_type', $user->getMorphClass())
                    ->where('owner_id', $user->getKey());
            });

            // Team entries if user belongs to team
            if (method_exists($user, 'teams') || method_exists($user, 'currentTeam')) {
                $q->orWhere(function (Builder $q2) use ($user) {
                    $q2->where('visibility', EntryVisibility::TEAM->value);

                    if (method_exists($user, 'currentTeam') && $user->currentTeam) {
                        $q2->where('team_type', $user->currentTeam->getMorphClass())
                            ->where('team_id', $user->currentTeam->getKey());
                    } elseif (method_exists($user, 'teams')) {
                        $teamIds = $user->teams->pluck('id');
                        $teamType = $user->teams->first()?->getMorphClass();
                        if ($teamType) {
                            $q2->where('team_type', $teamType)
                                ->whereIn('team_id', $teamIds);
                        }
                    }
                });
            }
        });
    }

    public function isPublic(): bool
    {
        return $this->visibility === EntryVisibility::PUBLIC->value
            || $this->visibility === EntryVisibility::PUBLIC;
    }

    public function isPrivate(): bool
    {
        return $this->visibility === EntryVisibility::PRIVATE->value
            || $this->visibility === EntryVisibility::PRIVATE;
    }

    public function isTeamVisible(): bool
    {
        return $this->visibility === EntryVisibility::TEAM->value
            || $this->visibility === EntryVisibility::TEAM;
    }

    public function isAccessibleBy(Model $user): bool
    {
        // Public entries are accessible to everyone
        if ($this->isPublic()) {
            return true;
        }

        // Check if user is the owner
        if ($this->isOwnedBy($user)) {
            return true;
        }

        // Check team visibility
        if ($this->isTeamVisible() && $this->hasTeam()) {
            if (method_exists($user, 'belongsToTeam')) {
                return $user->belongsToTeam($this->team);
            }

            if (method_exists($user, 'teams')) {
                return $user->teams->contains('id', $this->team_id);
            }

            if (method_exists($user, 'currentTeam') && $user->currentTeam) {
                return $user->currentTeam->getKey() === $this->team_id;
            }
        }

        return false;
    }
}
