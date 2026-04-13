@extends('layouts.app')

@section('title', 'Documentos cientificos')

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 sm:py-12 lg:px-8">

    {{-- Cabecera --}}
    <header class="mb-8 border-b border-ink-300 pb-6 sm:mb-10 sm:pb-8">
        <div class="label-tag">seccion 08 . documentos</div>
        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between sm:gap-4">
            <h1 class="font-mono text-2xl uppercase tracking-wider text-ink-900 sm:text-3xl">/ documentos</h1>
            <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500 sm:text-[11px]">
                {{ $documents->total() }} entradas
            </span>
        </div>
        <p class="mt-3 max-w-2xl font-serif text-sm leading-relaxed text-ink-600">
            Repositorio compartido de documentos cientificos: articulos, apuntes,
            protocolos y datasets vinculados a predicciones proteicas.
        </p>
    </header>

    {{-- Barra de filtros --}}
    <form method="GET" action="{{ route('documents.index') }}" class="panel mb-6 p-4 sm:mb-8">
        <div class="grid gap-3 sm:grid-cols-12">
            <div class="sm:col-span-6">
                <label class="label-tag mb-1.5 block">buscar</label>
                <input type="text" name="search" value="{{ $currentSearch }}"
                       placeholder="titulo o DOI..."
                       class="input-lab">
            </div>
            <div class="sm:col-span-4">
                <label class="label-tag mb-1.5 block">tipo</label>
                <select name="type" class="input-lab">
                    <option value="">Todos</option>
                    <option value="paper" {{ $currentType === 'paper' ? 'selected' : '' }}>Articulo</option>
                    <option value="note" {{ $currentType === 'note' ? 'selected' : '' }}>Apunte</option>
                    <option value="protocol" {{ $currentType === 'protocol' ? 'selected' : '' }}>Protocolo</option>
                    <option value="dataset" {{ $currentType === 'dataset' ? 'selected' : '' }}>Dataset</option>
                </select>
            </div>
            <div class="flex items-end gap-2 sm:col-span-2">
                <button type="submit" class="btn-primary flex-1 justify-center">aplicar</button>
                @if($currentSearch || $currentType)
                    <a href="{{ route('documents.index') }}" class="btn-secondary px-3" title="Limpiar">x</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Boton subir --}}
    @auth
        <div class="mb-6 sm:mb-8">
            <a href="{{ route('documents.create') }}" class="btn-primary">
                + subir documento
            </a>
        </div>
    @endauth

    {{-- Resultados --}}
    @if($documents->total() === 0)
        <div class="panel px-4 py-16 text-center sm:py-20">
            <div class="label-tag justify-center">sin resultados</div>
            <p class="mt-4 font-serif text-ink-600">
                @if($currentSearch || $currentType)
                    Ningun documento coincide con el filtro actual.
                @else
                    Aun no se ha subido ningun documento.
                @endif
            </p>
            @if($currentSearch || $currentType)
                <a href="{{ route('documents.index') }}" class="mt-5 inline-block font-mono text-xs uppercase tracking-wider text-signal-mint hover:underline">
                    <- reiniciar filtro
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($documents as $doc)
                @php
                    $typeBadge = match($doc->type) {
                        'paper' => 'border-signal-mint/60 bg-signal-mint/10 text-signal-mint-deep',
                        'note' => 'border-signal-violet/60 bg-signal-violet/10 text-signal-violet',
                        'protocol' => 'border-signal-amber/60 bg-signal-amber/10 text-signal-amber',
                        'dataset' => 'border-sky-500/60 bg-sky-50 text-sky-700',
                        default => 'border-ink-300 bg-ink-100/60 text-ink-500',
                    };
                @endphp
                <article class="panel flex flex-col p-4 sm:p-5">
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <span class="inline-flex items-center border px-2 py-1 font-mono text-[10px] uppercase tracking-wider {{ $typeBadge }}">
                            {{ $doc->typeLabel() }}
                        </span>
                        @if($doc->isRemote())
                            <span class="inline-flex items-center gap-1 border border-ink-300 bg-ink-100/60 px-2 py-1 font-mono text-[9px] uppercase tracking-wider text-ink-500">
                                <span class="status-dot bg-signal-amber"></span>
                                {{ $doc->origin_domain }}
                            </span>
                        @endif
                    </div>

                    <h3 class="font-serif text-lg leading-tight text-ink-900 sm:text-xl">
                        <a href="{{ route('documents.show', $doc) }}" class="transition-colors hover:text-signal-mint-deep">
                            {{ $doc->title }}
                        </a>
                    </h3>

                    <div class="mt-2 font-mono text-[10px] uppercase tracking-wider text-ink-500">
                        {{ $doc->authorDisplayName() }}
                        <span class="text-ink-400">.</span>
                        <span class="text-ink-400">{{ $doc->authorFederatedId() }}</span>
                    </div>

                    <dl class="mt-4 space-y-0 font-mono text-[11px] text-ink-700">
                        @if($doc->proteins->count() > 0)
                            <div class="field">
                                <dt>proteinas</dt>
                                <dd class="tabular-nums">{{ $doc->proteins->count() }}</dd>
                            </div>
                        @endif
                        <div class="field">
                            <dt>recurso</dt>
                            <dd>
                                @if($doc->hasFile())
                                    <span class="text-signal-mint">archivo</span>
                                @elseif($doc->url)
                                    <span class="text-signal-mint">enlace</span>
                                @else
                                    <span class="text-ink-400">--</span>
                                @endif
                            </dd>
                        </div>
                        <div class="field">
                            <dt>fecha</dt>
                            <dd class="tabular-nums">{{ $doc->created_at->format('Y-m-d') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-auto pt-4">
                        <a href="{{ route('documents.show', $doc) }}" class="btn-primary w-full justify-center">
                            ver documento
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Paginacion --}}
        @if($documents->hasPages())
            <div class="mt-8 border-t border-ink-300 pt-4">
                {{ $documents->links() }}
            </div>
        @endif
    @endif

    <p class="mt-10 border-t border-ink-300 pt-4 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">
        . repositorio cientifico -- documentos compartidos por la comunidad federada
    </p>
</div>
@endsection
