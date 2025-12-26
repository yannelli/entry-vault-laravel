<?php

namespace Yannelli\EntryVault\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Yannelli\EntryVault\Traits\HasEntries;
use Yannelli\EntryVault\Traits\HasEntryCategories;

class Team extends Model
{
    use HasEntries;
    use HasEntryCategories;

    protected $fillable = ['name'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
