<?php

namespace Yannelli\EntryVault\Transitions;

use Spatie\ModelStates\Transition;
use Yannelli\EntryVault\Events\EntryUnpublished;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\States\Draft;

class UnpublishTransition extends Transition
{
    public function __construct(
        private Entry $entry
    ) {}

    public function handle(): Entry
    {
        $this->entry->state = new Draft($this->entry);
        $this->entry->published_at = null;
        $this->entry->save();

        event(new EntryUnpublished($this->entry));

        return $this->entry;
    }
}
