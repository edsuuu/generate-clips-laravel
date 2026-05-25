<?php

declare(strict_types=1);

namespace App\Services\VideoProcessor\Data;

final readonly class IngestVideoData
{
    public function __construct(
        public string $videoId,
        public string $url,
        public string $callbackUrl,
        public ?string $callbackToken = null,
        public string $callbackHeader = 'Authorization',
        public bool $transcribe = true,
        public bool $validateTranscript = true,
        public bool $uploadOriginalToMinio = true,
        public ?string $llm = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'video_id' => $this->videoId,
            'url' => $this->url,
            'callback_url' => $this->callbackUrl,
            'callback_token' => $this->callbackToken,
            'callback_header' => $this->callbackHeader,
            'options' => [
                'transcribe' => $this->transcribe,
                'validate_transcript' => $this->validateTranscript,
                'upload_original_to_minio' => $this->uploadOriginalToMinio,
                'llm' => $this->llm,
            ],
        ];
    }
}
