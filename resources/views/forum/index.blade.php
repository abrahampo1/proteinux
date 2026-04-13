@extends('layouts.app')

@section('title', 'Foro')

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 sm:py-10 lg:px-8">

    {{-- Cabecera --}}
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4 border-b border-ink-300 pb-5 sm:mb-8 sm:pb-6">
        <div>
            <div class="label-tag">seccio&#769;n 08 · foro</div>
            <h1 class="mt-3 font-serif text-2xl leading-tight text-ink-900 sm:text-3xl lg:text-4xl">
                / foro
            </h1>
            <p class="mt-2 max-w-2xl font-serif text-sm leading-relaxed text-ink-600 sm:text-base">
                Discusiones sobre prote&#237;nas, predicciones y biolog&#237;a estructural.
                Los hilos pueden vincularse a predicciones concretas y recibir aportes
                de instancias federadas.
            </p>
        </div>
        <div class="flex shrink-0 flex-col items-end gap-1 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">
            <span><span class="text-ink-900 tabular-nums">{{ $threads->total() }}</span> hilos</span>
        </div>
    </header>

    {{-- Buscador y acciones --}}
    <div class="mb-6 flex flex-wrap items-center gap-2 sm:mb-8">
        <form action="{{ route('forum.index') }}" method="GET" class="flex flex-1 items-center gap-2">
            <input type="search" name="q" value="{{ $search }}"
                   placeholder="buscar por t&#237;tulo"
                   class="input-lab min-w-[200px] flex-1">
            <button type="submit" class="btn-primary">buscar</button>
            @if($search !== '')
                <a href="{{ route('forum.index') }}" class="btn-secondary">limpiar</a>
            @endif
        </form>
        @auth
            <a href="{{ route('forum.create') }}" class="btn-primary">+ nuevo hilo</a>
        @endauth
    </div>

    {{-- Lista de hilos --}}
    @if($threads->total() === 0)
        <div class="border border-dashed border-ink-300 bg-ink-100/50 px-6 py-16 text-center">
            <div class="label-tag mb-3 justify-center">foro vac&#237;o</div>
            <p class="font-serif text-sm text-ink-600 sm:text-base">
                @if($search !== '')
                    Ning&#250;n hilo coincide con
                    <span class="font-mono text-signal-mint-deep">"{{ $search }}"</span>.
                @else
                    A&#250;n no se ha creado ning&#250;n hilo de discusi&#243;n.
                    @auth
                        <a href="{{ route('forum.create') }}" class="font-mono text-signal-mint hover:underline">crear el primero &rarr;</a>
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
                                    &#250;lt. {{ $thread->last_activity_at->diffForHumans() }}
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
        &middot; foro federado &mdash; los hilos pueden recibir aportes de instancias conectadas
    </p>
</div>
@endsection
