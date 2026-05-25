<section class="w-full max-w-3xl mx-auto">
    <flux:heading size="xl" level="1">Gerar cortes a partir de um vídeo</flux:heading>
    <flux:subheading size="lg" class="mb-6">Cole a URL do YouTube. Baixamos, transcrevemos e você revisa antes de cortar.</flux:subheading>
    <flux:separator variant="subtle" class="mb-6" />

    <form wire:submit="start" class="flex flex-col gap-4">
        <flux:input
            wire:model="url"
            label="URL do vídeo"
            type="url"
            placeholder="https://www.youtube.com/watch?v=..."
            required
        />
        <div>
            <flux:button type="submit" variant="primary" icon="arrow-down-tray">
                <span wire:loading.remove wire:target="start">Iniciar processamento</span>
                <span wire:loading wire:target="start">Enviando...</span>
            </flux:button>
        </div>
    </form>

    @if($recent->isNotEmpty())
        <flux:separator variant="subtle" class="my-8" />
        <flux:heading size="lg" class="mb-3">Vídeos recentes</flux:heading>
        <div class="flex flex-col gap-2">
            @foreach($recent as $video)
                <a href="{{ route('videos.transcript', $video) }}" wire:navigate
                   class="flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-900">
                    <div class="min-w-0">
                        <div class="truncate font-medium">{{ $video->title ?? $video->url }}</div>
                        <div class="text-sm text-zinc-500">{{ $video->uuid }}</div>
                    </div>
                    <flux:badge size="sm">{{ $video->status?->label ?? '—' }}</flux:badge>
                </a>
            @endforeach
        </div>
    @endif
</section>
