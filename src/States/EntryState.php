<?php

namespace Yannelli\EntryVault\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;
use Yannelli\EntryVault\Transitions\ArchiveTransition;
use Yannelli\EntryVault\Transitions\PublishTransition;
use Yannelli\EntryVault\Transitions\RestoreTransition;
use Yannelli\EntryVault\Transitions\UnpublishTransition;

abstract class EntryState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->allowTransition(Draft::class, Published::class, PublishTransition::class)
            ->allowTransition(Draft::class, Archived::class, ArchiveTransition::class)
            ->allowTransition(Published::class, Draft::class, UnpublishTransition::class)
            ->allowTransition(Published::class, Archived::class, ArchiveTransition::class)
            ->allowTransition(Archived::class, Draft::class, RestoreTransition::class);
    }
}
