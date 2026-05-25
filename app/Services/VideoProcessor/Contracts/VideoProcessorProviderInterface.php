<?php

declare(strict_types=1);

namespace App\Services\VideoProcessor\Contracts;

use App\Services\VideoProcessor\Data\IngestVideoData;
use App\Services\VideoProcessor\Data\RecommendCutsData;
use App\Services\VideoProcessor\Data\RenderCutsData;
use App\Services\VideoProcessor\Data\SubtitleFullData;

/**
 * Contrato das chamadas HTTP para a API de processamento de vídeo.
 */
interface VideoProcessorProviderInterface
{
    /** @return array<string, mixed> Resposta 202 com job_id. */
    public function ingest(IngestVideoData $data): array;

    /** @return array<string, mixed> Resposta 202 com job_id. */
    public function subtitleFull(string $videoUuid, SubtitleFullData $data): array;

    /** @return array<string, mixed> Resposta síncrona com a lista de cortes. */
    public function recommendCuts(string $videoUuid, RecommendCutsData $data): array;

    /** @return array<string, mixed> Resposta 202 com job_id. */
    public function renderCuts(string $videoUuid, RenderCutsData $data): array;
}
