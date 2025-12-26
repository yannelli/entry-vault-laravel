<?php

namespace Yannelli\EntryVault\Enums;

enum EntryVisibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case TEAM = 'team';

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'Public',
            self::PRIVATE => 'Private',
            self::TEAM => 'Team',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PUBLIC => 'Anyone can view this entry',
            self::PRIVATE => 'Only the owner can view this entry',
            self::TEAM => 'Only team members can view this entry',
        };
    }
}
