<?php

namespace Yannelli\EntryVault\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Yannelli\EntryVault\Models\Entry;

class EntryUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Entry $entry
    ) {}
}
