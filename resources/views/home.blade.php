@extends('layouts.app')

@section('title', 'Proteinux')

@section('content')

{{-- ───────────────────────── HERO ───────────────────────── --}}
<section class="relative border-b border-ink-300">
    <div class="mx-auto grid max-w-[1400px] gap-12 px-4 py-16 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8 lg:py-24">

        {{-- Izquierda: bloque de título --}}
        <div class="lg:col-span-7">
            <div class="label-tag mb-6">
                <span>vol. 01 · nota 001</span>
            </div>

            <h1 class="font-serif text-[2.5rem] leading-[1.05] text-ink-900 sm:text-6xl">
                Predecimos la<br>
                forma tridimensional<br>
                de <span class="italic text-signal-mint">cualquier</span> proteína.
            </h1>

            <p class="mt-8 max-w-xl font-serif text-lg leading-relaxed text-ink-700">
                Una interfaz web para inferencia con AlphaFold&thinsp;2 sobre el supercomputador
                <span class="text-ink-900">CESGA Finis Terrae&nbsp;III</span>.
                Envía una secuencia FASTA y recibe una estructura anotada con
                confianza por residuo, error alineado predicho y contexto biológico.
                Sin terminal, sin SLURM, sin ficheros de cola.
            </p>

            <div class="mt-10 flex flex-wrap items-center gap-3">
                <a href="{{ route('jobs.create') }}" class="btn-primary">
                    → enviar secuencia
                </a>
                <a href="{{ route('proteins.index') }}" class="btn-secondary">
                    explorar catálogo
                </a>
            </div>

            {{-- Línea tipo paper --}}
            <div class="mt-10 border-t border-ink-300 pt-5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">
                <div>cátedra camelia · medicina personalizada</div>
                <div class="mt-1">impacthon 2026 · galicia, españa · acceso abierto</div>
            </div>
        </div>

        {{-- Derecha: lectura del instrumento --}}
        <div class="lg:col-span-5">
            <div class="crosshair panel p-6">
                <div class="mb-5 flex items-center justify-between">
                    <span class="label-tag">en vivo · clúster ft3</span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">
                        {{ now()->format('Y-m-d H:i:s') }}
                    </span>
                </div>

                <img src="{{ asset('logo/LogoMol.svg') }}" alt="" class="mx-auto h-40 w-auto opacity-90">

                <dl class="mt-6 space-y-0">
                    <div class="field">
                        <dt>trabajos en cola</dt>
                        <dd class="text-signal-mint">3 trabajos</dd>
                    </div>
                    <div class="field">
                        <dt>uso de gpu</dt>
                        <dd>74<span class="text-ink-500">%</span></dd>
                    </div>
                    <div class="field">
                        <dt>tiempo medio</dt>
                        <dd>00:04:12</dd>
                    </div>
                    <div class="field">
                        <dt>modelo</dt>
                        <dd>alphafold-2.3.1 · monómero</dd>
                    </div>
                    <div class="field">
                        <dt>último completado</dt>
                        <dd>{{ now()->subMinutes(7)->format('H:i:s') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</section>

{{-- ───────────────────────── MÉTRICAS ───────────────────────── --}}
@if($stats)
<section class="border-b border-ink-300 panel-flush">
    <div class="mx-auto grid max-w-[1400px] grid-cols-2 divide-x divide-ink-300 sm:grid-cols-4">
        <div class="px-6 py-6 sm:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">proteínas indexadas</div>
            <div class="mt-2 font-mono text-3xl text-ink-900 tabular-nums">{{ $stats['total_proteins'] ?? '—' }}</div>
        </div>
        <div class="px-6 py-6 sm:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">entradas curadas</div>
            <div class="mt-2 font-mono text-3xl text-ink-900 tabular-nums">{{ $stats['embedded_proteins'] ?? '—' }}</div>
        </div>
        <div class="px-6 py-6 sm:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">rango de longitud <span class="lowercase">/ aa</span></div>
            <div class="mt-2 font-mono text-3xl text-ink-900 tabular-nums">{{ $stats['min_length'] ?? '—' }}<span class="text-ink-400">–</span>{{ $stats['max_length'] ?? '—' }}</div>
        </div>
        <div class="px-6 py-6 sm:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">familias funcionales</div>
            <div class="mt-2 font-mono text-3xl text-ink-900 tabular-nums">{{ count($stats['by_category'] ?? []) }}</div>
        </div>
    </div>
</section>
@endif

{{-- ───────────────────────── PROCEDIMIENTO ───────────────────────── --}}
<section class="border-b border-ink-300">
    <div class="mx-auto grid max-w-[1400px] gap-10 px-4 py-16 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8">
        <div class="lg:col-span-3">
            <div class="label-tag">sección 02 · métodos</div>
            <h2 class="mt-3 font-serif text-3xl text-ink-900">Procedimiento</h2>
            <p class="mt-4 font-serif text-sm leading-relaxed text-ink-600">
                Tres etapas deterministas, todas transparentes: los datos entran, la
                estructura sale. El estado del pipeline es visible en todo momento.
            </p>
        </div>

        <div class="lg:col-span-9">
            <ol class="divide-y divide-ink-300 border-y border-ink-300">
                <li class="grid gap-6 py-6 md:grid-cols-12">
                    <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint md:col-span-2">
                        § 2.1 — entrada
                    </div>
                    <div class="md:col-span-10">
                        <h3 class="font-serif text-lg text-ink-900">Recepción de la secuencia</h3>
                        <p class="mt-1 font-serif text-sm leading-relaxed text-ink-600">
                            Pega una secuencia proteica monocadena en formato FASTA. La cabecera
                            se preserva como metadato del trabajo. La longitud se valida contra la
                            ventana del modelo (máx. 2 048 residuos).
                        </p>
                    </div>
                </li>

                <li class="grid gap-6 py-6 md:grid-cols-12">
                    <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint md:col-span-2">
                        § 2.2 — inferencia
                    </div>
                    <div class="md:col-span-10">
                        <h3 class="font-serif text-lg text-ink-900">AlphaFold2 sobre FT3</h3>
                        <p class="mt-1 font-serif text-sm leading-relaxed text-ink-600">
                            El trabajo se despacha a la partición GPU de Finis Terrae III.
                            Sondeamos el clúster cada tres segundos y transmitimos las transiciones
                            de estado (<span class="font-mono text-ink-800">PENDING → RUNNING → POSTPROCESS</span>) a la interfaz.
                        </p>
                    </div>
                </li>

                <li class="grid gap-6 py-6 md:grid-cols-12">
                    <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint md:col-span-2">
                        § 2.3 — salida
                    </div>
                    <div class="md:col-span-10">
                        <h3 class="font-serif text-lg text-ink-900">Estructura anotada</h3>
                        <p class="mt-1 font-serif text-sm leading-relaxed text-ink-600">
                            Recibes un modelo 3D coloreado por pLDDT por residuo, la matriz
                            completa de error alineado predicho, el desglose de estructura
                            secundaria, la facturación de HPC y los ficheros PDB / mmCIF.
                        </p>
                    </div>
                </li>
            </ol>
        </div>
    </div>
</section>

{{-- ───────────────────────── TABLA DE MUESTRA ───────────────────────── --}}
@if(!empty($samples))
<section class="border-b border-ink-300">
    <div class="mx-auto max-w-[1400px] px-4 py-16 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-end justify-between gap-6">
            <div>
                <div class="label-tag">sección 03 · conjunto de referencia</div>
                <h2 class="mt-3 font-serif text-3xl text-ink-900">Pruébalo con una proteína conocida</h2>
                <p class="mt-2 max-w-xl font-serif text-sm text-ink-600">
                    Entradas curadas con referencias cruzadas verificadas a UniProt y PDB.
                    Pulsa cualquier fila para enviar un trabajo de predicción usando su secuencia.
                </p>
            </div>
            <a href="{{ route('proteins.index') }}" class="hidden btn-secondary lg:inline-flex">
                catálogo completo →
            </a>
        </div>

        <div class="panel overflow-hidden">
            <table class="w-full font-mono text-xs">
                <thead class="border-b border-ink-300 bg-ink-150/80 text-[10px] uppercase tracking-[0.14em] text-ink-500">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">id</th>
                        <th class="px-4 py-3 text-left font-medium">proteína</th>
                        <th class="px-4 py-3 text-left font-medium">organismo</th>
                        <th class="px-4 py-3 text-left font-medium">familia</th>
                        <th class="px-4 py-3 text-right font-medium">longitud / aa</th>
                        <th class="px-4 py-3 text-right font-medium">acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-300">
                    @foreach(array_slice($samples, 0, 8) as $i => $sample)
                        <tr class="group transition-colors hover:bg-ink-150/60">
                            <td class="px-4 py-3 text-ink-400">{{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('jobs.create', ['fasta' => $sample['fasta_ready'] ?? $sample['fasta_sequence'] ?? '', 'filename' => strtolower(str_replace(' ', '_', $sample['protein_name'] ?? 'protein')) . '.fasta']) }}"
                                   class="text-ink-900 group-hover:text-signal-mint">
                                    {{ $sample['protein_name'] ?? $sample['protein_id'] }}
                                </a>
                            </td>
                            <td class="px-4 py-3 italic text-ink-600">{{ $sample['organism'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if(!empty($sample['category']))
                                    <x-category-badge :category="$sample['category']" />
                                @else
                                    <span class="text-ink-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink-800">{{ $sample['length'] ?? '?' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('jobs.create', ['fasta' => $sample['fasta_ready'] ?? $sample['fasta_sequence'] ?? '', 'filename' => strtolower(str_replace(' ', '_', $sample['protein_name'] ?? 'protein')) . '.fasta']) }}"
                                   class="text-signal-mint opacity-0 group-hover:opacity-100">
                                    predecir →
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

{{-- ───────────────────────── GLOSARIO ───────────────────────── --}}
<section>
    <div class="mx-auto grid max-w-[1400px] gap-10 px-4 py-16 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8">
        <div class="lg:col-span-3">
            <div class="label-tag">apéndice a · glosario</div>
            <h2 class="mt-3 font-serif text-3xl text-ink-900">Notas al margen</h2>
            <p class="mt-4 font-serif text-sm leading-relaxed text-ink-600">
                Definiciones rápidas para quien no es bioinformático. Citadas en el
                panel de resultados donde aparecen los términos por primera vez.
            </p>
        </div>

        <dl class="lg:col-span-9 grid gap-px bg-ink-300 sm:grid-cols-3">
            <div class="bg-ink-100 p-6">
                <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint">
                    <sup class="mr-1">[1]</sup> fasta
                </dt>
                <dd class="mt-3 font-serif text-sm leading-relaxed text-ink-800">
                    Formato de texto plano para secuencias biológicas. La cabecera empieza por
                    <span class="font-mono text-ink-900">&gt;</span>, seguida de la cadena de
                    aminoácidos en código de una sola letra (M, Q, I, F, V, K…).
                </dd>
            </div>

            <div class="bg-ink-100 p-6">
                <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint">
                    <sup class="mr-1">[2]</sup> pLDDT
                </dt>
                <dd class="mt-3 font-serif text-sm leading-relaxed text-ink-800">
                    Predicted Local Distance Difference Test. Puntuación de confianza
                    por residuo en [0, 100]. Valores por encima de 90 son muy fiables;
                    por debajo de 50 suelen indicar regiones desordenadas.
                </dd>
            </div>

            <div class="bg-ink-100 p-6">
                <dt class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-mint">
                    <sup class="mr-1">[3]</sup> alphafold2
                </dt>
                <dd class="mt-3 font-serif text-sm leading-relaxed text-ink-800">
                    Sistema de aprendizaje profundo de DeepMind que predice estructuras
                    proteicas en 3D a partir de la secuencia con precisión casi
                    experimental. Ganó el CASP14 en 2020.
                </dd>
            </div>
        </dl>
    </div>
</section>

@endsection
