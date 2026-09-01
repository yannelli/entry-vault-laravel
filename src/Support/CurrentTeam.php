<?php

namespace Yannelli\EntryVault\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

final class CurrentTeam
{
    /**
     * Resolve the user's current team.
     *
     * Supports both Laravel Jetstream-style `currentTeam()` relationships
     * (which return a Relation) and custom accessors that return a model.
     */
    public static function for(Model $user): ?Model
    {
        if (! method_exists($user, 'currentTeam')) {
            return null;
        }

        $result = $user->currentTeam();

        if ($result instanceof Relation) {
            $related = $result->getResults();

            return $related instanceof Model ? $related : null;
        }

        return $result instanceof Model ? $result : null;
    }
}
