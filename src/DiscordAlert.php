<?php

namespace Spatie\DiscordAlerts;

class DiscordAlert
{
    protected string $webhookUrlName = 'default';
    protected $username = null;
    protected $avatarUrl = null;

    public function to(string $webhookUrlName): self
    {
        $this->webhookUrlName = $webhookUrlName;

        return $this;
    }

    public function username(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function avatar(string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;

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
                $embeds[$key]['color'] = hexdec(str_replace('#', '', $embed['color'])) ;
            }
        }

        $jobArguments = [
            'text' => $text,
            'webhookUrl' => $webhookUrl,
            'embeds' => $embeds,
        ];

        if($this->username != null) { $jobArguments['username'] = $this->username; }
        if($this->avatarUrl != null) { $jobArguments['avatarUrl'] = $this->avatarUrl; }

        $job = Config::getJob($jobArguments);

        dispatch($job);
    }

    private function parseNewline(string $text): string
    {
        return str_replace('\n', PHP_EOL, $text);
    }
}
