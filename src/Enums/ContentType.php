<?php

namespace Yannelli\EntryVault\Enums;

enum ContentType: string
{
    case MARKDOWN = 'markdown';
    case HTML = 'html';
    case JSON = 'json';
    case TEXT = 'text';

    public function label(): string
    {
        return match ($this) {
            self::MARKDOWN => 'Markdown',
            self::HTML => 'HTML',
            self::JSON => 'JSON',
            self::TEXT => 'Plain Text',
        };
    }

    public static function isValid(string $type): bool
    {
        return in_array($type, array_column(self::cases(), 'value'), true);
    }
}
