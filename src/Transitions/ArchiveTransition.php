<?php

namespace Yannelli\EntryVault\Transitions;

use Spatie\ModelStates\Transition;
use Yannelli\EntryVault\Events\EntryArchived;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\States\Archived;

class ArchiveTransition extends Transition
{
    public function __construct(
        private Entry $entry
    ) {}

    public function handle(): Entry
    {
        $this->entry->state = new Archived($this->entry);
        $this->entry->save();

        event(new EntryArchived($this->entry));

        return $this->entry;
    }
}
