<?php

namespace Yannelli\EntryVault\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Yannelli\EntryVault\Traits\HasEntries;
use Yannelli\EntryVault\Traits\HasEntryCategories;

class User extends Model
{
    use HasEntries;
    use HasEntryCategories;

    protected $fillable = ['name', 'email'];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    public function currentTeam(): ?Team
    {
        return $this->teams()->first();
    }
}
