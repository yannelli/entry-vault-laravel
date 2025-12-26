<?php

namespace Yannelli\EntryVault\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Overtrue\LaravelVersionable\Version;
use Yannelli\EntryVault\Models\Entry;

class EntryReverted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Entry $entry,
        public Version $fromVersion
    ) {}
}
