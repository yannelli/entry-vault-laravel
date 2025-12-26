<?php

namespace Yannelli\EntryVault\Exceptions;

use Exception;

class EntryVaultException extends Exception
{
    public static function invalidContentType(string $type): self
    {
        return new self("Invalid content type: {$type}");
    }

    public static function entryNotFound(string $identifier): self
    {
        return new self("Entry not found: {$identifier}");
    }

    public static function categoryNotFound(string $identifier): self
    {
        return new self("Category not found: {$identifier}");
    }

    public static function templateNotFound(string $identifier): self
    {
        return new self("Template not found: {$identifier}");
    }

    public static function notATemplate(string $identifier): self
    {
        return new self("Entry is not a template: {$identifier}");
    }
}
