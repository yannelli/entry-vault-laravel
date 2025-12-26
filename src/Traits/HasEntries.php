<?php

namespace Yannelli\EntryVault\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasEntries
{
    public function entries(): MorphMany
    {
        return $this->morphMany(
            config('entry-vault.models.entry'),
            'owner'
        );
    }

    public function draftEntries(): MorphMany
    {
        return $this->entries()->draft();
    }

    public function publishedEntries(): MorphMany
    {
        return $this->entries()->published();
    }

    public function archivedEntries(): MorphMany
    {
        return $this->entries()->archived();
    }

    public function entryTemplates(): MorphMany
    {
        return $this->entries()->templates();
    }

    public function featuredEntries(): MorphMany
    {
        return $this->entries()->featured();
    }
}
