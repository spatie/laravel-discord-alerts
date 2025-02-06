<?php

namespace Spatie\DiscordAlerts\Exceptions;

use RuntimeException;

class UsernameNotValid extends RunTimeException
{
    public static function make(string $name): self
    {
        return new self("Invalid username `{$name}`. Allowed: letters, numbers, spaces, underscores, dashes (max 32 chars).");
    }
}
