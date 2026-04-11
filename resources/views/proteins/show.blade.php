@extends('layouts.app')

@section('title', $protein['protein_name'] ?? 'Protein Detail')

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-12 sm:px-6 lg:px-8">

    {{-- Breadcrumb --}}
    <a href="{{ route('proteins.index') }}" class="font-mono text-[11px] uppercase tracking-[0.14em] text-ink-400 hover:text-signal-mint">
        ← / catalog
    </a>

    {{-- Header card --}}
    <header class="mt-4 border-b border-ink-700 pb-8">
        <div class="label-tag">entry · {{ str_pad((string)($protein['protein_id'] ?? '0'), 4, '0', STR_PAD_LEFT) }}</div>

        <div class="mt-3 grid items-end gap-6 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <h1 class="font-serif text-4xl text-ink-50 leading-tight">
                    {{ $protein['protein_name'] }}
                </h1>
                <p class="mt-2 font-serif text-lg italic text-ink-300">
                    {{ $protein['organism'] ?? 'Unknown organism' }}
                </p>
                @if(!empty($protein['category']))
                    <div class="mt-3"><x-category-badge :category="$protein['category']" /></div>
                @endif
            </div>
            <div class="lg:col-span-3 lg:text-right">
                <a href="{{ route('jobs.create', ['fasta' => $protein['fasta_ready'] ?? '', 'filename' => ($protein['protein_id'] ?? 'protein') . '.fasta']) }}"
                   class="btn-primary justify-center w-full lg:w-auto">
                    → predict structure
                </a>
            </div>
        </div>
    </header>

    {{-- Two-column spec sheet --}}
    <div class="mt-10 grid gap-8 lg:grid-cols-12">

        {{-- Left: prose --}}
        <article class="lg:col-span-7 space-y-8">
            @if(!empty($protein['description']))
                <section>
                    <div class="label-tag mb-3">§ 1 · description</div>
                    <p class="font-serif text-base leading-relaxed text-ink-100">
                        {{ $protein['description'] }}
                    </p>
                </section>
            @endif

            @if(!empty($protein['function']))
                <section>
                    <div class="label-tag mb-3">§ 2 · biological function</div>
                    <p class="font-serif text-base leading-relaxed text-ink-100">
                        {{ $protein['function'] }}
                    </p>
                </section>
            @endif

            @if(!empty($protein['cellular_location']))
                <section>
                    <div class="label-tag mb-3">§ 3 · cellular location</div>
                    <p class="font-serif text-base leading-relaxed text-ink-100">
                        {{ $protein['cellular_location'] }}
                    </p>
                </section>
            @endif

            {{-- FASTA viewer --}}
            @if(!empty($protein['fasta_ready']))
                <section>
                    <div class="mb-3 flex items-center justify-between">
                        <div class="label-tag">§ 4 · fasta payload</div>
                        <button onclick="copyFasta()" id="copy-btn" class="btn-secondary !py-1.5 !px-3 !text-[10px]">
                            copy
                        </button>
                    </div>
                    <pre id="fasta-content" class="max-h-72 overflow-auto border border-ink-700 bg-ink-950 p-4 font-mono text-[11px] leading-relaxed text-signal-mint">{{ $protein['fasta_ready'] }}</pre>
                </section>
            @endif
        </article>

        {{-- Right: spec dl --}}
        <aside class="lg:col-span-5">
            <div class="panel crosshair p-6">
                <div class="mb-4 flex items-center justify-between">
                    <span class="label-tag">spec sheet</span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-400">v1</span>
                </div>

                <dl class="space-y-0">
                    <div class="field">
                        <dt>length</dt>
                        <dd>{{ $protein['length'] ?? '—' }} <span class="text-ink-400">aa</span></dd>
                    </div>
                    @if(!empty($protein['molecular_weight']))
                        <div class="field">
                            <dt>molecular weight</dt>
                            <dd>{{ number_format($protein['molecular_weight'], 1) }} <span class="text-ink-400">Da</span></dd>
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
                            <dt>organism</dt>
                            <dd class="italic">{{ $protein['organism'] }}</dd>
                        </div>
                    @endif
                </dl>

                @if(!empty($protein['tags']))
                    <div class="mt-6 border-t border-ink-700 pt-4">
                        <div class="label-tag mb-3">tags</div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($protein['tags'] as $tag)
                                <span class="border border-ink-600 px-2 py-0.5 font-mono text-[10px] uppercase tracking-wider text-ink-300">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyFasta() {
    const text = document.getElementById('fasta-content').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copy-btn');
        btn.textContent = 'copied ✓';
        btn.classList.add('!text-signal-mint', '!border-signal-mint');
        setTimeout(() => {
            btn.textContent = 'copy';
            btn.classList.remove('!text-signal-mint', '!border-signal-mint');
        }, 2000);
    });
}
</script>
@endpush
