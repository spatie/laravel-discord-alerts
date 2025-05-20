<?php

namespace Spatie\DiscordAlerts;

use Spatie\DiscordAlerts\Exceptions\AvatarUrlNotValid;
use Spatie\DiscordAlerts\Exceptions\JobClassDoesNotExist;
use Spatie\DiscordAlerts\Exceptions\WebhookDoesNotExist;
use Spatie\DiscordAlerts\Exceptions\WebhookUrlNotValid;
use Spatie\DiscordAlerts\Jobs\SendToDiscordChannelJob;

class Config
{
    public static function getJob(array $arguments): SendToDiscordChannelJob
    {
        $jobClass = config('discord-alerts.job');

        if (is_null($jobClass) || ! class_exists($jobClass)) {
            throw JobClassDoesNotExist::make($jobClass);
        }

        return app($jobClass, $arguments);
    }

    public static function getWebhookUrl(string $name): string
    {
        if (filter_var($name, FILTER_VALIDATE_URL)) {
            return $name;
        }

        $url = config("discord-alerts.webhook_urls.{$name}");

        if (is_null($url)) {
            throw WebhookDoesNotExist::make($name);
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw WebhookUrlNotValid::make($name, $url);
        }

        return $url;
    }

    public static function getAvatarUrl(string $name): ?string
    {
        $url = config("discord-alerts.avatar_urls.{$name}", '');

        if ($url === '') {
            return null;
        }

        if (! preg_match('/^https:\/\//', $url)) {
            throw AvatarUrlNotValid::invalidProtocol($url);
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw AvatarUrlNotValid::invalidUrl($url);
        }

        return $url;
    }

    public static function getConnection(): string
    {
        $connection = config("discord-alerts.queue_connection");

        if (is_null($connection)) {
            $connection = config("queue.default");
        }

        return $connection;
    }

    public static function getQueue(): ?string
    {
        $queueName = config("discord-alerts.queue");

        if (! $queueName) {
            return null;
        }

        return $queueName;
    }
}
