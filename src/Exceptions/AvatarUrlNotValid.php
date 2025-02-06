<?php

namespace Spatie\DiscordAlerts\Exceptions;

use Exception;

class AvatarUrlNotValid extends Exception
{
    public static function invalidUrl(string $url): self
    {
        return new self("Invalid avatar URL: {$url}");
    }

    public static function invalidProtocol(string $url): self
    {
        return new self("Invalid avatar URL: {$url}. Must use HTTPS.");
    }
}
