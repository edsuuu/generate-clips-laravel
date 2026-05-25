<?php

declare(strict_types=1);

namespace App\Livewire\Videos;

use App\Models\Cut;
use App\Models\ProcessingJob;
use App\Models\Status;
use App\Models\Video;
use App\Services\VideoProcessor\VideoProcessorService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

final class Editor extends Component
{
    public Video $video;

    public float $newStart = 0.0;

    public float $newEnd = 60.0;

    public string $userPrompt = '';

    public function mount(Video $video): void
    {
        $this->video = $video;
    }

    public function refreshStatus(): void
    {
        $this->video->refresh();
    }

    public function addCut(): void
    {
        $this->validate([
            'newStart' => 'required|numeric|min:0',
            'newEnd' => 'required|numeric|gt:newStart',
        ]);

        $maxIndex = $this->video->cuts()->max('index');
        $index = (is_numeric($maxIndex) ? (int) $maxIndex : 0) + 1;

        $this->video->cuts()->create([
            'index' => $index,
            'name' => 'PT'.$index,
            'type' => 'pt'.$index,
            'source' => 'manual',
            'start_seconds' => $this->newStart,
            'end_seconds' => $this->newEnd,
            'duration_seconds' => $this->newEnd - $this->newStart,
            'status_id' => Status::idFor('pending'),
        ]);

        Flux::toast('Corte adicionado.');
    }

    public function updateCut(string $uuid, float $start, float $end): void
    {
        $cut = $this->video->cuts()->where('uuid', $uuid)->firstOrFail();
        $cut->update([
            'start_seconds' => $start,
            'end_seconds' => $end,
            'duration_seconds' => max(0, $end - $start),
        ]);
    }

    public function deleteCut(string $uuid): void
    {
        $this->video->cuts()->where('uuid', $uuid)->delete();
    }

    public function recommend(VideoProcessorService $videoProcessor): void
    {
        $cuts = $videoProcessor->recommendCuts($this->video, $this->userPrompt ?: null);

        foreach ($cuts as $item) {
            $maxIndexVal = $this->video->cuts()->max('index');
            $maxIndex = is_numeric($maxIndexVal) ? (int) $maxIndexVal : 0;
            $index = isset($item['index']) && is_numeric($item['index']) ? (int) $item['index'] : $maxIndex + 1;
            $this->video->cuts()->updateOrCreate(
                ['video_id' => $this->video->id, 'index' => $index],
                [
                    'name' => $item['name'] ?? 'PT'.$index,
                    'type' => $item['type'] ?? 'pt'.$index,
                    'source' => 'ai',
                    'start_seconds' => $item['start_seconds'],
                    'end_seconds' => $item['end_seconds'],
                    'duration_seconds' => $item['duration_seconds'],
                    'score' => $item['score'] ?? null,
                    'reason' => $item['reason'] ?? null,
                    'status_id' => Status::idFor('pending'),
                ],
            );
        }

        Flux::toast(count($cuts).' cortes recomendados pela IA.');
    }

    public function renderCuts(VideoProcessorService $videoProcessor): void
    {
        /** @var Collection<int, Cut> $cuts */
        $cuts = $this->video->cuts()->get();
        abort_if($cuts->isEmpty(), 422, 'Nenhum corte para renderizar.');

        $videoProcessor->startRenderCuts($this->video, $cuts);
        Flux::toast('Renderização iniciada. Os arquivos aparecem ao concluir.');
    }

    public function render(): View
    {
        $this->video->refresh()->load(['cuts.files', 'files']);

        $legendado = $this->video->fileOfType('legendado');
        $original = $this->video->fileOfType('original');
        $playable = $legendado ?? $original;

        // Job em andamento para mostrar progresso.
        $activeJob = $this->video->processingJobs()
            ->whereHas('status', fn ($q) => $q->whereNotIn('key', ['completed', 'failed']))
            ->latest()
            ->first();

        $status = $this->video->status;

        return view('livewire.videos.editor', [
            'cuts' => $this->video->cuts,
            'playerUrl' => $playable?->temporaryUrl(120),
            'statusKey' => $status instanceof Status ? $status->key : null,
            'activeJobId' => $activeJob instanceof ProcessingJob ? $activeJob->external_job_id : null,
            'wsUrl' => config('video-processor.ws_url'),
        ]);
    }
}
