<?php

namespace Yannelli\EntryVault\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class EntryState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->allowTransition(Draft::class, Published::class)
            ->allowTransition(Draft::class, Archived::class)
            ->allowTransition(Published::class, Draft::class)
            ->allowTransition(Published::class, Archived::class)
            ->allowTransition(Archived::class, Draft::class);
    }
}
