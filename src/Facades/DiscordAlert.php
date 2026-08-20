<?php

namespace Spatie\DiscordAlerts\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static self to(string $webhookUrlName)
 * @method static self delayMinutes(int $minutes)
 * @method static self delayHours(int $hours)
 * @method static self withUsername(string $username)
 * @method static self enableTts(bool $enabled = false)
 * @method static self withAvatar(string $avatarName)
 * @method static self attach(string|\SplFileInfo $file, ?string $name = null, ?string $mimeType = null)
 * @method static self attachData(string $content, string $name, ?string $mimeType = null)
 * @method static void message(string $text, array<int, array<string, mixed>> $embeds = [])
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
