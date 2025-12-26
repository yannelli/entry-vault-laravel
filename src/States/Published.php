<?php

namespace Yannelli\EntryVault\States;

class Published extends EntryState
{
    public static $name = 'published';

    public function label(): string
    {
        return 'Published';
    }

    public function color(): string
    {
        return 'green';
    }
}
