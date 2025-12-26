<?php

namespace Yannelli\EntryVault\Transitions;

use Spatie\ModelStates\Transition;
use Yannelli\EntryVault\Events\EntryPublished;
use Yannelli\EntryVault\Exceptions\InvalidStateTransition;
use Yannelli\EntryVault\Models\Entry;
use Yannelli\EntryVault\States\Published;

class PublishTransition extends Transition
{
    public function __construct(
        private Entry $entry
    ) {}

    public function handle(): Entry
    {
        if (empty($this->entry->title)) {
            throw InvalidStateTransition::missingRequiredField('title');
        }

        $this->entry->state = new Published($this->entry);
        $this->entry->published_at = now();
        $this->entry->save();

        event(new EntryPublished($this->entry));

        return $this->entry;
    }
}
