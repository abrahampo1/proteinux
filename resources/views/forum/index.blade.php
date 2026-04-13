@extends('layouts.app')

@section('title', 'Foro')

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 sm:py-10 lg:px-8">

    {{-- Navegación contextual --}}
    <div class="mb-6 flex flex-wrap items-center gap-2 font-mono text-[11px] uppercase tracking-[0.14em] text-ink-500 sm:mb-8">
        <a href="{{ route('home') }}" class="transition-colors hover:text-signal-mint">inicio</a>
        <span class="text-ink-400">/</span>
        @if(!empty($proteinRef))
            <a href="{{ route('proteins.show', $proteinRef) }}" class="transition-colors hover:text-signal-mint">catálogo</a>
            <span class="text-ink-400">/</span>
            <span class="text-ink-700">hilos · {{ $proteinRef }}</span>
        @elseif(!empty($jobFilter))
            <a href="{{ route('jobs.show', $jobFilter) }}" class="transition-colors hover:text-signal-mint">trabajo · {{ Str::limit($jobFilter, 12) }}</a>
            <span class="text-ink-400">/</span>
            <span class="text-ink-700">hilos</span>
        @else
            <span class="text-ink-700">foro</span>
        @endif
    </div>

    {{-- Cabecera --}}
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4 border-b border-ink-300 pb-5 sm:mb-8 sm:pb-6">
        <div>
            <div class="label-tag">sección 08 · foro</div>
            <h1 class="mt-3 font-serif text-2xl leading-tight text-ink-900 sm:text-3xl lg:text-4xl">
                @if(!empty($proteinRef))
                    Discusiones · {{ $proteinRef }}
                @elseif(!empty($jobFilter))
                    Discusiones · trabajo
                @else
                    / foro
                @endif
            </h1>
            <p class="mt-2 max-w-2xl font-serif text-sm leading-relaxed text-ink-600 sm:text-base">
                @if(!empty($proteinRef))
                    Hilos de discusión vinculados a esta proteína del catálogo.
                @elseif(!empty($jobFilter))
                    Hilos de discusión vinculados a esta predicción.
                @else
                    Discusiones sobre proteínas, predicciones y biología estructural.
                    Los hilos pueden vincularse a predicciones concretas y recibir aportes
                    de instancias federadas.
                @endif
            </p>
        </div>
        <div class="flex shrink-0 flex-col items-end gap-1 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">
            <span><span class="text-ink-900 tabular-nums">{{ $threads->total() }}</span> hilos</span>
        </div>
    </header>

    {{-- Buscador y acciones --}}
    <div class="mb-6 flex flex-wrap items-center gap-2 sm:mb-8">
        <form action="{{ route('forum.index') }}" method="GET" class="flex flex-1 items-center gap-2">
            @if(!empty($proteinRef))
                <input type="hidden" name="protein_ref" value="{{ $proteinRef }}">
            @endif
            @if(!empty($jobFilter))
                <input type="hidden" name="job" value="{{ $jobFilter }}">
            @endif
            <input type="search" name="q" value="{{ $search }}"
                   placeholder="buscar por título"
                   class="input-lab min-w-[200px] flex-1">
            <button type="submit" class="btn-primary">buscar</button>
            @if($search !== '')
                <a href="{{ route('forum.index', array_filter(['protein_ref' => $proteinRef ?? null, 'job' => $jobFilter ?? null])) }}" class="btn-secondary">limpiar</a>
            @endif
        </form>
        @auth
            <a href="{{ route('forum.create', array_filter(['protein_ref' => $proteinRef ?? null, 'predicted_job_id' => $jobFilter ? optional(\App\Models\PredictedJob::where('job_id', $jobFilter)->first())->id : null])) }}"
               class="btn-primary">+ nuevo hilo</a>
        @endauth
    </div>

    {{-- Filtro activo --}}
    @if(!empty($proteinRef) || !empty($jobFilter))
        <div class="mb-6 flex items-center gap-3 border border-signal-mint/30 bg-signal-mint/5 px-4 py-3">
            <span class="font-mono text-[10px] uppercase tracking-wider text-signal-mint-deep">
                @if(!empty($proteinRef))
                    filtrando por proteína: {{ $proteinRef }}
                @else
                    filtrando por predicción: {{ Str::limit($jobFilter, 20) }}
                @endif
            </span>
            <a href="{{ route('forum.index') }}" class="ml-auto font-mono text-[10px] uppercase tracking-wider text-ink-500 transition-colors hover:text-signal-rust">
                × quitar filtro
            </a>
        </div>
    @endif

    {{-- Lista de hilos --}}
    @if($threads->total() === 0)
        <div class="border border-dashed border-ink-300 bg-ink-100/50 px-6 py-16 text-center">
            <div class="label-tag mb-3 justify-center">sin hilos</div>
            <p class="font-serif text-sm text-ink-600 sm:text-base">
                @if($search !== '')
                    Ningún hilo coincide con
                    <span class="font-mono text-signal-mint-deep">"{{ $search }}"</span>.
                @elseif(!empty($proteinRef))
                    Aún no hay discusiones sobre esta proteína.
                    @auth
                        <a href="{{ route('forum.create', ['protein_ref' => $proteinRef]) }}" class="font-mono text-signal-mint hover:underline">crear el primero →</a>
                    @endauth
                @elseif(!empty($jobFilter))
                    Aún no hay discusiones sobre esta predicción.
                    @auth
                        <a href="{{ route('forum.create', ['predicted_job_id' => optional(\App\Models\PredictedJob::where('job_id', $jobFilter)->first())->id]) }}" class="font-mono text-signal-mint hover:underline">crear el primero →</a>
                    @endauth
                @else
                    Aún no se ha creado ningún hilo de discusión.
                    @auth
                        <a href="{{ route('forum.create') }}" class="font-mono text-signal-mint hover:underline">crear el primero →</a>
                    @endauth
                @endif
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($threads as $thread)
                <a href="{{ route('forum.show', $thread) }}"
                   class="group block border border-ink-300 bg-ink-100/60 p-4 transition-all hover:border-signal-mint/60 hover:bg-ink-100 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                @if($thread->is_pinned)
                                    <span class="inline-flex items-center gap-1 border border-signal-amber/40 bg-signal-amber/5 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-signal-amber">
                                        fijado
                                    </span>
                                @endif
                                @if($thread->is_locked)
                                    <span class="inline-flex items-center gap-1 border border-ink-400/60 bg-ink-200/50 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-ink-600">
                                        cerrado
                                    </span>
                                @endif
                                @if($thread->protein_reference)
                                    <span class="inline-flex items-center gap-1 border border-signal-violet/40 bg-signal-violet/5 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-signal-violet">
                                        catálogo
                                    </span>
                                @endif
                                <x-origin-badge :domain="$thread->origin_domain" />
                            </div>

                            <h3 class="font-serif text-base leading-tight text-ink-900 group-hover:text-signal-mint-deep sm:text-lg">
                                {{ $thread->title }}
                            </h3>

                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                                <x-federated-author :author="$thread->author()" :showDomain="true" />
                                <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">
                                    {{ $thread->created_at->format('Y-m-d') }}
                                </span>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span class="font-mono text-[11px] tabular-nums text-ink-700">
                                {{ $thread->posts_count }} <span class="text-ink-400">respuestas</span>
                            </span>
                            @if($thread->last_activity_at)
                                <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">
                                    últ. {{ $thread->last_activity_at->diffForHumans() }}
                                </span>
                            @endif
                            @if($thread->predictedJob)
                                <span class="mt-1 inline-flex items-center gap-1 border border-signal-mint/40 bg-signal-mint/5 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-signal-mint-deep">
                                    <span class="status-dot bg-signal-mint"></span>
                                    {{ $thread->predictedJob->displayName() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if($threads->hasPages())
            <div class="mt-8 border-t border-ink-300 pt-4">
                {{ $threads->links() }}
            </div>
        @endif
    @endif

    <p class="mt-10 border-t border-ink-300 pt-4 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">
        · foro federado — los hilos pueden recibir aportes de instancias conectadas
    </p>
</div>
@endsection
