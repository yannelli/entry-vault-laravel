<?php

namespace Yannelli\EntryVault\Models;

use Overtrue\LaravelVersionable\Version;

class EntryVersion extends Version
{
    protected $table = 'entry_versions';
}
