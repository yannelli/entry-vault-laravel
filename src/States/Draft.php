<?php

namespace Yannelli\EntryVault\States;

class Draft extends EntryState
{
    public static $name = 'draft';

    public function label(): string
    {
        return 'Draft';
    }

    public function color(): string
    {
        return 'gray';
    }
}
