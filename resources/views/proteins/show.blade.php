@extends('layouts.app')

@section('title', $protein['protein_name'] ?? 'Protein Detail')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    {{-- Back link --}}
    <a href="{{ route('proteins.index') }}" class="mb-6 inline-flex items-center gap-1 text-sm text-slate-400 hover:text-teal-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Back to Catalog
    </a>

    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white">{{ $protein['protein_name'] }}</h1>
                @if(!empty($protein['category']))
                    <x-category-badge :category="$protein['category']" />
                @endif
            </div>
            <p class="mt-1 text-slate-400 italic">{{ $protein['organism'] ?? 'Unknown organism' }}</p>
        </div>
        <a href="{{ route('jobs.create', ['fasta' => $protein['fasta_ready'] ?? '', 'filename' => ($protein['protein_id'] ?? 'protein') . '.fasta']) }}"
           class="shrink-0 rounded-lg bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-teal-600/20 transition-all hover:bg-teal-500">
            Predict This Structure
        </a>
    </div>

    {{-- Info grid --}}
    <div class="grid gap-6 sm:grid-cols-2">
        {{-- Details --}}
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-5">
            <h2 class="mb-4 text-sm font-semibold text-white">Details</h2>
            <dl class="space-y-3 text-sm">
                @if(!empty($protein['description']))
                    <div>
                        <dt class="text-slate-500">Description</dt>
                        <dd class="mt-0.5 text-slate-300">{{ $protein['description'] }}</dd>
                    </div>
                @endif
                @if(!empty($protein['function']))
                    <div>
                        <dt class="text-slate-500">Function</dt>
                        <dd class="mt-0.5 text-slate-300">{{ $protein['function'] }}</dd>
                    </div>
                @endif
                @if(!empty($protein['cellular_location']))
                    <div>
                        <dt class="text-slate-500">Cellular Location</dt>
                        <dd class="mt-0.5 text-slate-300">{{ $protein['cellular_location'] }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Properties --}}
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-5">
            <h2 class="mb-4 text-sm font-semibold text-white">Properties</h2>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-slate-500">Length</dt>
                    <dd class="font-medium text-white">{{ $protein['length'] ?? '—' }} aa</dd>
                </div>
                @if(!empty($protein['molecular_weight']))
                    <div>
                        <dt class="text-slate-500">Molecular Weight</dt>
                        <dd class="font-medium text-white">{{ number_format($protein['molecular_weight'], 1) }} Da</dd>
                    </div>
                @endif
                @if(!empty($protein['uniprot_id']))
                    <div>
                        <dt class="text-slate-500">UniProt</dt>
                        <dd><a href="https://www.uniprot.org/uniprot/{{ $protein['uniprot_id'] }}" target="_blank" class="font-medium text-teal-400 hover:underline">{{ $protein['uniprot_id'] }}</a></dd>
                    </div>
                @endif
                @if(!empty($protein['pdb_id']))
                    <div>
                        <dt class="text-slate-500">PDB</dt>
                        <dd><a href="https://www.rcsb.org/structure/{{ $protein['pdb_id'] }}" target="_blank" class="font-medium text-teal-400 hover:underline">{{ $protein['pdb_id'] }}</a></dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- FASTA sequence --}}
    @if(!empty($protein['fasta_ready']))
        <div class="mt-6 rounded-xl border border-slate-800 bg-slate-900 p-5">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-white">FASTA Sequence</h2>
                <button onclick="copyFasta()" id="copy-btn"
                        class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-400 transition-colors hover:border-teal-500 hover:text-white">
                    Copy
                </button>
            </div>
            <pre id="fasta-content" class="max-h-48 overflow-auto rounded-lg bg-slate-950 p-4 text-xs text-emerald-400 font-mono leading-relaxed">{{ $protein['fasta_ready'] }}</pre>
        </div>
    @endif

    {{-- Tags --}}
    @if(!empty($protein['tags']))
        <div class="mt-6 flex flex-wrap gap-2">
            @foreach($protein['tags'] as $tag)
                <span class="rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs text-slate-400">{{ $tag }}</span>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function copyFasta() {
    const text = document.getElementById('fasta-content').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copy-btn');
        btn.textContent = 'Copied!';
        btn.classList.add('border-teal-500', 'text-teal-400');
        setTimeout(() => {
            btn.textContent = 'Copy';
            btn.classList.remove('border-teal-500', 'text-teal-400');
        }, 2000);
    });
}
</script>
@endpush
