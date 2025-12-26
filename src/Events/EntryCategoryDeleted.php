<?php

namespace Yannelli\EntryVault\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Yannelli\EntryVault\Models\EntryCategory;

class EntryCategoryDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public EntryCategory $category
    ) {}
}
