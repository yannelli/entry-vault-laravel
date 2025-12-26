<?php

namespace Yannelli\EntryVault\Exceptions;

class InvalidStateTransition extends EntryVaultException
{
    public static function missingRequiredField(string $field): self
    {
        return new self("Cannot transition: missing required field '{$field}'");
    }

    public static function notAllowed(string $from, string $to): self
    {
        return new self("Transition from '{$from}' to '{$to}' is not allowed");
    }

    public static function preconditionFailed(string $message): self
    {
        return new self("Transition precondition failed: {$message}");
    }
}
