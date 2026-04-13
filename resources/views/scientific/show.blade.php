@extends('layouts.app')

@section('title', $document->title)

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">

    {{-- Cabecera --}}
    <header class="mb-8 border-b border-ink-300 pb-6 sm:mb-10 sm:pb-8">
        <div class="flex flex-wrap items-center gap-3">
            <div class="label-tag">documento</div>
            @php
                $typeBadge = match($document->type) {
                    'paper' => 'border-signal-mint/60 bg-signal-mint/10 text-signal-mint-deep',
                    'note' => 'border-signal-violet/60 bg-signal-violet/10 text-signal-violet',
                    'protocol' => 'border-signal-amber/60 bg-signal-amber/10 text-signal-amber',
                    'dataset' => 'border-sky-500/60 bg-sky-50 text-sky-700',
                    default => 'border-ink-300 bg-ink-100/60 text-ink-500',
                };
            @endphp
            <span class="inline-flex items-center border px-2 py-1 font-mono text-[10px] uppercase tracking-wider {{ $typeBadge }}">
                {{ $document->typeLabel() }}
            </span>
            @if($document->isRemote())
                <span class="inline-flex items-center gap-1 border border-ink-300 bg-ink-100/60 px-2 py-1 font-mono text-[9px] uppercase tracking-wider text-ink-500">
                    <span class="status-dot bg-signal-amber"></span>
                    origen: {{ $document->origin_domain }}
                </span>
            @endif
            @if($document->is_shared)
                <span class="inline-flex items-center gap-1 border border-signal-mint/40 bg-signal-mint/10 px-2 py-1 font-mono text-[9px] uppercase tracking-wider text-signal-mint-deep">
                    <span class="status-dot bg-signal-mint shadow-[0_0_6px_rgba(13,148,136,0.55)]"></span>
                    federado
                </span>
            @endif
        </div>

        <h1 class="mt-4 font-serif text-3xl leading-tight text-ink-900 sm:text-4xl">
            {{ $document->title }}
        </h1>

        <div class="mt-3 font-mono text-[11px] uppercase tracking-wider text-ink-500">
            por {{ $document->authorDisplayName() }}
            <span class="text-ink-400">.</span>
            <span class="text-ink-400">{{ $document->authorFederatedId() }}</span>
            <span class="text-ink-400">.</span>
            <span class="tabular-nums">{{ $document->created_at->format('Y-m-d H:i') }}</span>
        </div>
    </header>

    {{-- Contenido principal --}}
    <div class="grid gap-8 lg:grid-cols-3">

        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Descripcion --}}
            @if($document->description)
                <section class="panel p-5 sm:p-6">
                    <div class="label-tag mb-3">descripcion</div>
                    <div class="font-serif text-sm leading-relaxed text-ink-700 sm:text-base">
                        {!! nl2br(e($document->description)) !!}
                    </div>
                </section>
            @endif

            {{-- Proteinas vinculadas --}}
            @if($document->proteins->count() > 0)
                <section>
                    <div class="label-tag mb-4">proteinas vinculadas</div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($document->proteins as $protein)
                            <a href="{{ route('jobs.show', $protein->job_id) }}"
                               class="panel flex items-center gap-3 p-4 transition-colors hover:border-signal-mint/60">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center border border-ink-300 bg-ink-150/80 font-mono text-[10px] uppercase text-ink-500">
                                    PDB
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate font-serif text-sm text-ink-900">{{ $protein->displayName() }}</div>
                                    @if($protein->organism)
                                        <div class="truncate font-serif text-xs italic text-ink-500">{{ $protein->organism }}</div>
                                    @endif
                                    <div class="mt-0.5 font-mono text-[10px] uppercase tracking-wider text-ink-400">
                                        @if($protein->sequence_length)
                                            {{ $protein->sequence_length }} aa
                                        @endif
                                        @if($protein->plddt_mean)
                                            . plddt {{ number_format($protein->plddt_mean, 1) }}
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        {{-- Barra lateral --}}
        <div class="space-y-6">

            {{-- Descarga / enlace --}}
            <section class="panel p-5">
                <div class="label-tag mb-4">recurso</div>
                @if($document->hasFile())
                    <a href="{{ route('documents.download', $document) }}"
                       class="btn-primary w-full justify-center">
                        descargar archivo
                    </a>
                    <dl class="mt-4 space-y-0 font-mono text-[11px] text-ink-700">
                        <div class="field">
                            <dt>archivo</dt>
                            <dd class="truncate max-w-[140px]" title="{{ $document->file_name }}">{{ $document->file_name }}</dd>
                        </div>
                        @if($document->file_size)
                            <div class="field">
                                <dt>tamano</dt>
                                <dd class="tabular-nums">{{ number_format($document->file_size / 1024, 0) }} KB</dd>
                            </div>
                        @endif
                        @if($document->mime_type)
                            <div class="field">
                                <dt>formato</dt>
                                <dd>{{ $document->mime_type }}</dd>
                            </div>
                        @endif
                    </dl>
                @elseif($document->url)
                    <a href="{{ $document->url }}" target="_blank" rel="noopener"
                       class="btn-primary w-full justify-center">
                        abrir enlace externo ->
                    </a>
                    <p class="mt-3 break-all font-mono text-[10px] text-ink-400">
                        {{ $document->url }}
                    </p>
                @else
                    <p class="font-mono text-[11px] uppercase tracking-wider text-ink-400">
                        sin archivo ni enlace disponible
                    </p>
                @endif
            </section>

            {{-- DOI --}}
            @if($document->doi)
                <section class="panel p-5">
                    <div class="label-tag mb-3">doi</div>
                    <a href="https://doi.org/{{ $document->doi }}" target="_blank" rel="noopener"
                       class="font-mono text-sm text-signal-mint transition-colors hover:text-signal-mint-deep hover:underline">
                        {{ $document->doi }}
                    </a>
                </section>
            @endif

            {{-- Metadatos --}}
            <section class="panel p-5">
                <div class="label-tag mb-3">metadatos</div>
                <dl class="space-y-0 font-mono text-[11px] text-ink-700">
                    <div class="field">
                        <dt>tipo</dt>
                        <dd>{{ $document->typeLabel() }}</dd>
                    </div>
                    <div class="field">
                        <dt>autor</dt>
                        <dd class="truncate max-w-[140px]">{{ $document->authorDisplayName() }}</dd>
                    </div>
                    <div class="field">
                        <dt>identidad</dt>
                        <dd class="truncate max-w-[140px]">{{ $document->authorFederatedId() }}</dd>
                    </div>
                    <div class="field">
                        <dt>creado</dt>
                        <dd class="tabular-nums">{{ $document->created_at->format('Y-m-d') }}</dd>
                    </div>
                    <div class="field">
                        <dt>compartido</dt>
                        <dd>{{ $document->is_shared ? 'si' : 'no' }}</dd>
                    </div>
                    @if($document->isRemote())
                        <div class="field">
                            <dt>origen</dt>
                            <dd>{{ $document->origin_domain }}</dd>
                        </div>
                    @endif
                </dl>
            </section>
        </div>
    </div>

    {{-- Volver --}}
    <div class="mt-10 border-t border-ink-300 pt-4">
        <a href="{{ route('documents.index') }}" class="font-mono text-xs uppercase tracking-wider text-signal-mint hover:underline">
            <- volver a documentos
        </a>
    </div>
</div>
@endsection
