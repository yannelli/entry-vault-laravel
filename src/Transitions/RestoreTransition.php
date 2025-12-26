<?php

namespace Yannelli\EntryVault\Transitions;

use Spatie\ModelStates\Transition;
use Yannelli\EntryVault\Events\EntryRestored;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\States\Draft;

class RestoreTransition extends Transition
{
    public function __construct(
        private Entry $entry
    ) {}

    public function handle(): Entry
    {
        $this->entry->state = new Draft($this->entry);
        $this->entry->save();

        event(new EntryRestored($this->entry));

        return $this->entry;
    }
}
