@extends('layouts.app')

@section('title', $protein['protein_name'] ?? 'Detalle de proteína')

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 sm:py-12 lg:px-8">

    {{-- Migas --}}
    <a href="{{ route('proteins.index') }}" class="font-mono text-[11px] uppercase tracking-[0.14em] text-ink-500 hover:text-signal-mint">
        ← / catálogo
    </a>

    {{-- Cabecera --}}
    <header class="mt-4 border-b border-ink-300 pb-6 sm:pb-8">
        <div class="label-tag">entrada · {{ str_pad((string)($protein['protein_id'] ?? '0'), 4, '0', STR_PAD_LEFT) }}</div>

        <div class="mt-3 grid items-end gap-5 lg:grid-cols-12 lg:gap-6">
            <div class="lg:col-span-9">
                <h1 class="font-serif text-3xl leading-tight text-ink-900 sm:text-4xl">
                    {{ $protein['protein_name'] }}
                </h1>
                <p class="mt-2 font-serif text-base italic text-ink-600 sm:text-lg">
                    {{ $protein['organism'] ?? 'Organismo desconocido' }}
                </p>
                @if(!empty($protein['category']))
                    <div class="mt-3"><x-category-badge :category="$protein['category']" /></div>
                @endif
            </div>
            <div class="lg:col-span-3 lg:text-right">
                <a href="{{ route('jobs.create', ['fasta' => $protein['fasta_ready'] ?? '', 'filename' => ($protein['protein_id'] ?? 'protein') . '.fasta']) }}"
                   class="btn-primary w-full justify-center lg:w-auto">
                    → predecir estructura
                </a>
            </div>
        </div>
    </header>

    {{-- Ficha técnica a dos columnas --}}
    <div class="mt-8 grid gap-6 sm:mt-10 sm:gap-8 lg:grid-cols-12">

        {{-- Izquierda: prosa --}}
        <article class="space-y-6 sm:space-y-8 lg:col-span-7">
            @if(!empty($protein['description']))
                <section>
                    <div class="label-tag mb-3">§ 1 · descripción</div>
                    <p class="font-serif text-base leading-relaxed text-ink-800">
                        {{ $protein['description'] }}
                    </p>
                </section>
            @endif

            @if(!empty($protein['function']))
                <section>
                    <div class="label-tag mb-3">§ 2 · función biológica</div>
                    <p class="font-serif text-base leading-relaxed text-ink-800">
                        {{ $protein['function'] }}
                    </p>
                </section>
            @endif

            @if(!empty($protein['cellular_location']))
                <section>
                    <div class="label-tag mb-3">§ 3 · localización celular</div>
                    <p class="font-serif text-base leading-relaxed text-ink-800">
                        {{ $protein['cellular_location'] }}
                    </p>
                </section>
            @endif

            {{-- Visor FASTA --}}
            @if(!empty($protein['fasta_ready']))
                <section>
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <div class="label-tag">§ 4 · carga fasta</div>
                        <button onclick="copyFasta()" id="copy-btn" class="btn-secondary !px-3 !py-1.5 !text-[10px]">
                            copiar
                        </button>
                    </div>
                    <pre id="fasta-content" class="max-h-64 overflow-auto border border-ink-300 bg-ink-100 p-3 font-mono text-[10px] leading-relaxed text-signal-mint-deep sm:max-h-72 sm:p-4 sm:text-[11px]" style="white-space: pre-wrap; word-break: break-all;">{{ $protein['fasta_ready'] }}</pre>
                </section>
            @endif
        </article>

        {{-- Derecha: ficha técnica --}}
        <aside class="lg:col-span-5">
            <div class="panel crosshair p-5 sm:p-6">
                <div class="mb-4 flex items-center justify-between">
                    <span class="label-tag">ficha técnica</span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">v1</span>
                </div>

                <dl class="space-y-0">
                    <div class="field">
                        <dt>longitud</dt>
                        <dd>{{ $protein['length'] ?? '—' }} <span class="text-ink-500">aa</span></dd>
                    </div>
                    @if(!empty($protein['molecular_weight']))
                        <div class="field">
                            <dt>peso molecular</dt>
                            <dd>{{ number_format($protein['molecular_weight'], 1) }} <span class="text-ink-500">Da</span></dd>
                        </div>
                    @endif
                    @if(!empty($protein['uniprot_id']))
                        <div class="field">
                            <dt>uniprot</dt>
                            <dd>
                                <a href="https://www.uniprot.org/uniprot/{{ $protein['uniprot_id'] }}" target="_blank"
                                   class="text-signal-mint hover:underline">{{ $protein['uniprot_id'] }} ↗</a>
                            </dd>
                        </div>
                    @endif
                    @if(!empty($protein['pdb_id']))
                        <div class="field">
                            <dt>pdb</dt>
                            <dd>
                                <a href="https://www.rcsb.org/structure/{{ $protein['pdb_id'] }}" target="_blank"
                                   class="text-signal-mint hover:underline">{{ $protein['pdb_id'] }} ↗</a>
                            </dd>
                        </div>
                    @endif
                    @if(!empty($protein['organism']))
                        <div class="field">
                            <dt>organismo</dt>
                            <dd class="italic">{{ $protein['organism'] }}</dd>
                        </div>
                    @endif
                </dl>

                @if(!empty($protein['tags']))
                    <div class="mt-6 border-t border-ink-300 pt-4">
                        <div class="label-tag mb-3">etiquetas</div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($protein['tags'] as $tag)
                                <span class="border border-ink-400 px-2 py-0.5 font-mono text-[10px] uppercase tracking-wider text-ink-600">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </aside>
    </div>

    {{-- Discusiones del foro --}}
    <section class="mt-10 border-t border-ink-300 pt-8 sm:mt-12 sm:pt-10">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <div class="label-tag">§ 5 · discusiones</div>
                <h2 class="mt-2 font-serif text-xl text-ink-900 sm:text-2xl">Hilos del foro</h2>
                <p class="mt-1 font-serif text-sm text-ink-600">
                    {{ $threadCount }} {{ $threadCount === 1 ? 'discusión' : 'discusiones' }} sobre esta proteína.
                </p>
            </div>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('forum.create', ['protein_ref' => $protein['protein_id']]) }}" class="btn-primary">
                        + nuevo hilo
                    </a>
                @endauth
                @if($threadCount > 5)
                    <a href="{{ route('forum.index', ['protein_ref' => $protein['protein_id']]) }}" class="btn-secondary">
                        ver todos →
                    </a>
                @endif
            </div>
        </div>

        @if($threads->isEmpty())
            <div class="border border-dashed border-ink-300 bg-ink-100/50 px-6 py-10 text-center">
                <p class="font-serif text-sm text-ink-600">
                    Aún no hay discusiones sobre esta proteína.
                    @auth
                        <a href="{{ route('forum.create', ['protein_ref' => $protein['protein_id']]) }}" class="font-mono text-signal-mint hover:underline">Inicia la primera →</a>
                    @else
                        <a href="{{ route('login') }}" class="font-mono text-signal-mint hover:underline">Inicia sesión</a> para crear un hilo.
                    @endauth
                </p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($threads as $thread)
                    <a href="{{ route('forum.show', $thread) }}"
                       class="group block border border-ink-300 bg-ink-100/60 p-4 transition-all hover:border-signal-mint/60 hover:bg-ink-100">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h3 class="font-serif text-base leading-tight text-ink-900 group-hover:text-signal-mint-deep">
                                    {{ $thread->title }}
                                </h3>
                                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <x-federated-author :author="$thread->author()" />
                                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">
                                        {{ $thread->created_at->format('Y-m-d') }}
                                    </span>
                                </div>
                            </div>
                            <span class="shrink-0 font-mono text-[11px] tabular-nums text-ink-700">
                                {{ $thread->posts_count }} <span class="text-ink-400">resp.</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            @if($threadCount > 5)
                <div class="mt-4 text-center">
                    <a href="{{ route('forum.index', ['protein_ref' => $protein['protein_id']]) }}"
                       class="font-mono text-[11px] uppercase tracking-wider text-signal-mint transition-colors hover:text-signal-mint-deep">
                        ver las {{ $threadCount }} discusiones →
                    </a>
                </div>
            @endif
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
function copyFasta() {
    const text = document.getElementById('fasta-content').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copy-btn');
        btn.textContent = 'copiado ✓';
        btn.classList.add('!text-signal-mint', '!border-signal-mint');
        setTimeout(() => {
            btn.textContent = 'copiar';
            btn.classList.remove('!text-signal-mint', '!border-signal-mint');
        }, 2000);
    });
}
</script>
@endpush
