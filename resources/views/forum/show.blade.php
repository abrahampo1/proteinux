@extends('layouts.app')

@section('title', $thread->title)

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 sm:py-10 lg:px-8">

    {{-- Navegaci&#243;n --}}
    <div class="mb-6 font-mono text-[11px] uppercase tracking-[0.14em] text-ink-500 sm:mb-8">
        <a href="{{ route('forum.index') }}" class="transition-colors hover:text-signal-mint">&larr; volver al foro</a>
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
                        <p class="font-serif text-sm text-ink-600">A&#250;n no hay respuestas. S&#233; el primero en participar.</p>
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
                            <button type="submit" class="btn-primary">&rarr; publicar respuesta</button>
                        </form>
                    </div>
                @else
                    <div class="mt-8 border border-dashed border-ink-300 bg-ink-100/50 px-6 py-6 text-center">
                        <p class="font-mono text-[11px] uppercase tracking-wider text-ink-500">
                            este hilo est&#225; cerrado y no acepta m&#225;s respuestas
                        </p>
                    </div>
                @endif
            @else
                <div class="mt-8 border border-dashed border-ink-300 bg-ink-100/50 px-6 py-6 text-center">
                    <p class="font-serif text-sm text-ink-600">
                        <a href="{{ route('login') }}" class="font-mono text-signal-mint hover:underline">Inicia sesi&#243;n</a>
                        para participar en la discusi&#243;n.
                    </p>
                </div>
            @endauth
        </div>

        {{-- Barra lateral --}}
        <aside class="lg:col-span-4">
            {{-- Prote&#237;na vinculada --}}
            @if($thread->predictedJob)
                <div class="panel p-4 sm:p-5">
                    <div class="label-tag mb-3">prote&#237;na vinculada</div>
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
                        ver predicci&#243;n
                    </a>
                </div>
            @endif

            {{-- Info del hilo --}}
            <div class="panel mt-4 p-4 sm:p-5">
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
                            <dt>&#250;lt. actividad</dt>
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
        &middot; hilo federado &mdash; las respuestas pueden provenir de instancias conectadas
    </p>
</div>
@endsection
