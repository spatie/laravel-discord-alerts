<?php

namespace Spatie\DiscordAlerts;

class DiscordAlert
{
    protected string $webhookUrlName = 'default';
    protected int $delay = 0;
    protected ?string $username = null;
    protected bool $tts = false;
    protected ?string $avatarUrl = null;

    public function to(string $webhookUrlName): self
    {
        $this->webhookUrlName = $webhookUrlName;
        $this->delay = 0;

        return $this;
    }

    public function delayMinutes(int $minutes = 0): self
    {
        $this->delay += $minutes;

        return $this;
    }

    public function delayHours(int $hours = 0): self
    {
        $this->delay += $hours * 60;

        return $this;
    }

    public function withUsername(string $username): self
    {
        // Validate username: Allow letters, numbers, spaces, underscores, and dashes
        if (! preg_match('/^[a-zA-Z0-9 _-]{1,32}$/', $username)) {
            throw new \InvalidArgumentException("Invalid username. Allowed: letters, numbers, spaces, underscores, dashes (max 32 chars).");
        }

        $this->username = $username;

        return $this;
    }

    public function enableTTS(bool $enabled = false): self
    {
        $this->tts = $enabled;

        return $this;
    }

    public function withAvatar(string $avatarName): self
    {
        $this->avatarUrl = Config::getAvatarUrl($avatarName);

        return $this;
    }

    public function message(string $text, array $embeds = []): void
    {
        $webhookUrl = Config::getWebhookUrl($this->webhookUrlName);

        $text = $this->parseNewline($text);

        foreach ($embeds as $key => $embed) {
            if (array_key_exists('description', $embed)) {
                $embeds[$key]['description'] = $this->parseNewline($embeds[$key]['description']);
            }

            if (array_key_exists('color', $embed)) {
                $embeds[$key]['color'] = hexdec(str_replace('#', '', $embed['color']));
            }
        }

        $jobArguments = [
            'text' => $text,
            'webhookUrl' => $webhookUrl,
            'tts' => $this->tts,
            'embeds' => $embeds,
        ];

        if (! empty($this->username)) {
            $jobArguments['username'] = $this->username;
        }

        if (! empty($this->avatarUrl)) {
            $jobArguments['avatar_url'] = $this->avatarUrl;
        } else {
            $defaultAvatar = Config::getAvatarUrl('default');
            if (! empty($defaultAvatar)) {
                $jobArguments['avatar_url'] = $defaultAvatar;
            }
        }

        $job = Config::getJob($jobArguments);

        dispatch($job)->delay(now()->addMinutes($this->delay))->onConnection(Config::getConnection());
    }

    private function parseNewline(string $text): string
    {
        return str_replace('\n', PHP_EOL, $text);
    }
}
