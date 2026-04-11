@extends('layouts.app')

@section('title', 'Catálogo de proteínas')

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 sm:py-12 lg:px-8">

    {{-- Cabecera --}}
    <header class="mb-8 border-b border-ink-300 pb-6 sm:mb-10 sm:pb-8">
        <div class="label-tag">sección 03 · catálogo</div>
        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between sm:gap-4">
            <h1 class="font-serif text-3xl text-ink-900 sm:text-4xl">Catálogo de proteínas</h1>
            <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500 sm:text-[11px]">
                {{ count($proteins) }} entradas · uniprot · pdb
            </span>
        </div>
        <p class="mt-3 max-w-2xl font-serif text-sm leading-relaxed text-ink-600">
            Conjunto curado de proteínas bien caracterizadas. Cada entrada lleva
            identificadores verificados de UniProt y PDB, su función biológica y
            una carga FASTA lista para enviar.
        </p>
    </header>

    {{-- Barra de filtros --}}
    <form method="GET" action="{{ route('proteins.index') }}" class="panel mb-6 p-4 sm:mb-8">
        <div class="grid gap-3 sm:grid-cols-12">
            <div class="sm:col-span-7">
                <label class="label-tag mb-1.5 block">consulta</label>
                <input type="text" name="search" value="{{ $currentSearch }}"
                       placeholder="busca por nombre, organismo o palabra clave…"
                       class="input-lab">
            </div>
            <div class="sm:col-span-3">
                <label class="label-tag mb-1.5 block">familia</label>
                <select name="category" class="input-lab">
                    <option value="">— todas las familias —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $currentCategory === $cat ? 'selected' : '' }}>{{ str_replace('-', ' ', $cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 sm:col-span-2">
                <button type="submit" class="btn-primary flex-1 justify-center">aplicar</button>
                @if($currentSearch || $currentCategory)
                    <a href="{{ route('proteins.index') }}" class="btn-secondary px-3" title="Limpiar">×</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Resultados --}}
    @if(!empty($proteins))
        {{-- Vista en tarjetas · móvil / tablet pequeña --}}
        <div class="space-y-3 lg:hidden">
            @foreach($proteins as $i => $protein)
                <a href="{{ route('proteins.show', $protein['protein_id']) }}"
                   class="group block border border-ink-300 bg-ink-100/60 p-4 transition-colors hover:border-signal-mint/60 hover:bg-ink-100">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="font-mono text-[10px] uppercase tracking-wider text-ink-400 tabular-nums">
                                {{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}
                            </div>
                            <h3 class="mt-1 font-serif text-base leading-tight text-ink-900 group-hover:text-signal-mint-deep sm:text-lg">
                                {{ $protein['protein_name'] }}
                            </h3>
                            <div class="mt-1 font-serif text-xs italic text-ink-600">
                                {{ $protein['organism'] ?? '—' }}
                            </div>
                            @if(!empty($protein['description']))
                                <p class="mt-2 line-clamp-2 font-serif text-[13px] leading-relaxed text-ink-600">
                                    {{ $protein['description'] }}
                                </p>
                            @endif
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            @if(!empty($protein['category']))
                                <x-category-badge :category="$protein['category']" />
                            @endif
                            <div class="font-mono text-[11px] tabular-nums text-ink-700">
                                {{ $protein['length'] ?? '?' }} <span class="text-ink-400">aa</span>
                            </div>
                        </div>
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1 border-t border-dashed border-ink-300 pt-3 font-mono text-[10px] uppercase tracking-wider text-ink-500">
                        <div class="flex justify-between gap-2">
                            <dt>uniprot</dt>
                            <dd class="truncate text-ink-800">{{ $protein['uniprot_id'] ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt>pdb</dt>
                            <dd class="truncate text-ink-800">{{ $protein['pdb_id'] ?? '—' }}</dd>
                        </div>
                    </dl>
                </a>
            @endforeach
        </div>

        {{-- Tabla · desktop --}}
        <div class="panel hidden overflow-x-auto lg:block">
            <table class="w-full font-mono text-xs">
                <thead class="border-b border-ink-300 bg-ink-150/80 text-[10px] uppercase tracking-[0.14em] text-ink-500">
                    <tr>
                        <th class="w-12 px-4 py-3 text-left font-medium">#</th>
                        <th class="px-4 py-3 text-left font-medium">proteína</th>
                        <th class="px-4 py-3 text-left font-medium">organismo</th>
                        <th class="px-4 py-3 text-left font-medium">familia</th>
                        <th class="px-4 py-3 text-left font-medium">uniprot</th>
                        <th class="px-4 py-3 text-left font-medium">pdb</th>
                        <th class="px-4 py-3 text-right font-medium">longitud / aa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-300">
                    @foreach($proteins as $i => $protein)
                        <tr class="group transition-colors hover:bg-ink-150/60">
                            <td class="px-4 py-3 tabular-nums text-ink-400">{{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('proteins.show', $protein['protein_id']) }}"
                                   class="text-ink-900 group-hover:text-signal-mint">
                                    {{ $protein['protein_name'] }}
                                </a>
                                @if(!empty($protein['description']))
                                    <div class="mt-0.5 max-w-md truncate font-serif text-[11px] text-ink-500">
                                        {{ $protein['description'] }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 italic text-ink-600">{{ $protein['organism'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if(!empty($protein['category']))
                                    <x-category-badge :category="$protein['category']" />
                                @else
                                    <span class="text-ink-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-ink-600">{{ $protein['uniprot_id'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $protein['pdb_id'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink-800">{{ $protein['length'] ?? '?' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="panel px-4 py-16 text-center sm:py-20">
            <div class="label-tag justify-center">sin resultados</div>
            <p class="mt-4 font-serif text-ink-600">Ninguna proteína coincide con el filtro actual.</p>
            <a href="{{ route('proteins.index') }}" class="mt-5 inline-block font-mono text-xs uppercase tracking-wider text-signal-mint hover:underline">
                ← reiniciar filtro
            </a>
        </div>
    @endif
</div>
@endsection
