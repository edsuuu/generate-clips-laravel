<?php

declare(strict_types=1);

namespace App\Livewire\Videos;

use App\Models\ProcessingJob;
use App\Models\Status;
use App\Models\Transcript as TranscriptModel;
use App\Models\Video;
use App\Services\Status\StatusService;
use Illuminate\View\View;
use Livewire\Component;

final class Transcript extends Component
{
    public Video $video;

    public string $text = '';

    public bool $loaded = false;

    public function mount(Video $video): void
    {
        $this->video = $video;
        $this->loadTranscriptText();
    }

    /** Polling enquanto a transcrição não chega do Python. */
    public function refreshStatus(): void
    {
        $this->video->refresh();
        if (! $this->loaded) {
            $this->loadTranscriptText();
        }
    }

    public function confirm(StatusService $status): void
    {
        $transcript = $this->video->transcript;
        abort_if($transcript === null, 422, 'Transcrição ainda não disponível.');

        $transcript->update([
            'edited_text' => $this->text,
            'active_text_source' => 'edited',
            'is_confirmed_by_user' => true,
            'confirmed_at' => now(),
        ]);

        $status->transition($this->video, 'transcript_confirmed', 'Transcrição confirmada pelo usuário');
        $status->transition($this->video, 'waiting_cuts', 'Transcrição pronta para criação manual de cortes');

        $this->redirectRoute('videos.editor', ['video' => $this->video->uuid], navigate: true);
    }

    public function render(): View
    {
        $this->video->refresh();

        $job = $this->video->processingJobs()
            ->where('type', 'ingest')->latest()->first();

        $status = $this->video->status;

        return view('livewire.videos.transcript', [
            'statusKey' => $status instanceof Status ? $status->key : null,
            'ready' => $this->video->transcript !== null,
            'jobId' => $job instanceof ProcessingJob ? $job->external_job_id : null,
            'wsUrl' => config('video-processor.ws_url'),
        ]);
    }

    private function loadTranscriptText(): void
    {
        $transcript = $this->video->transcript;
        if ($transcript instanceof TranscriptModel) {
            $this->text = $transcript->activeText() ?? '';
            $this->loaded = true;
        }
    }
}
