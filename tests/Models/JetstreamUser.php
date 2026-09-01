<?php

namespace Yannelli\EntryVault\Tests\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JetstreamUser extends User
{
    protected $table = 'users';

    protected $fillable = ['name', 'email', 'current_team_id'];

    /**
     * Mirror Laravel Jetstream: currentTeam() is a BelongsTo relationship,
     * so calling the method returns a Relation rather than a Team model.
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user', 'user_id', 'team_id');
    }
}
