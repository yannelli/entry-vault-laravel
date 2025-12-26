<?php

namespace Yannelli\EntryVault\States;

class Archived extends EntryState
{
    public static $name = 'archived';

    public function label(): string
    {
        return 'Archived';
    }

    public function color(): string
    {
        return 'red';
    }
}
