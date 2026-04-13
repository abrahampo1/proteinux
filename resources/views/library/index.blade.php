@extends('layouts.app')

@section('title', 'Biblioteca de predicciones')

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 sm:py-10 lg:px-8">

    {{-- Cabecera --}}
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4 border-b border-ink-300 pb-5 sm:mb-8 sm:pb-6">
        <div>
            <div class="label-tag">sección 06 · biblioteca</div>
            <h1 class="mt-3 font-serif text-2xl leading-tight text-ink-900 sm:text-3xl lg:text-4xl">
                Biblioteca de predicciones
            </h1>
            <p class="mt-2 max-w-2xl font-serif text-sm leading-relaxed text-ink-600 sm:text-base">
                Historial compartido de predicciones ya ejecutadas. Al enviar una
                secuencia ya conocida, saltará directamente al resultado para
                evitar duplicar cómputo. Puedes forzar una nueva ejecución con
                <span class="font-mono text-signal-mint-deep">«ejecutar de nuevo»</span>.
            </p>
        </div>
        <div class="flex shrink-0 flex-col items-end gap-1 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">
            <span><span class="text-ink-900 tabular-nums">{{ $total }}</span> entradas</span>
            <span><span class="text-signal-mint-deep tabular-nums">{{ $completed }}</span> completadas</span>
        </div>
    </header>

    {{-- Filtro de origen --}}
    <div class="mb-4 flex items-center gap-1 font-mono text-[11px] uppercase tracking-[0.14em]">
        <a href="{{ route('library.index', array_merge(request()->only('q'), ['source' => 'all'])) }}"
           class="px-3 py-1.5 border transition-colors {{ ($source ?? 'all') === 'all' ? 'border-signal-mint bg-signal-mint/10 text-signal-mint-deep' : 'border-ink-300 text-ink-600 hover:text-ink-900' }}">
            todos
        </a>
        <a href="{{ route('library.index', array_merge(request()->only('q'), ['source' => 'local'])) }}"
           class="px-3 py-1.5 border transition-colors {{ ($source ?? 'all') === 'local' ? 'border-signal-mint bg-signal-mint/10 text-signal-mint-deep' : 'border-ink-300 text-ink-600 hover:text-ink-900' }}">
            local
        </a>
        <a href="{{ route('library.index', array_merge(request()->only('q'), ['source' => 'federated'])) }}"
           class="px-3 py-1.5 border transition-colors {{ ($source ?? 'all') === 'federated' ? 'border-signal-mint bg-signal-mint/10 text-signal-mint-deep' : 'border-ink-300 text-ink-600 hover:text-ink-900' }}">
            federado
        </a>
    </div>

    {{-- Buscador --}}
    <form action="{{ route('library.index') }}" method="GET" class="mb-6 flex flex-wrap items-center gap-2 sm:mb-8">
        <input type="hidden" name="source" value="{{ $source ?? 'all' }}">
        <input type="search" name="q" value="{{ $search }}"
               placeholder="buscar por nombre, organismo, UniProt o PDB"
               class="input-lab flex-1 min-w-[260px]">
        <button type="submit" class="btn-primary">buscar</button>
        @if($search !== '')
            <a href="{{ route('library.index', ['source' => $source ?? 'all']) }}" class="btn-secondary">limpiar</a>
        @endif
    </form>

    {{-- Grid --}}
    @if($jobs->total() === 0)
        <div class="border border-dashed border-ink-300 bg-ink-100/50 px-6 py-16 text-center">
            <div class="label-tag mb-3 justify-center">biblioteca vacía</div>
            <p class="font-serif text-sm text-ink-600 sm:text-base">
                @if($search !== '')
                    Ninguna predicción coincide con
                    <span class="font-mono text-signal-mint-deep">"{{ $search }}"</span>.
                @else
                    Aún no se ha ejecutado ninguna predicción.
                    <a href="{{ route('jobs.create') }}" class="font-mono text-signal-mint hover:underline">enviar la primera →</a>
                @endif
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($jobs as $entry)
                @php
                    $score = $entry->plddt_mean;
                    if ($score === null) {
                        $badgeCls = 'border-ink-300 bg-ink-100/60 text-ink-500';
                        $badgeLabel = 'en proceso';
                    } elseif ($score >= 90) {
                        $badgeCls = 'border-[#0053D6]/60 bg-[#0053D6]/10 text-[#0053D6]';
                        $badgeLabel = 'muy alta';
                    } elseif ($score >= 70) {
                        $badgeCls = 'border-sky-500/60 bg-sky-50 text-sky-700';
                        $badgeLabel = 'alta';
                    } elseif ($score >= 50) {
                        $badgeCls = 'border-amber-500/60 bg-amber-50 text-amber-700';
                        $badgeLabel = 'media';
                    } else {
                        $badgeCls = 'border-orange-500/60 bg-orange-50 text-orange-700';
                        $badgeLabel = 'baja';
                    }
                @endphp
                <article class="panel flex flex-col p-4 sm:p-5">
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="label-tag">§ {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            @if($entry->isRemote())
                                <x-origin-badge :domain="$entry->origin_domain" />
                            @endif
                        </div>
                        <span class="inline-flex items-center gap-1.5 border px-2 py-1 font-mono text-[10px] uppercase tracking-wider {{ $badgeCls }}">
                            <span class="status-dot" style="background:currentColor"></span>
                            @if($score !== null)
                                {{ number_format($score, 1) }} · {{ $badgeLabel }}
                            @else
                                {{ $badgeLabel }}
                            @endif
                        </span>
                    </div>

                    <h3 class="font-serif text-lg leading-tight text-ink-900 sm:text-xl">
                        {{ $entry->displayName() }}
                    </h3>
                    @if($entry->organism)
                        <p class="mt-1 font-serif text-[13px] italic text-ink-600">{{ $entry->organism }}</p>
                    @endif

                    <dl class="mt-4 space-y-0 font-mono text-[11px] text-ink-700">
                        @if($entry->uniprot_id)
                            <div class="field">
                                <dt>UniProt</dt>
                                <dd class="tabular-nums">{{ $entry->uniprot_id }}</dd>
                            </div>
                        @endif
                        @if($entry->pdb_id)
                            <div class="field">
                                <dt>PDB</dt>
                                <dd class="tabular-nums">{{ $entry->pdb_id }}</dd>
                            </div>
                        @endif
                        @if($entry->sequence_length)
                            <div class="field">
                                <dt>residuos</dt>
                                <dd class="tabular-nums">{{ $entry->sequence_length }}</dd>
                            </div>
                        @endif
                        <div class="field">
                            <dt>completado</dt>
                            <dd class="tabular-nums">
                                {{ $entry->completed_at ? $entry->completed_at->format('Y-m-d') : '—' }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-auto flex flex-wrap gap-2 pt-4">
                        @if(! $entry->isRemote())
                            <a href="{{ route('jobs.show', $entry->job_id) }}"
                               class="btn-primary flex-1 justify-center">
                                ver resultado
                            </a>
                            <form action="{{ route('library.rerun', $entry) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-secondary"
                                        onclick="return confirm('¿Relanzar esta predicción? Consumirá GPU nueva en el CESGA.')">
                                    ↻ de nuevo
                                </button>
                            </form>
                            @auth
                                @if(! $entry->isShared())
                                    <form action="{{ route('library.share', $entry) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-secondary" title="Compartir con instancias federadas">
                                            compartir
                                        </button>
                                    </form>
                                @else
                                    <span class="btn-secondary opacity-50 cursor-default">compartida</span>
                                @endif
                            @endauth
                        @else
                            <span class="btn-secondary flex-1 justify-center text-center opacity-60 cursor-default">
                                remoto · {{ $entry->origin_domain }}
                            </span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Paginación --}}
        @if($jobs->hasPages())
            <div class="mt-8 border-t border-ink-300 pt-4">
                {{ $jobs->links() }}
            </div>
        @endif
    @endif

    <p class="mt-10 border-t border-ink-300 pt-4 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">
        · biblioteca compartida — cualquier predicción lanzada queda disponible para toda la instancia
    </p>
</div>
@endsection
