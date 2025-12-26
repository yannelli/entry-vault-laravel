<?php

namespace Yannelli\EntryVault\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Yannelli\EntryVault\Enums\EntryVisibility;
use Yannelli\EntryVault\Facades\EntryVault;

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

                    $currentTeam = method_exists($user, 'currentTeam') ? $user->currentTeam() : null;
                    if ($currentTeam) {
                        $q2->where('team_type', $currentTeam->getMorphClass())
                            ->where('team_id', $currentTeam->getKey());
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
        // Check global authorization callback first
        if (! EntryVault::checkAuthorization($this)) {
            return false;
        }

        // Public entries are accessible to everyone
        if ($this->isPublic()) {
            return true;
        }

        // Check owner authorization using resolver
        if (EntryVault::hasOwnerResolver()) {
            if (EntryVault::checkOwnerAuthorization($user, $this)) {
                return true;
            }
        } else {
            // Fall back to default ownership check
            if ($this->isOwnedBy($user)) {
                return true;
            }
        }

        // Check team visibility
        if ($this->isTeamVisible() && $this->hasTeam()) {
            // Use team resolver if available
            $currentTeam = method_exists($user, 'currentTeam') ? $user->currentTeam() : null;
            if (EntryVault::hasTeamResolver()) {
                if ($currentTeam) {
                    return EntryVault::checkTeamAuthorization($currentTeam, $this);
                }
            }

            // Fall back to default team checks
            if (method_exists($user, 'belongsToTeam')) {
                return $user->belongsToTeam($this->team);
            }

            if (method_exists($user, 'teams')) {
                return $user->teams->contains('id', $this->team_id);
            }

            if ($currentTeam) {
                return $currentTeam->getKey() === $this->team_id;
            }
        }

        return false;
    }

    /**
     * Check if entry is accessible using all registered resolvers.
     * This is a more comprehensive check that includes custom resolvers.
     */
    public function isAuthorizedFor(Model $user): bool
    {
        // Check global authorization
        if (! EntryVault::checkAuthorization($this)) {
            return false;
        }

        // Check owner authorization
        if (EntryVault::hasOwnerResolver()) {
            if (EntryVault::checkOwnerAuthorization($user, $this)) {
                return true;
            }
        }

        // Check team authorization
        $currentTeam = method_exists($user, 'currentTeam') ? $user->currentTeam() : null;
        if (EntryVault::hasTeamResolver() && $currentTeam) {
            if (EntryVault::checkTeamAuthorization($currentTeam, $this)) {
                return true;
            }
        }

        // Check custom resolvers
        foreach (EntryVault::getCustomResolvers() as $name => $resolver) {
            if (EntryVault::checkCustomAuthorization($name, $user, $this)) {
                return true;
            }
        }

        // Fall back to standard accessibility check
        return $this->isAccessibleBy($user);
    }
}
