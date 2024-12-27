<?php

namespace Spatie\DiscordAlerts\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static self to(string $text)
 * @method static self delayMinutes(int $minutes)
 * @method static self delayHours(int $hours)
 * @method static void message(string $text, array $embeds = null)
 *
 * @see \Spatie\DiscordAlerts\DiscordAlert
 */
class DiscordAlert extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-discord-alerts';
    }
}
