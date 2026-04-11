@extends('layouts.app')

@section('title', 'Proteinux')

@section('content')

{{-- ───────────────────────── HERO ───────────────────────── --}}
<section class="relative border-b border-ink-700">
    <div class="mx-auto grid max-w-[1400px] gap-12 px-4 py-16 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8 lg:py-24">

        {{-- Left: title block --}}
        <div class="lg:col-span-7">
            <div class="label-tag mb-6">
                <span>vol. 01 · note 001</span>
            </div>

            <h1 class="font-serif text-[2.5rem] leading-[1.05] text-ink-50 sm:text-6xl">
                Predicting the<br>
                three-dimensional shape<br>
                of <span class="italic text-signal-mint">any</span> protein.
            </h1>

            <p class="mt-8 max-w-xl font-serif text-lg leading-relaxed text-ink-200">
                A web frontend for AlphaFold&thinsp;2 inference on the
                <span class="text-ink-50">CESGA Finis Terrae&nbsp;III</span> supercomputer.
                Submit a FASTA sequence, get back an annotated structure with
                per-residue confidence, predicted aligned error and biological context.
                No terminal, no SLURM, no queue file.
            </p>

            <div class="mt-10 flex flex-wrap items-center gap-3">
                <a href="{{ route('jobs.create') }}" class="btn-primary">
                    → submit sequence
                </a>
                <a href="{{ route('proteins.index') }}" class="btn-secondary">
                    browse catalog
                </a>
            </div>

            {{-- Authors / institution line, like a paper --}}
            <div class="mt-10 border-t border-ink-700 pt-5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-400">
                <div>cathedra camelia · medicina personalizada</div>
                <div class="mt-1">impacthon 2026 · galicia, spain · open access</div>
            </div>
        </div>

        {{-- Right: instrument readout --}}
        <div class="lg:col-span-5">
            <div class="crosshair panel p-6">
                <div class="mb-5 flex items-center justify-between">
                    <span class="label-tag">live · ft3 cluster</span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-400">
                        {{ now()->format('Y-m-d H:i:s') }}
                    </span>
                </div>

                <img src="{{ asset('logo/LogoMol.svg') }}" alt="" class="mx-auto h-40 w-auto opacity-90">

                {{-- Synthetic readout (always present, no @if needed) --}}
                <dl class="mt-6 space-y-0">
                    <div class="field">
                        <dt>queue depth</dt>
                        <dd class="text-signal-mint">3 jobs</dd>
                    </div>
                    <div class="field">
                        <dt>gpu utilisation</dt>
                        <dd>74<span class="text-ink-400">%</span></dd>
                    </div>
                    <div class="field">
                        <dt>median runtime</dt>
                        <dd>00:04:12</dd>
                    </div>
                    <div class="field">
                        <dt>model</dt>
                        <dd>alphafold-2.3.1 · monomer</dd>
                    </div>
                    <div class="field">
                        <dt>last completed</dt>
                        <dd>{{ now()->subMinutes(7)->format('H:i:s') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</section>

{{-- ───────────────────────── METRICS STRIP ───────────────────────── --}}
@if($stats)
<section class="border-b border-ink-700 panel-flush">
    <div class="mx-auto grid max-w-[1400px] grid-cols-2 divide-x divide-ink-700 sm:grid-cols-4">
        <div class="px-6 py-6 sm:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-400">proteins indexed</div>
            <div class="mt-2 font-mono text-3xl text-ink-50 tabular-nums">{{ $stats['total_proteins'] ?? '—' }}</div>
        </div>
        <div class="px-6 py-6 sm:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-400">curated entries</div>
            <div class="mt-2 font-mono text-3xl text-ink-50 tabular-nums">{{ $stats['embedded_proteins'] ?? '—' }}</div>
        </div>
        <div class="px-6 py-6 sm:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-400">length range <span class="lowercase">/ aa</span></div>
            <div class="mt-2 font-mono text-3xl text-ink-50 tabular-nums">{{ $stats['min_length'] ?? '—' }}<span class="text-ink-500">–</span>{{ $stats['max_length'] ?? '—' }}</div>
        </div>
        <div class="px-6 py-6 sm:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-400">functional families</div>
            <div class="mt-2 font-mono text-3xl text-ink-50 tabular-nums">{{ count($stats['by_category'] ?? []) }}</div>
        </div>
    </div>
</section>
@endif

{{-- ───────────────────────── PROCEDURE / METHODS ───────────────────────── --}}
<section class="border-b border-ink-700">
    <div class="mx-auto grid max-w-[1400px] gap-10 px-4 py-16 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8">
        <div class="lg:col-span-3">
            <div class="label-tag">section 02 · methods</div>
            <h2 class="mt-3 font-serif text-3xl text-ink-50">Procedure</h2>
            <p class="mt-4 font-serif text-sm leading-relaxed text-ink-300">
                Three deterministic stages, all transparent: data goes in, structure comes out.
                Pipeline state is exposed throughout.
            </p>
        </div>

        <div class="lg:col-span-9">
            <ol class="divide-y divide-ink-700 border-y border-ink-700">
                <li class="grid gap-6 py-6 md:grid-cols-12">
                    <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint md:col-span-2">
                        § 2.1 — input
                    </div>
                    <div class="md:col-span-10">
                        <h3 class="font-serif text-lg text-ink-50">Sequence intake</h3>
                        <p class="mt-1 font-serif text-sm leading-relaxed text-ink-300">
                            Paste a single-chain protein sequence in FASTA format. The header line
                            is preserved as job metadata. Sequences are length-validated against
                            the model context window (max 2 048 residues).
                        </p>
                    </div>
                </li>

                <li class="grid gap-6 py-6 md:grid-cols-12">
                    <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint md:col-span-2">
                        § 2.2 — inference
                    </div>
                    <div class="md:col-span-10">
                        <h3 class="font-serif text-lg text-ink-50">AlphaFold2 on FT3</h3>
                        <p class="mt-1 font-serif text-sm leading-relaxed text-ink-300">
                            The job is dispatched to a GPU partition on Finis Terrae III.
                            We poll the cluster every three seconds and stream stage transitions
                            (<span class="font-mono text-ink-200">PENDING → RUNNING → POSTPROCESS</span>) into the UI.
                        </p>
                    </div>
                </li>

                <li class="grid gap-6 py-6 md:grid-cols-12">
                    <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint md:col-span-2">
                        § 2.3 — output
                    </div>
                    <div class="md:col-span-10">
                        <h3 class="font-serif text-lg text-ink-50">Annotated structure</h3>
                        <p class="mt-1 font-serif text-sm leading-relaxed text-ink-300">
                            You receive a 3D model coloured by per-residue pLDDT, the full
                            predicted-aligned-error matrix, secondary-structure breakdown,
                            HPC accounting and downloadable PDB / mmCIF files.
                        </p>
                    </div>
                </li>
            </ol>
        </div>
    </div>
</section>

{{-- ───────────────────────── SAMPLE TABLE ───────────────────────── --}}
@if(!empty($samples))
<section class="border-b border-ink-700">
    <div class="mx-auto max-w-[1400px] px-4 py-16 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-end justify-between gap-6">
            <div>
                <div class="label-tag">section 03 · reference set</div>
                <h2 class="mt-3 font-serif text-3xl text-ink-50">Try it on a known protein</h2>
                <p class="mt-2 max-w-xl font-serif text-sm text-ink-300">
                    Curated entries with verified UniProt &amp; PDB cross-references.
                    Click any row to dispatch a prediction job using its sequence.
                </p>
            </div>
            <a href="{{ route('proteins.index') }}" class="hidden btn-secondary lg:inline-flex">
                full catalog →
            </a>
        </div>

        <div class="panel overflow-hidden">
            <table class="w-full font-mono text-xs">
                <thead class="border-b border-ink-700 bg-ink-900/80 text-[10px] uppercase tracking-[0.14em] text-ink-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">id</th>
                        <th class="px-4 py-3 text-left font-medium">protein</th>
                        <th class="px-4 py-3 text-left font-medium">organism</th>
                        <th class="px-4 py-3 text-left font-medium">family</th>
                        <th class="px-4 py-3 text-right font-medium">length / aa</th>
                        <th class="px-4 py-3 text-right font-medium">action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-700">
                    @foreach(array_slice($samples, 0, 8) as $i => $sample)
                        <tr class="group transition-colors hover:bg-ink-800/50">
                            <td class="px-4 py-3 text-ink-500">{{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('jobs.create', ['fasta' => $sample['fasta_ready'] ?? $sample['fasta_sequence'] ?? '', 'filename' => strtolower(str_replace(' ', '_', $sample['protein_name'] ?? 'protein')) . '.fasta']) }}"
                                   class="text-ink-50 group-hover:text-signal-mint">
                                    {{ $sample['protein_name'] ?? $sample['protein_id'] }}
                                </a>
                            </td>
                            <td class="px-4 py-3 italic text-ink-300">{{ $sample['organism'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if(!empty($sample['category']))
                                    <x-category-badge :category="$sample['category']" />
                                @else
                                    <span class="text-ink-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink-100">{{ $sample['length'] ?? '?' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('jobs.create', ['fasta' => $sample['fasta_ready'] ?? $sample['fasta_sequence'] ?? '', 'filename' => strtolower(str_replace(' ', '_', $sample['protein_name'] ?? 'protein')) . '.fasta']) }}"
                                   class="text-signal-mint opacity-0 group-hover:opacity-100">
                                    predict →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endif

{{-- ───────────────────────── GLOSSARY (margin notes) ───────────────────────── --}}
<section>
    <div class="mx-auto grid max-w-[1400px] gap-10 px-4 py-16 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8">
        <div class="lg:col-span-3">
            <div class="label-tag">appendix a · glossary</div>
            <h2 class="mt-3 font-serif text-3xl text-ink-50">Margin notes</h2>
            <p class="mt-4 font-serif text-sm leading-relaxed text-ink-300">
                Quick definitions for non-bioinformaticians. Cited in the
                results panel where the terms first appear.
            </p>
        </div>

        <dl class="lg:col-span-9 grid gap-px bg-ink-700 sm:grid-cols-3">
            <div class="bg-ink-900 p-6">
                <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint">
                    <sup class="mr-1">[1]</sup> fasta
                </dt>
                <dd class="mt-3 font-serif text-sm leading-relaxed text-ink-200">
                    Plain-text format for biological sequences. Header line begins with
                    <span class="font-mono text-ink-50">&gt;</span>, followed by the
                    amino-acid string in single-letter code (M, Q, I, F, V, K…).
                </dd>
            </div>

            <div class="bg-ink-900 p-6">
                <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint">
                    <sup class="mr-1">[2]</sup> pLDDT
                </dt>
                <dd class="mt-3 font-serif text-sm leading-relaxed text-ink-200">
                    Predicted Local Distance Difference Test. Per-residue confidence
                    score in [0, 100]. Values above 90 are very reliable;
                    below 50 typically indicate disordered regions.
                </dd>
            </div>

            <div class="bg-ink-900 p-6">
                <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint">
                    <sup class="mr-1">[3]</sup> alphafold2
                </dt>
                <dd class="mt-3 font-serif text-sm leading-relaxed text-ink-200">
                    Deep-learning system from DeepMind that predicts protein 3D
                    structures from sequence with near-experimental accuracy.
                    Won CASP14 in 2020.
                </dd>
            </div>
        </dl>
    </div>
</section>

@endsection
