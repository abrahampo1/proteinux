@extends('layouts.app')

@section('title', 'Protein Catalog')

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-12 sm:px-6 lg:px-8">

    {{-- Header block --}}
    <header class="mb-10 border-b border-ink-700 pb-8">
        <div class="label-tag">section 03 · catalog</div>
        <div class="mt-3 flex flex-wrap items-end justify-between gap-4">
            <h1 class="font-serif text-4xl text-ink-50">Protein Catalog</h1>
            <span class="font-mono text-[11px] uppercase tracking-[0.14em] text-ink-400">
                {{ count($proteins) }} entries · uniprot · pdb cross-ref
            </span>
        </div>
        <p class="mt-3 max-w-2xl font-serif text-sm leading-relaxed text-ink-300">
            Curated set of well-characterised proteins. Every entry carries
            verified UniProt and PDB identifiers, biological function and a
            ready-to-submit FASTA payload.
        </p>
    </header>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('proteins.index') }}" class="mb-8 panel p-4">
        <div class="grid gap-3 sm:grid-cols-12">
            <div class="sm:col-span-7">
                <label class="label-tag mb-1.5 block">query</label>
                <input type="text" name="search" value="{{ $currentSearch }}"
                       placeholder="search by name, organism, keyword…"
                       class="input-lab">
            </div>
            <div class="sm:col-span-3">
                <label class="label-tag mb-1.5 block">family</label>
                <select name="category" class="input-lab">
                    <option value="">— all families —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $currentCategory === $cat ? 'selected' : '' }}>{{ str_replace('-', ' ', $cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1 justify-center">apply</button>
                @if($currentSearch || $currentCategory)
                    <a href="{{ route('proteins.index') }}" class="btn-secondary px-3" title="Clear">×</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Result table --}}
    @if(!empty($proteins))
        <div class="panel overflow-x-auto">
            <table class="w-full font-mono text-xs">
                <thead class="border-b border-ink-700 bg-ink-900/80 text-[10px] uppercase tracking-[0.14em] text-ink-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium w-12">#</th>
                        <th class="px-4 py-3 text-left font-medium">protein</th>
                        <th class="px-4 py-3 text-left font-medium">organism</th>
                        <th class="px-4 py-3 text-left font-medium">family</th>
                        <th class="px-4 py-3 text-left font-medium">uniprot</th>
                        <th class="px-4 py-3 text-left font-medium">pdb</th>
                        <th class="px-4 py-3 text-right font-medium">length / aa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-700">
                    @foreach($proteins as $i => $protein)
                        <tr class="group transition-colors hover:bg-ink-800/40">
                            <td class="px-4 py-3 text-ink-500 tabular-nums">{{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('proteins.show', $protein['protein_id']) }}"
                                   class="text-ink-50 group-hover:text-signal-mint">
                                    {{ $protein['protein_name'] }}
                                </a>
                                @if(!empty($protein['description']))
                                    <div class="mt-0.5 max-w-md truncate font-serif text-[11px] text-ink-400">
                                        {{ $protein['description'] }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 italic text-ink-300">{{ $protein['organism'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if(!empty($protein['category']))
                                    <x-category-badge :category="$protein['category']" />
                                @else
                                    <span class="text-ink-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-ink-300">{{ $protein['uniprot_id'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink-300">{{ $protein['pdb_id'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink-100">{{ $protein['length'] ?? '?' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="panel py-20 text-center">
            <div class="label-tag justify-center">no results</div>
            <p class="mt-4 font-serif text-ink-300">No protein matches the current filter.</p>
            <a href="{{ route('proteins.index') }}" class="mt-5 inline-block font-mono text-xs uppercase tracking-wider text-signal-mint hover:underline">
                ← reset filter
            </a>
        </div>
    @endif
</div>
@endsection
