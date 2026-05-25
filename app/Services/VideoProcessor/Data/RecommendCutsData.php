<?php

declare(strict_types=1);

namespace App\Services\VideoProcessor\Data;

final readonly class RecommendCutsData
{
    /**
     * @param  array<string, mixed>  $transcriptJson
     * @param  array<string, mixed>  $video
     * @param  array<string, mixed>  $constraints
     */
    public function __construct(
        public array $transcriptJson,
        public array $video = [],
        public array $constraints = [],
        public ?string $userPrompt = null,
        public ?string $llm = null,
        public ?string $callbackUrl = null,
        public ?string $callbackToken = null,
        public string $callbackHeader = 'Authorization',
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'transcript_json' => $this->transcriptJson,
            'video' => $this->video,
            'constraints' => $this->constraints,
            'user_prompt' => $this->userPrompt,
            'llm' => $this->llm,
            'callback_url' => $this->callbackUrl,
            'callback_token' => $this->callbackToken,
            'callback_header' => $this->callbackHeader,
        ];
    }
}
