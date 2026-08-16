<?php

namespace Spatie\DiscordAlerts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use RuntimeException;

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
     * @param array<int, array{type: string, name: string, path?: string, content?: string, headers?: array<string, string>}>|null $attachments
     */
    public function __construct(
        public string $text,
        public string $webhookUrl,
        public ?string $username = null,
        public bool $tts = false,
        public ?string $avatar_url = null,
        public array|null $embeds = null,
        public array|null $attachments = null
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

        foreach ($this->attachments as $index => $attachment) {
            $request = $this->attachFile($request, $index, $attachment);
        }

        $request->post($this->webhookUrl, [
            'payload_json' => json_encode($payload),
        ]);
    }

    /**
     * @param array{type: string, name: string, path?: string, content?: string, headers?: array<string, string>} $attachment
     */
    private function attachFile(PendingRequest $request, int $index, array $attachment): PendingRequest
    {
        $headers = $attachment['headers'] ?? [];

        if ($attachment['type'] === 'content') {
            return $request->attach("files[{$index}]", $attachment['content'] ?? '', $attachment['name'], $headers);
        }

        $path = $attachment['path'] ?? '';

        if (! is_readable($path)) {
            throw new RuntimeException("Discord attachment path is not readable: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Discord attachment path could not be read: {$path}");
        }

        return $request->attach("files[{$index}]", $contents, $attachment['name'], $headers);
    }
}
