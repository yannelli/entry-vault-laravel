<?php

namespace Yannelli\EntryVault\Tests\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JetstreamUser extends User
{
    protected $fillable = ['name', 'email', 'current_team_id'];

    /**
     * Mirror Laravel Jetstream: currentTeam() is a BelongsTo relationship,
     * so calling the method returns a Relation rather than a Team model.
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }
}
