<?php

namespace Spatie\DiscordAlerts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendToDiscordChannelJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    public function __construct(
        public string $text,
        public string $webhookUrl,
        public ?string $username = null,
        public bool $tts = false,
        public ?string $avatar_url = null,
        public array|null $embeds = null
    ) {
    }

    public function handle(): void
    {
        $payload = [
            'content' => $this->text,
            'tts' => $this->tts,
        ];

        if (!empty($this->username)) {
            $payload['username'] = $this->username;
        }

        if (!empty($this->avatar_url)) {
            $payload['avatar_url'] = $this->avatar_url;
        }

        if (!empty($this->embeds)) {
            $payload['embeds'] = $this->embeds;
        }

        Http::post($this->webhookUrl, $payload);
    }
}