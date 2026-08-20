<?php

namespace Spatie\DiscordAlerts\Exceptions;

use RuntimeException;

class AttachmentNotReadable extends RuntimeException
{
    public static function make(string $path): self
    {
        return new self("The attachment at `{$path}` does not exist or could not be read.");
    }
}
