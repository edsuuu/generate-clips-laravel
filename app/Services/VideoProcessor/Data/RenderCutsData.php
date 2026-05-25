<?php

declare(strict_types=1);

namespace App\Services\VideoProcessor\Data;

final readonly class RenderCutsData
{
    /**
     * @param  list<array<string, mixed>>  $cuts  Cada item: cut_id, name, type,
     *                                            start_seconds, end_seconds, vertical, face_tracking, output_path
     * @param  array<string, mixed>  $transcriptJson
     */
    public function __construct(
        public string $sourcePath,
        public array $transcriptJson,
        public array $cuts,
        public string $callbackUrl,
        public ?string $bucket = null,
        public ?string $callbackToken = null,
        public string $callbackHeader = 'Authorization',
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'source_file' => ['bucket' => $this->bucket, 'path' => $this->sourcePath],
            'transcript_json' => $this->transcriptJson,
            'cuts' => $this->cuts,
            'callback_url' => $this->callbackUrl,
            'callback_token' => $this->callbackToken,
            'callback_header' => $this->callbackHeader,
        ];
    }
}
