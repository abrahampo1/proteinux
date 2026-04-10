@extends('layouts.app')

@section('title', 'Proteinux')

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden border-b border-slate-800">
    <div class="absolute inset-0 bg-gradient-to-b from-teal-950/20 to-transparent"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-teal-500/30 bg-teal-500/10 px-4 py-1.5 text-xs font-medium text-teal-400">
                <span class="h-1.5 w-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                IMPACTHON 2026 &middot; Cathedra CAMELIA
            </div>
            <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl">
                Predict Protein Structures<br>
                <span class="text-teal-400">Without the Complexity</span>
            </h1>
            <p class="mt-6 text-lg text-slate-400 leading-relaxed">
                Submit any protein sequence and get a predicted 3D structure powered by AlphaFold2 on the CESGA Finis Terrae III supercomputer. No terminal required.
            </p>
            <div class="mt-8 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                <a href="{{ route('jobs.create') }}" class="rounded-lg bg-teal-600 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-600/20 transition-all hover:bg-teal-500 hover:shadow-teal-500/30">
                    Submit a Prediction
                </a>
                <a href="{{ route('proteins.index') }}" class="rounded-lg border border-slate-700 bg-slate-800/50 px-8 py-3 text-sm font-medium text-slate-300 transition-colors hover:border-slate-600 hover:text-white">
                    Browse Protein Catalog
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Stats --}}
@if($stats)
<section class="border-b border-slate-800 py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <x-stat-card :value="$stats['total_proteins'] ?? '—'" label="Proteins in Catalog" />
            <x-stat-card :value="$stats['embedded_proteins'] ?? '—'" label="Curated (real metadata)" />
            <x-stat-card :value="($stats['min_length'] ?? '—') . '-' . ($stats['max_length'] ?? '—')" label="Sequence Length (aa)" />
            <x-stat-card :value="count($stats['by_category'] ?? [])" label="Functional Categories" />
        </div>
    </div>
</section>
@endif

{{-- Quick Start --}}
@if(!empty($samples))
<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold text-white">Try It Now</h2>
            <p class="mt-2 text-slate-400">Click any protein to predict its 3D structure instantly.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach(array_slice($samples, 0, 8) as $sample)
                <a href="{{ route('jobs.create', ['fasta' => $sample['fasta_ready'] ?? $sample['fasta_sequence'] ?? '', 'filename' => strtolower(str_replace(' ', '_', $sample['protein_name'] ?? 'protein')) . '.fasta']) }}"
                   class="group rounded-xl border border-slate-800 bg-slate-900 p-5 transition-all hover:border-teal-500/40 hover:bg-slate-800/80">
                    <h3 class="font-semibold text-white group-hover:text-teal-400 transition-colors">{{ $sample['protein_name'] ?? $sample['protein_id'] }}</h3>
                    <p class="mt-1 text-xs text-slate-500 italic">{{ $sample['organism'] ?? '' }}</p>
                    <div class="mt-3 flex items-center gap-2 text-xs text-slate-400">
                        <span>{{ $sample['length'] ?? '?' }} aa</span>
                        @if(!empty($sample['category']))
                            <x-category-badge :category="$sample['category']" />
                        @endif
                    </div>
                    <p class="mt-3 text-xs text-teal-500 opacity-0 transition-opacity group-hover:opacity-100">Click to predict &rarr;</p>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- How it works --}}
<section class="border-t border-slate-800 py-16 bg-slate-900/30">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-10 text-center">
            <h2 class="text-2xl font-bold text-white">How It Works</h2>
            <p class="mt-2 text-slate-400">Three simple steps from sequence to structure.</p>
        </div>
        <div class="grid gap-8 sm:grid-cols-3">
            <div class="text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-600/20 text-teal-400 text-xl font-bold">1</div>
                <h3 class="font-semibold text-white">Paste Your Sequence</h3>
                <p class="mt-2 text-sm text-slate-400">Enter any protein sequence in FASTA format. Choose from our catalog or paste your own.</p>
            </div>
            <div class="text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-600/20 text-teal-400 text-xl font-bold">2</div>
                <h3 class="font-semibold text-white">Watch It Run</h3>
                <p class="mt-2 text-sm text-slate-400">Monitor progress as AlphaFold2 predicts the 3D structure on CESGA's GPU cluster in real time.</p>
            </div>
            <div class="text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-600/20 text-teal-400 text-xl font-bold">3</div>
                <h3 class="font-semibold text-white">Explore Results</h3>
                <p class="mt-2 text-sm text-slate-400">Interact with the 3D structure, confidence metrics, biological properties, and download files.</p>
            </div>
        </div>
    </div>
</section>

{{-- Explainer cards --}}
<section class="border-t border-slate-800 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-10 text-center">
            <h2 class="text-2xl font-bold text-white">Key Concepts</h2>
            <p class="mt-2 text-slate-400">New to protein structure prediction? Here's what you need to know.</p>
        </div>
        <div class="grid gap-6 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                <h3 class="mb-2 font-semibold text-teal-400">What is FASTA?</h3>
                <p class="text-sm text-slate-400 leading-relaxed">FASTA is the standard text format for protein sequences. It starts with a <code class="text-teal-400">&gt;</code> header line, followed by the amino acid sequence using single-letter codes (like M, Q, I, F, V, K...).</p>
            </div>
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                <h3 class="mb-2 font-semibold text-teal-400">What is pLDDT?</h3>
                <p class="text-sm text-slate-400 leading-relaxed">pLDDT (predicted Local Distance Difference Test) measures how confident AlphaFold2 is about each amino acid's position. Scores above 90 are very reliable; below 50 usually indicates disordered regions.</p>
            </div>
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                <h3 class="mb-2 font-semibold text-teal-400">What is AlphaFold?</h3>
                <p class="text-sm text-slate-400 leading-relaxed">AlphaFold2 is a deep learning system by DeepMind that predicts protein 3D structures from amino acid sequences with near-experimental accuracy. It won the CASP14 competition in 2020.</p>
            </div>
        </div>
    </div>
</section>
@endsection
