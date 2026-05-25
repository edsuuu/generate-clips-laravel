<section
    class="w-full max-w-7xl mx-auto"
    @if($activeJobId) wire:poll.5s="refreshStatus" @endif
    x-data="{
        player: null,
        duration: @js((float) ($video->duration_seconds ?? 0)),
        current: 0,
        start: @js((float) $newStart),
        end: @js((float) $newEnd),
        zoom: 1.8,
        dragging: null,
        viewportWidth: 900,
        resizeHandler: null,
        thumbTrack: [],
        thumbFrameCount: 14,
        loadingThumbs: false,
        init() {
            this.end = this.end > this.start ? this.end : Math.max(this.start + 1, 60);
            this.resizeHandler = () => {
                if (this.$refs.timelineViewport) {
                    this.viewportWidth = this.$refs.timelineViewport.clientWidth;
                    const nextFrameCount = Math.min(22, Math.max(10, Math.ceil(this.viewportWidth / 82)));
                    if (nextFrameCount !== this.thumbFrameCount) {
                        this.thumbFrameCount = nextFrameCount;
                        this.buildThumbTrack();
                    }
                }
            };
            this.$nextTick(() => this.resizeHandler());
            window.addEventListener('resize', this.resizeHandler);
            this.syncWire();
        },
        destroy() {
            if (this.resizeHandler) {
                window.removeEventListener('resize', this.resizeHandler);
            }
        },
        registerPlayer(el) {
            this.player = el;
            const sync = () => {
                if (Number.isFinite(el.duration) && el.duration > 0) {
                    this.duration = el.duration;
                    if (this.end > this.duration) this.end = this.duration;
                    if (!this.thumbTrack.length) {
                        this.buildThumbTrack();
                    }
                }
                this.current = el.currentTime || 0;
            };
            el.addEventListener('loadedmetadata', sync);
            el.addEventListener('timeupdate', sync);
            sync();
        },
        clamp(value, min, max) {
            return Math.min(max, Math.max(min, value));
        },
        round(value) {
            return Math.round(value * 1000) / 1000;
        },
        adjustZoom(event) {
            const direction = event.deltaY < 0 ? 0.2 : -0.2;
            this.zoom = this.round(this.clamp(this.zoom + direction, 0.6, 5));
        },
        nudgePlayhead(delta) {
            this.jumpTo(this.current + delta);
        },
        get pxPerSecond() {
            return 42 * this.zoom;
        },
        get timelineWidth() {
            return Math.max(this.viewportWidth, Math.ceil((this.duration || 1) * this.pxPerSecond));
        },
        timeToPx(time) {
            return this.clamp(time, 0, this.duration || time) * this.pxPerSecond;
        },
        pxToTime(px) {
            return this.clamp(px / this.pxPerSecond, 0, this.duration || 0);
        },
        formatClock(seconds) {
            const safe = Math.max(0, Number(seconds || 0));
            const minutes = Math.floor(safe / 60);
            const secs = Math.floor(safe % 60);
            const millis = Math.floor((safe - Math.floor(safe)) * 1000);
            return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}.${String(millis).padStart(3, '0')}`;
        },
        formatRuler(seconds) {
            const safe = Math.max(0, Number(seconds || 0));
            const minutes = Math.floor(safe / 60);
            const secs = Math.floor(safe % 60);
            return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        },
        timelineStep() {
            if (this.zoom >= 4) return 0.1;
            if (this.zoom >= 3) return 0.25;
            if (this.zoom >= 2) return 0.5;
            if (this.zoom >= 1.2) return 1;
            if (this.zoom >= 0.8) return 2;
            return 5;
        },
        rulerMarks() {
            const step = this.timelineStep();
            const total = Math.max(1, Math.ceil((this.duration || 0) / step));
            const majorEvery = step <= 0.1 ? 5 : step <= 0.25 ? 4 : step <= 0.5 ? 4 : step <= 1 ? 5 : step <= 2 ? 3 : 2;
            return Array.from({ length: total + 1 }, (_, idx) => {
                const time = Math.min((this.duration || 0), idx * step);
                return {
                    time,
                    label: idx % majorEvery === 0,
                    height: idx % majorEvery === 0 ? 28 : 16,
                };
            });
        },
        jumpTo(time) {
            const next = this.round(this.clamp(time, 0, this.duration || 0));
            this.current = next;
            if (this.player) {
                this.player.currentTime = next;
            }
        },
        buildThumbTrack() {
            const total = this.duration || this.player?.duration || 0;
            if (!total) {
                this.thumbTrack = [];
                return;
            }

            const frameCount = Math.min(22, Math.max(10, this.thumbFrameCount));
            this.thumbTrack = Array.from({ length: frameCount }, (_, index) => {
                const ratio = frameCount === 1 ? 0 : index / (frameCount - 1);
                const time = this.round(Math.min(total, ratio * total));
                return {
                    id: `thumb-${index}-${Math.round(time * 1000)}`,
                    left: `${(index / frameCount) * 100}%`,
                    width: `${100 / frameCount}%`,
                    time,
                };
            });
        },
        primeThumb(video, time) {
            const seekToTime = () => {
                try {
                    video.currentTime = Math.max(0, Number(time || 0));
                } catch (error) {
                    console.warn('Nao foi possivel posicionar thumbnail.', error);
                }
            };

            if (video.readyState >= 1) {
                seekToTime();
                return;
            }

            video.addEventListener('loadedmetadata', seekToTime, { once: true });
            video.addEventListener('seeked', () => video.pause(), { once: true });
        },
        setStartFromCurrent() {
            this.start = this.round(this.clamp(this.current, 0, this.end - 0.05));
            this.syncWire();
        },
        setEndFromCurrent() {
            const maxDuration = this.duration || Math.max(this.current, this.end, this.start + 0.05);
            this.end = this.round(this.clamp(this.current, this.start + 0.05, maxDuration));
            this.syncWire();
        },
        syncWire() {
            this.start = this.round(this.start);
            this.end = this.round(this.end);
            this.$wire.set('newStart', this.start, false);
            this.$wire.set('newEnd', this.end, false);
        },
        addCutFromTimeline() {
            this.syncWire();
            this.$wire.addCut();
        },
        seekFromPointer(event) {
            if (!this.$refs.timelineInner || !this.$refs.timelineViewport) return;
            const rect = this.$refs.timelineInner.getBoundingClientRect();
            const px = event.clientX - rect.left + this.$refs.timelineViewport.scrollLeft;
            this.jumpTo(this.pxToTime(px));
        },
        beginDrag(kind, event) {
            event.preventDefault();
            this.dragging = kind;
            const move = (ev) => this.handleDrag(ev);
            const up = () => {
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', up);
                this.dragging = null;
                this.syncWire();
            };
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
        },
        handleDrag(event) {
            if (!this.dragging || !this.$refs.timelineInner || !this.$refs.timelineViewport) return;
            const rect = this.$refs.timelineInner.getBoundingClientRect();
            const px = event.clientX - rect.left + this.$refs.timelineViewport.scrollLeft;
            const time = this.round(this.pxToTime(px));
            const maxDuration = this.duration || Math.max(this.end, time);

            if (this.dragging === 'start') {
                this.start = this.clamp(time, 0, this.end - 0.05);
                this.jumpTo(this.start);
                return;
            }
            if (this.dragging === 'end') {
                this.end = this.clamp(time, this.start + 0.05, maxDuration);
                this.jumpTo(this.end);
                return;
            }
            if (this.dragging === 'playhead') {
                this.jumpTo(time);
            }
        },
        syncStartInput() {
            this.start = this.round(this.clamp(this.start, 0, this.end - 0.05));
            this.syncWire();
        },
        syncEndInput() {
            const maxDuration = this.duration || Math.max(this.end, this.start + 0.05);
            this.end = this.round(this.clamp(this.end, this.start + 0.05, maxDuration));
            this.syncWire();
        },
        loadCut(start, end) {
            const safeStart = this.round(Math.max(0, Number(start || 0)));
            const maxDuration = this.duration || Math.max(Number(end || 0), safeStart + 0.05);
            this.start = safeStart;
            this.end = this.round(this.clamp(Number(end || 0), safeStart + 0.05, maxDuration));
            this.jumpTo(this.start);
            this.syncWire();
        }
    }"
>
    <flux:heading size="xl" level="1">{{ $video->title ?? 'Editor de cortes' }}</flux:heading>
    <flux:subheading size="lg" class="mb-2">Use a timeline para marcar com precisão os pontos do corte, igual um editor leve de vídeo.</flux:subheading>
    <div class="flex items-center gap-3 mb-4">
        <flux:badge>{{ $video->status?->label ?? '—' }}</flux:badge>
    </div>
    <flux:separator variant="subtle" class="mb-6" />

    @if($activeJobId)
        <div class="mb-6" wire:key="render-progress-{{ $activeJobId }}">
            @include('livewire.videos._progress', ['jobId' => $activeJobId, 'wsUrl' => $wsUrl])
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.25fr)_360px] gap-8">
        <div class="space-y-5 min-w-0">
            <div class="rounded-3xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/80 dark:bg-zinc-900/60 p-5 min-w-0 overflow-hidden">
                <div class="mx-auto w-full" style="max-width: 36rem;">
                    @if($playerUrl)
                        <video
                            x-init="registerPlayer($el)"
                            src="{{ $playerUrl }}"
                            controls
                            class="w-full rounded-2xl bg-black aspect-video shadow-sm"
                            style="max-height: 320px;"
                        ></video>
                    @else
                        <div class="w-full rounded-2xl border border-dashed border-zinc-300 dark:border-zinc-700 aspect-video flex items-center justify-center text-zinc-500">
                            Video original ainda nao disponivel.
                        </div>
                    @endif
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <flux:badge size="sm" color="sky">Playhead <span class="ml-1 tabular-nums" x-text="formatClock(current)"></span></flux:badge>
                    <flux:badge size="sm" color="emerald">Início <span class="ml-1 tabular-nums" x-text="formatClock(start)"></span></flux:badge>
                    <flux:badge size="sm" color="amber">Fim <span class="ml-1 tabular-nums" x-text="formatClock(end)"></span></flux:badge>
                    <flux:badge size="sm">Duração <span class="ml-1 tabular-nums" x-text="formatClock(Math.max(0, end - start))"></span></flux:badge>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <flux:button size="sm" variant="ghost" x-on:click="jumpTo(start)">ir para início</flux:button>
                    <flux:button size="sm" variant="ghost" x-on:click="jumpTo(end)">ir para fim</flux:button>
                    <flux:button size="sm" variant="ghost" x-on:click="nudgePlayhead(-0.05)">-50ms</flux:button>
                    <flux:button size="sm" variant="ghost" x-on:click="nudgePlayhead(0.05)">+50ms</flux:button>
                    <flux:button size="sm" variant="ghost" x-on:click="setStartFromCurrent()">marcar início no playhead</flux:button>
                    <flux:button size="sm" variant="ghost" x-on:click="setEndFromCurrent()">marcar fim no playhead</flux:button>
                </div>

                <div class="mt-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-950 text-zinc-100 p-4 min-w-0">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-3">
                        <div>
                            <div class="text-sm font-medium">Timeline do corte</div>
                            <div class="text-xs text-zinc-400">Clique para mover o playhead, arraste as alças para ajustar início e fim, use zoom para refinar.</div>
                        </div>
                        <label class="flex items-center gap-3 text-sm">
                            <span class="text-zinc-400">Zoom</span>
                            <input type="range" min="0.6" max="5" step="0.1" x-model.number="zoom" class="accent-cyan-400 w-40">
                            <span class="tabular-nums text-zinc-300 w-10 text-right" x-text="zoom.toFixed(1) + 'x'"></span>
                        </label>
                    </div>

                    <div
                        x-ref="timelineViewport"
                        class="relative overflow-x-auto rounded-xl border border-zinc-800 bg-zinc-950 pb-2"
                        x-on:wheel.prevent="adjustZoom($event)"
                    >
                        <div
                            x-ref="timelineInner"
                            class="relative"
                            :style="`width:${timelineWidth}px`"
                        >
                            <div class="relative h-10 border-b border-zinc-800 bg-[linear-gradient(180deg,rgba(255,255,255,0.04),rgba(255,255,255,0))]">
                                <template x-for="mark in rulerMarks()" :key="`mark-${mark.time}`">
                                    <div
                                        class="absolute top-0"
                                        :style="`left:${timeToPx(mark.time)}px`"
                                    >
                                        <div class="w-px bg-zinc-500/80" :style="`height:${mark.height}px`"></div>
                                        <div
                                            x-show="mark.label"
                                            class="absolute top-0 left-2 text-[10px] tracking-wide text-zinc-400 tabular-nums"
                                            x-text="formatRuler(mark.time)"
                                        ></div>
                                    </div>
                                </template>
                            </div>

                            <div
                                class="relative h-28 cursor-pointer overflow-hidden rounded-b-xl bg-zinc-900"
                                style="height: 7rem;"
                                x-on:click="seekFromPointer($event)"
                            >
                                <template x-if="thumbTrack.length">
                                    <div class="absolute inset-0 flex">
                                        <template x-for="thumb in thumbTrack" :key="thumb.id">
                                            <div
                                                class="absolute inset-y-0 border-r border-black/30"
                                                :style="`left:${thumb.left};width:${thumb.width}`"
                                            >
                                                <video
                                                    :src="player ? (player.currentSrc || player.src) : ''"
                                                    muted
                                                    playsinline
                                                    preload="metadata"
                                                    class="h-full w-full object-cover opacity-80 select-none pointer-events-none"
                                                    x-init="primeThumb($el, thumb.time)"
                                                ></video>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <div
                                    x-show="!thumbTrack.length"
                                    class="absolute inset-0 bg-[linear-gradient(135deg,rgba(34,211,238,0.18),rgba(34,211,238,0.05)),repeating-linear-gradient(90deg,rgba(255,255,255,0.03)_0px,rgba(255,255,255,0.03)_20px,transparent_20px,transparent_40px)]"
                                ></div>

                                <div class="absolute inset-y-0 left-0 bg-black/45"
                                     :style="`width:${timeToPx(start)}px`"></div>
                                <div class="absolute inset-y-0 bg-black/45"
                                     :style="`left:${timeToPx(end)}px;width:${Math.max(0, timelineWidth - timeToPx(end))}px`"></div>

                                <div class="absolute inset-y-5 rounded-xl border border-cyan-300/70 bg-cyan-400/15"
                                     :style="`left:${timeToPx(start)}px;width:${Math.max(8, timeToPx(end) - timeToPx(start))}px`"></div>

                                <div class="absolute inset-y-3 w-[3px] bg-white shadow-[0_0_0_1px_rgba(255,255,255,0.35)]"
                                     :style="`left:${timeToPx(current)}px`"
                                     x-on:pointerdown.stop="beginDrag('playhead', $event)">
                                    <div class="absolute -top-2 left-1/2 -translate-x-1/2 h-5 w-5 rounded-full border-2 border-white bg-cyan-400 shadow-lg"></div>
                                </div>

                                <button
                                    type="button"
                                    class="absolute inset-y-4 w-4 -translate-x-1/2 rounded-full border border-emerald-300 bg-emerald-400/90 shadow-lg"
                                    :style="`left:${timeToPx(start)}px`"
                                    x-on:pointerdown.stop="beginDrag('start', $event)"
                                    aria-label="Arrastar início"
                                ></button>

                                <button
                                    type="button"
                                    class="absolute inset-y-4 w-4 -translate-x-1/2 rounded-full border border-amber-300 bg-amber-400/90 shadow-lg"
                                    :style="`left:${timeToPx(end)}px`"
                                    x-on:pointerdown.stop="beginDrag('end', $event)"
                                    aria-label="Arrastar fim"
                                ></button>

                                <div class="absolute left-4 top-4 rounded-full bg-black/50 px-3 py-1 text-[11px] text-zinc-200">
                                    timeline com preview
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-950/50 p-3">
                        <div class="text-xs uppercase tracking-wide text-zinc-500 mb-1">Início</div>
                        <input type="number" step="0.001" x-model.number="start" x-on:change="syncStartInput()" class="w-full bg-transparent text-sm font-medium outline-none">
                    </label>
                    <label class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-950/50 p-3">
                        <div class="text-xs uppercase tracking-wide text-zinc-500 mb-1">Fim</div>
                        <input type="number" step="0.001" x-model.number="end" x-on:change="syncEndInput()" class="w-full bg-transparent text-sm font-medium outline-none">
                    </label>
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-950/50 p-3">
                        <div class="text-xs uppercase tracking-wide text-zinc-500 mb-1">Duração</div>
                        <div class="text-sm font-medium tabular-nums" x-text="formatClock(Math.max(0, end - start))"></div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <flux:button variant="primary" size="sm" icon="plus" x-on:click="addCutFromTimeline()">Adicionar corte</flux:button>
                </div>
            </div>

            <div class="rounded-3xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-950/50 p-5">
                <flux:heading size="sm">Sugestão opcional da IA</flux:heading>
                <flux:text class="text-sm text-zinc-500 mt-1">
                    Se quiser acelerar, a IA ainda pode sugerir tempos iniciais. Depois voce ajusta tudo na timeline.
                </flux:text>
                <div class="mt-3 flex flex-col gap-3">
                    <flux:input wire:model="userPrompt" placeholder="Ex: foque nos melhores ganchos (opcional)" />
                    <div>
                        <flux:button wire:click="recommend" variant="filled" size="sm" icon="sparkles">
                            <span wire:loading.remove wire:target="recommend">Sugerir cortes com IA</span>
                            <span wire:loading wire:target="recommend">Pensando...</span>
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Cortes ({{ $cuts->count() }})</flux:heading>
                @if($cuts->isNotEmpty())
                    <flux:button wire:click="renderCuts" variant="primary" size="sm" icon="film">
                        <span wire:loading.remove wire:target="renderCuts">Renderizar todos</span>
                        <span wire:loading wire:target="renderCuts">Enviando...</span>
                    </flux:button>
                @endif
            </div>

            @if($cuts->isEmpty())
                <flux:text class="text-zinc-500">Nenhum corte ainda. Marque o range na timeline e adicione o corte.</flux:text>
            @endif

            <div class="flex flex-col gap-3">
                @foreach($cuts as $cut)
                    @php($rendered = $cut->files->firstWhere('type', $cut->type))
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-950/50 p-4" wire:key="cut-{{ $cut->uuid }}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <flux:badge color="{{ $cut->source === 'ai' ? 'purple' : 'zinc' }}" size="sm">{{ $cut->name }}</flux:badge>
                                @if($cut->source === 'ai')<flux:badge size="sm" color="purple">IA</flux:badge>@endif
                                @if($cut->rendered_at)<flux:badge size="sm" color="green">renderizado</flux:badge>@endif
                            </div>
                            <div class="flex items-center gap-1">
                                <flux:button size="xs" variant="ghost"
                                    x-on:click="loadCut({{ $cut->start_seconds }}, {{ $cut->end_seconds }})">
                                    editar na timeline
                                </flux:button>
                                <flux:button wire:click="deleteCut('{{ $cut->uuid }}')" size="xs" variant="ghost" icon="trash" />
                            </div>
                        </div>

                        @if($cut->reason)
                            <flux:text class="text-sm text-zinc-500 mb-2">{{ $cut->reason }}</flux:text>
                        @endif

                        <div class="grid grid-cols-[1fr_1fr_auto] gap-2 items-end"
                             x-data="{ s: {{ $cut->start_seconds }}, e: {{ $cut->end_seconds }} }">
                            <flux:input type="number" step="0.001" x-model="s" label="Início" size="sm" />
                            <flux:input type="number" step="0.001" x-model="e" label="Fim" size="sm" />
                            <flux:button size="sm" variant="ghost"
                                x-on:click="$wire.updateCut('{{ $cut->uuid }}', parseFloat(s), parseFloat(e))">
                                salvar
                            </flux:button>
                        </div>

                        <flux:text class="mt-2 text-xs text-zinc-400 tabular-nums">
                            {{ number_format($cut->duration_seconds, 3) }}s
                        </flux:text>

                        @if($rendered)
                            <video src="{{ $rendered->temporaryUrl(120) }}" controls
                                   class="w-full mt-3 rounded-xl bg-black aspect-[9/16] max-h-80 mx-auto"></video>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
