<section class="w-full max-w-4xl mx-auto" @if(! $ready) wire:poll.4s="refreshStatus" @endif>
    <flux:heading size="xl" level="1">{{ $video->title ?? 'Processando vídeo...' }}</flux:heading>
    <flux:subheading size="lg" class="mb-2">
        Revise a transcrição. Corrija palavras e depois siga direto para a seleção manual dos cortes.
    </flux:subheading>

    <div class="flex items-center gap-3 mb-4">
        <flux:badge>{{ $video->status?->label ?? '—' }}</flux:badge>
        @if($video->duration_seconds)
            <flux:text class="text-sm text-zinc-500">{{ gmdate('i:s', (int) $video->duration_seconds) }} min</flux:text>
        @endif
    </div>
    <flux:separator variant="subtle" class="mb-6" />

    @if(! $ready)
        <div class="flex flex-col items-center justify-center gap-5 py-12 text-center">
            <flux:heading size="lg">Baixando e transcrevendo o vídeo...</flux:heading>
            @if($jobId)
                <div class="w-full max-w-xl">
                    @include('livewire.videos._progress', ['jobId' => $jobId, 'wsUrl' => $wsUrl])
                </div>
            @else
                <flux:icon.loading class="size-8 text-zinc-400" />
            @endif
            <flux:text class="text-zinc-500">Esta página atualiza sozinha quando a transcrição ficar pronta.</flux:text>
        </div>
    @else
        <form wire:submit="confirm" class="flex flex-col gap-4">
            <flux:textarea
                wire:model="text"
                label="Transcrição"
                rows="16"
                placeholder="A transcrição aparecerá aqui..."
            />
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary" icon="check">
                    <span wire:loading.remove wire:target="confirm">Confirmar e ir para os cortes</span>
                    <span wire:loading wire:target="confirm">Enviando...</span>
                </flux:button>
                <flux:button :href="route('videos.create')" variant="ghost" wire:navigate>Voltar</flux:button>
            </div>
        </form>
    @endif
</section>
