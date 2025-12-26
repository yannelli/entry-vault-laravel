<?php

namespace Yannelli\EntryVault\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasEntryCategories
{
    public function entryCategories(): MorphMany
    {
        return $this->morphMany(
            config('entry-vault.models.category'),
            'owner'
        );
    }

    public function defaultEntryCategory()
    {
        return $this->entryCategories()->default()->first();
    }
}
