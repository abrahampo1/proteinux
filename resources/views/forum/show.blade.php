@extends('layouts.app')

@section('title', $thread->title)

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 sm:py-10 lg:px-8">

    {{-- Navegación con migas de pan --}}
    <div class="mb-6 flex flex-wrap items-center gap-2 font-mono text-[11px] uppercase tracking-[0.14em] text-ink-500 sm:mb-8">
        <a href="{{ route('home') }}" class="transition-colors hover:text-signal-mint">inicio</a>
        <span class="text-ink-400">/</span>
        <a href="{{ route('forum.index') }}" class="transition-colors hover:text-signal-mint">foro</a>
        @if($thread->protein_reference)
            <span class="text-ink-400">/</span>
            <a href="{{ route('forum.index', ['protein_ref' => $thread->protein_reference]) }}" class="transition-colors hover:text-signal-mint">{{ $thread->protein_reference }}</a>
        @endif
        <span class="text-ink-400">/</span>
        <span class="truncate text-ink-700">{{ Str::limit($thread->title, 40) }}</span>
    </div>

    {{-- Cabecera del hilo --}}
    <header class="mb-6 border-b border-ink-300 pb-5 sm:mb-8 sm:pb-6">
        <div class="mb-3 flex flex-wrap items-center gap-2">
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
                <a href="{{ route('proteins.show', $thread->protein_reference) }}"
                   class="inline-flex items-center gap-1 border border-signal-violet/40 bg-signal-violet/5 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-signal-violet transition-colors hover:bg-signal-violet/10">
                    catálogo · {{ $thread->protein_reference }}
                </a>
            @endif
            <x-origin-badge :domain="$thread->origin_domain" />
        </div>

        <h1 class="font-serif text-2xl leading-tight text-ink-900 sm:text-3xl lg:text-4xl">
            {{ $thread->title }}
        </h1>

        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">
            <x-federated-author :author="$thread->author()" :showDomain="true" />
            <time class="font-mono text-[10px] uppercase tracking-wider text-ink-400" datetime="{{ $thread->created_at->toIso8601String() }}">
                {{ $thread->created_at->format('Y-m-d H:i') }}
            </time>
            <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">
                {{ $thread->posts_count }} respuestas
            </span>
        </div>
    </header>

    <div class="grid gap-8 lg:grid-cols-12">
        {{-- Columna principal --}}
        <div class="lg:col-span-8">
            {{-- Cuerpo del hilo --}}
            <article class="panel p-5 sm:p-6">
                <div class="font-serif text-sm leading-relaxed text-ink-800 sm:text-base">
                    {!! nl2br(e($thread->body)) !!}
                </div>
            </article>

            {{-- Respuestas --}}
            <div class="mt-8">
                <div class="mb-4 label-tag">respuestas · {{ $posts->total() }}</div>

                @if($posts->total() === 0)
                    <div class="border border-dashed border-ink-300 bg-ink-100/50 px-6 py-10 text-center">
                        <p class="font-serif text-sm text-ink-600">Aún no hay respuestas. Sé el primero en participar.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($posts as $post)
                            <x-forum-post :post="$post" />
                        @endforeach
                    </div>

                    @if($posts->hasPages())
                        <div class="mt-6 border-t border-ink-300 pt-4">
                            {{ $posts->links() }}
                        </div>
                    @endif
                @endif
            </div>

            {{-- Formulario de respuesta --}}
            @auth
                @if(!$thread->is_locked)
                    <div class="mt-8 border-t border-ink-300 pt-6">
                        <div class="label-tag mb-4">nueva respuesta</div>
                        <form action="{{ route('forum.posts.store', $thread) }}" method="POST">
                            @csrf
                            <textarea name="body" rows="5" required
                                      class="input-lab mb-3 leading-relaxed @error('body') !border-signal-rust @enderror"
                                      placeholder="Escribe tu respuesta...">{{ old('body') }}</textarea>
                            @error('body')
                                <p class="mb-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
                            @enderror
                            <button type="submit" class="btn-primary">→ publicar respuesta</button>
                        </form>
                    </div>
                @else
                    <div class="mt-8 border border-dashed border-ink-300 bg-ink-100/50 px-6 py-6 text-center">
                        <p class="font-mono text-[11px] uppercase tracking-wider text-ink-500">
                            este hilo está cerrado y no acepta más respuestas
                        </p>
                    </div>
                @endif
            @else
                <div class="mt-8 border border-dashed border-ink-300 bg-ink-100/50 px-6 py-6 text-center">
                    <p class="font-serif text-sm text-ink-600">
                        <a href="{{ route('login') }}" class="font-mono text-signal-mint hover:underline">Inicia sesión</a>
                        para participar en la discusión.
                    </p>
                </div>
            @endauth
        </div>

        {{-- Barra lateral --}}
        <aside class="lg:col-span-4">
            {{-- Proteína vinculada --}}
            @if($thread->predictedJob)
                <div class="panel p-4 sm:p-5">
                    <div class="label-tag mb-3">predicción vinculada</div>
                    <h3 class="font-serif text-base leading-tight text-ink-900">
                        {{ $thread->predictedJob->displayName() }}
                    </h3>
                    @if($thread->predictedJob->organism)
                        <p class="mt-1 font-serif text-[13px] italic text-ink-600">
                            {{ $thread->predictedJob->organism }}
                        </p>
                    @endif

                    <dl class="mt-3 space-y-0 font-mono text-[11px] text-ink-700">
                        @if($thread->predictedJob->uniprot_id)
                            <div class="field">
                                <dt>UniProt</dt>
                                <dd class="tabular-nums">{{ $thread->predictedJob->uniprot_id }}</dd>
                            </div>
                        @endif
                        @if($thread->predictedJob->pdb_id)
                            <div class="field">
                                <dt>PDB</dt>
                                <dd class="tabular-nums">{{ $thread->predictedJob->pdb_id }}</dd>
                            </div>
                        @endif
                        @if($thread->predictedJob->sequence_length)
                            <div class="field">
                                <dt>residuos</dt>
                                <dd class="tabular-nums">{{ $thread->predictedJob->sequence_length }}</dd>
                            </div>
                        @endif
                    </dl>

                    <a href="{{ route('jobs.show', $thread->predictedJob->job_id) }}"
                       class="btn-primary mt-4 w-full justify-center">
                        ver predicción
                    </a>
                </div>
            @endif

            {{-- Proteína del catálogo --}}
            @if($thread->protein_reference)
                <div class="panel {{ $thread->predictedJob ? 'mt-4' : '' }} p-4 sm:p-5">
                    <div class="label-tag mb-3">proteína del catálogo</div>
                    <p class="font-mono text-sm text-ink-900">{{ $thread->protein_reference }}</p>
                    <a href="{{ route('proteins.show', $thread->protein_reference) }}"
                       class="btn-secondary mt-3 w-full justify-center">
                        ver en catálogo
                    </a>
                    <a href="{{ route('forum.index', ['protein_ref' => $thread->protein_reference]) }}"
                       class="mt-2 block text-center font-mono text-[10px] uppercase tracking-wider text-ink-500 transition-colors hover:text-signal-mint">
                        ver todos los hilos de esta proteína
                    </a>
                </div>
            @endif

            {{-- Info del hilo --}}
            <div class="panel {{ ($thread->predictedJob || $thread->protein_reference) ? 'mt-4' : '' }} p-4 sm:p-5">
                <div class="label-tag mb-3">info del hilo</div>
                <dl class="space-y-0 font-mono text-[11px] text-ink-700">
                    <div class="field">
                        <dt>creado</dt>
                        <dd>{{ $thread->created_at->format('Y-m-d') }}</dd>
                    </div>
                    <div class="field">
                        <dt>respuestas</dt>
                        <dd class="tabular-nums">{{ $thread->posts_count }}</dd>
                    </div>
                    @if($thread->last_activity_at)
                        <div class="field">
                            <dt>últ. actividad</dt>
                            <dd>{{ $thread->last_activity_at->diffForHumans() }}</dd>
                        </div>
                    @endif
                    @if($thread->origin_domain)
                        <div class="field">
                            <dt>origen</dt>
                            <dd>{{ $thread->origin_domain }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </aside>
    </div>

    <p class="mt-10 border-t border-ink-300 pt-4 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">
        · hilo federado — las respuestas pueden provenir de instancias conectadas
    </p>
</div>
@endsection
