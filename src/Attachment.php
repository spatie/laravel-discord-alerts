<?php

namespace Spatie\DiscordAlerts;

use SplFileInfo;
use Spatie\DiscordAlerts\Exceptions\AttachmentNotReadable;

class Attachment
{
    protected function __construct(
        public readonly string $name,
        public readonly ?string $path = null,
        public readonly ?string $content = null,
        public readonly ?string $mimeType = null,
    ) {
    }

    public static function fromPath(string|SplFileInfo $file, ?string $name = null, ?string $mimeType = null): self
    {
        $path = $file instanceof SplFileInfo ? $file->getPathname() : $file;

        if (! is_readable($path)) {
            throw AttachmentNotReadable::make($path);
        }

        return new self(
            name: static::sanitizeName($name ?? basename($path)),
            path: $path,
            mimeType: $mimeType,
        );
    }

    public static function fromData(string $content, string $name, ?string $mimeType = null): self
    {
        return new self(
            name: static::sanitizeName($name),
            content: $content,
            mimeType: $mimeType,
        );
    }

    public function contents(): string
    {
        if ($this->content !== null) {
            return $this->content;
        }

        $contents = @file_get_contents((string) $this->path);

        if ($contents === false) {
            throw AttachmentNotReadable::make((string) $this->path);
        }

        return $contents;
    }

    /**
     * When no mime type is given, Guzzle will guess one based on the file name.
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
        if (! $this->mimeType) {
            return [];
        }

        return ['Content-Type' => $this->mimeType];
    }

    protected static function sanitizeName(string $name): string
    {
        $name = preg_replace('#[\x00-\x1F\x7F"\\\\/]#', '', $name) ?? '';

        return $name === '' ? 'attachment' : $name;
    }
}
