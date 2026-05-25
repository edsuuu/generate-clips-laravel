<?php

declare(strict_types=1);

namespace App\Services\VideoProcessor\Data;

final readonly class SubtitleFullData
{
    /**
     * @param  array<string, mixed>  $transcriptJson
     */
    public function __construct(
        public string $sourcePath,
        public string $outputPath,
        public array $transcriptJson,
        public string $callbackUrl,
        public ?string $transcriptText = null,
        public ?string $bucket = null,
        public ?string $callbackToken = null,
        public string $callbackHeader = 'Authorization',
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'transcript_text' => $this->transcriptText,
            'transcript_json' => $this->transcriptJson,
            'source_file' => ['bucket' => $this->bucket, 'path' => $this->sourcePath],
            'output' => ['bucket' => $this->bucket, 'path' => $this->outputPath],
            'callback_url' => $this->callbackUrl,
            'callback_token' => $this->callbackToken,
            'callback_header' => $this->callbackHeader,
        ];
    }
}
