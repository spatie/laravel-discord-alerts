<?php

namespace Spatie\DiscordAlerts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Spatie\DiscordAlerts\Attachment;

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

    /**
     * @param array<int, array<string, mixed>>|null $embeds
     * @param array<int, Attachment>|null $attachments
     */
    public function __construct(
        public string $text,
        public string $webhookUrl,
        public ?string $username = null,
        public bool $tts = false,
        public ?string $avatar_url = null,
        public ?array $embeds = null,
        public ?array $attachments = null,
    ) {
    }

    public function handle(): void
    {
        $payload = [
            'content' => $this->text,
            'tts' => $this->tts,
        ];

        if (! empty($this->username)) {
            $payload['username'] = $this->username;
        }

        if (! empty($this->avatar_url)) {
            $payload['avatar_url'] = $this->avatar_url;
        }

        if (! empty($this->embeds)) {
            $payload['embeds'] = $this->embeds;
        }

        if (empty($this->attachments)) {
            Http::post($this->webhookUrl, $payload);

            return;
        }

        $request = Http::asMultipart();

        foreach (array_values($this->attachments) as $index => $attachment) {
            $request->attach("files[{$index}]", $attachment->contents(), $attachment->name, $attachment->headers());
        }

        $request->post($this->webhookUrl, [
            'payload_json' => json_encode($payload, JSON_THROW_ON_ERROR),
        ]);
    }
}
