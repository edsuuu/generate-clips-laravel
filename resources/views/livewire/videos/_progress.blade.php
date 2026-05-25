{{--
  Barra de progresso em tempo real consumindo o WebSocket da API Python.
  Espera: $jobId (external_job_id) e $wsUrl (ws://host:porta).
  Ao receber stage=done/error, recarrega o componente via $wire.refreshStatus().
--}}
@props(['jobId', 'wsUrl'])

<div
    wire:ignore
    x-data="{
        percent: 0,
        message: 'Conectando ao processamento...',
        stage: '',
        ws: null,
        init() {
            const url = @js($wsUrl) + '/jobs/' + @js($jobId) + '/ws';
            try {
                this.ws = new WebSocket(url);
            } catch (e) {
                this.message = 'Não foi possível conectar ao progresso';
                return;
            }
            this.ws.onmessage = (event) => {
                let d;
                try { d = JSON.parse(event.data); } catch (e) { return; }
                if (d.type === 'ping') return;
                if (typeof d.percent !== 'undefined' && d.percent !== null) this.percent = d.percent;
                if (d.message) this.message = d.message;
                if (d.stage) this.stage = d.stage;
                if (d.stage === 'done' || d.stage === 'error') {
                    if (this.ws) this.ws.close();
                    setTimeout(() => $wire.refreshStatus(), 800);
                }
            };
            this.ws.onerror = () => { this.message = 'Aguardando processamento...'; };
        },
        destroy() { if (this.ws) this.ws.close(); }
    }"
    class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 p-5"
>
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium" x-text="message"></span>
        <span class="text-sm font-semibold tabular-nums" x-text="Math.round(percent) + '%'"></span>
    </div>
    <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-3 overflow-hidden">
        <div class="bg-indigo-500 h-3 rounded-full transition-all duration-300"
             :style="`width: ${percent}%`"></div>
    </div>
    <div class="mt-2 text-xs text-zinc-400" x-show="stage" x-text="'Etapa: ' + stage"></div>
</div>
