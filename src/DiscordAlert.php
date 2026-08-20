<?php

namespace Spatie\DiscordAlerts;

use SplFileInfo;
use Spatie\DiscordAlerts\Exceptions\UsernameNotValid;

class DiscordAlert
{
    protected string $webhookUrlName = 'default';
    protected int $delay = 0;
    protected ?string $username = null;
    protected bool $tts = false;
    protected ?string $avatarUrl = null;

    /** @var array<int, Attachment> */
    protected array $attachments = [];

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
        if (! preg_match('/^[a-zA-Z0-9 _-]{1,32}$/', $username)) {
            throw UsernameNotValid::make($username);
        }

        $this->username = $username;

        return $this;
    }

    public function enableTts(bool $enabled = false): self
    {
        $this->tts = $enabled;

        return $this;
    }

    public function withAvatar(string $avatarName): self
    {
        $this->avatarUrl = Config::getAvatarUrl($avatarName);

        return $this;
    }

    public function attach(string|SplFileInfo $file, ?string $name = null, ?string $mimeType = null): self
    {
        $this->attachments[] = Attachment::fromPath($file, $name, $mimeType);

        return $this;
    }

    public function attachData(string $content, string $name, ?string $mimeType = null): self
    {
        $this->attachments[] = Attachment::fromData($content, $name, $mimeType);

        return $this;
    }

    /**
     * @param array<int, array<string, mixed>> $embeds
     */
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

        if (! empty($this->attachments)) {
            $jobArguments['attachments'] = $this->attachments;
        }

        if (! empty($this->username)) {
            $jobArguments['username'] = $this->username;
        }

        $avatarUrl = $this->avatarUrl ?? Config::getAvatarUrl('default');

        if ($avatarUrl) {
            $jobArguments['avatar_url'] = $avatarUrl;
        }

        $job = Config::getJob($jobArguments);

        if ($queue = Config::getQueue()) {
            $job->onQueue($queue);
        }

        $this->attachments = [];

        dispatch($job)->delay(now()->addMinutes($this->delay))->onConnection(Config::getConnection());
    }

    private function parseNewline(string $text): string
    {
        return str_replace('\n', PHP_EOL, $text);
    }
}
