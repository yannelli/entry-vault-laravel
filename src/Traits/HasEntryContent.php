<?php

namespace Yannelli\EntryVault\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasEntryContent
{
    public function entryContent(): MorphOne
    {
        return $this->morphOne(
            config('entry-vault.models.content'),
            'contentable'
        );
    }

    public function entryContents(): MorphMany
    {
        return $this->morphMany(
            config('entry-vault.models.content'),
            'contentable'
        );
    }
}
