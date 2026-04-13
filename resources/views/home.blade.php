@extends('layouts.app')

@section('title', 'Proteinux')

@section('content')

{{-- ───────────────────────── HERO ───────────────────────── --}}
<section class="relative border-b border-ink-300">
    <div class="mx-auto grid max-w-[1400px] gap-10 px-4 py-12 sm:gap-12 sm:px-6 sm:py-16 lg:grid-cols-12 lg:gap-16 lg:px-8 lg:py-24">

        {{-- Izquierda: bloque de título --}}
        <div class="lg:col-span-7">
            <div class="label-tag mb-5 sm:mb-6">
                <span>vol. 01 · nota 001</span>
            </div>

            <h1 class="font-serif text-[2rem] leading-[1.08] text-ink-900 sm:text-5xl lg:text-6xl">
                Predecimos la<br>
                forma tridimensional<br>
                de <span class="italic text-signal-mint">cualquier</span> proteína.
            </h1>

            <p class="mt-6 max-w-xl font-serif text-base leading-relaxed text-ink-700 sm:mt-8 sm:text-lg">
                Una interfaz web para inferencia con AlphaFold&thinsp;2 sobre el supercomputador
                <span class="text-ink-900">CESGA Finis Terrae&nbsp;III</span>.
                Envía una secuencia FASTA y recibe una estructura anotada con
                confianza por residuo, error alineado predicho y contexto biológico.
                Sin terminal, sin SLURM, sin ficheros de cola.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:mt-10 sm:flex-row sm:flex-wrap sm:items-center">
                <a href="{{ route('jobs.create') }}" class="btn-primary w-full justify-center sm:w-auto">
                    → enviar secuencia
                </a>
                <a href="{{ route('proteins.index') }}" class="btn-secondary w-full justify-center sm:w-auto">
                    explorar catálogo
                </a>
            </div>

            {{-- Línea tipo paper --}}
            <div class="mt-8 border-t border-ink-300 pt-5 font-mono text-[10px] uppercase leading-relaxed tracking-[0.14em] text-ink-500 sm:mt-10">
                <div>cátedra camelia · medicina personalizada</div>
                <div class="mt-1"><a href="https://impacthon-web.vercel.app" target="_blank" rel="noopener" class="transition-colors hover:text-ink-800">impacthon 2026</a> · galicia, españa · acceso abierto</div>
            </div>
        </div>

        {{-- Derecha: lectura del instrumento --}}
        <div class="lg:col-span-5">
            <div class="crosshair panel p-5 sm:p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
                    <span class="label-tag">en vivo · clúster ft3</span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">
                        {{ now()->format('Y-m-d H:i:s') }}
                    </span>
                </div>

                <img src="{{ asset('logo/LogoMol.svg') }}" alt="" class="mx-auto h-32 w-auto opacity-90 sm:h-40">

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
    <div class="mx-auto grid max-w-[1400px] grid-cols-2 divide-x divide-y divide-ink-300 sm:grid-cols-4 sm:divide-y-0">
        <div class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">proteínas indexadas</div>
            <div class="mt-2 font-mono text-2xl text-ink-900 tabular-nums sm:text-3xl">{{ $stats['total_proteins'] ?? '—' }}</div>
        </div>
        <div class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">entradas curadas</div>
            <div class="mt-2 font-mono text-2xl text-ink-900 tabular-nums sm:text-3xl">{{ $stats['embedded_proteins'] ?? '—' }}</div>
        </div>
        <div class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">rango <span class="lowercase">/ aa</span></div>
            <div class="mt-2 font-mono text-2xl text-ink-900 tabular-nums sm:text-3xl">{{ $stats['min_length'] ?? '—' }}<span class="text-ink-400">–</span>{{ $stats['max_length'] ?? '—' }}</div>
        </div>
        <div class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">familias</div>
            <div class="mt-2 font-mono text-2xl text-ink-900 tabular-nums sm:text-3xl">{{ count($stats['by_category'] ?? []) }}</div>
        </div>
    </div>
</section>
@endif

{{-- ───────────────────────── PROCEDIMIENTO ───────────────────────── --}}
<section class="border-b border-ink-300">
    <div class="mx-auto grid max-w-[1400px] gap-8 px-4 py-12 sm:gap-10 sm:px-6 sm:py-16 lg:grid-cols-12 lg:gap-16 lg:px-8">
        <div class="lg:col-span-3">
            <div class="label-tag">sección 02 · métodos</div>
            <h2 class="mt-3 font-serif text-2xl text-ink-900 sm:text-3xl">Procedimiento</h2>
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

{{-- ───────────────────────── ACTIVIDAD RECIENTE ───────────────────────── --}}
@if($recentThreads->isNotEmpty() || $recentPredictions->isNotEmpty())
<section class="border-b border-ink-300">
    <div class="mx-auto max-w-[1400px] px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-3">
                <div class="label-tag">sección 03 · comunidad</div>
                <h2 class="mt-3 font-serif text-2xl text-ink-900 sm:text-3xl">Actividad reciente</h2>
                <p class="mt-4 font-serif text-sm leading-relaxed text-ink-600">
                    Últimas discusiones y predicciones completadas por la comunidad.
                    La plataforma es colaborativa y federada.
                </p>
            </div>

            <div class="grid gap-8 sm:grid-cols-2 lg:col-span-9">
                {{-- Últimos hilos --}}
                @if($recentThreads->isNotEmpty())
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <span class="label-tag">discusiones</span>
                        <a href="{{ route('forum.index') }}" class="font-mono text-[10px] uppercase tracking-wider text-signal-mint transition-colors hover:text-signal-mint-deep">
                            ver todas &rarr;
                        </a>
                    </div>
                    <div class="space-y-3">
                        @foreach($recentThreads as $thread)
                            <a href="{{ route('forum.show', $thread) }}"
                               class="block border border-ink-300 bg-ink-100/60 p-4 transition-colors hover:border-signal-mint/60 hover:bg-ink-100">
                                <h3 class="font-serif text-sm leading-tight text-ink-900 sm:text-base">{{ $thread->title }}</h3>
                                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <span class="font-mono text-[10px] text-ink-600">{{ $thread->authorDisplayName() }}</span>
                                    <span class="font-mono text-[10px] tabular-nums text-ink-400">{{ $thread->posts_count }} resp.</span>
                                    @if($thread->last_activity_at)
                                        <span class="font-mono text-[10px] text-ink-400">{{ $thread->last_activity_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                                @if($thread->predictedJob)
                                    <span class="mt-2 inline-flex items-center gap-1 border border-signal-mint/40 bg-signal-mint/5 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-signal-mint-deep">
                                        <span class="status-dot bg-signal-mint"></span>
                                        {{ $thread->predictedJob->displayName() }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Últimas predicciones --}}
                @if($recentPredictions->isNotEmpty())
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <span class="label-tag">predicciones</span>
                        <a href="{{ route('library.index') }}" class="font-mono text-[10px] uppercase tracking-wider text-signal-mint transition-colors hover:text-signal-mint-deep">
                            ver todas &rarr;
                        </a>
                    </div>
                    <div class="space-y-3">
                        @foreach($recentPredictions as $prediction)
                            @php
                                $score = $prediction->plddt_mean;
                                if ($score === null) {
                                    $badgeCls = 'border-ink-300 bg-ink-100/60 text-ink-500';
                                    $badgeLabel = 'en proceso';
                                } elseif ($score >= 90) {
                                    $badgeCls = 'border-[#0053D6]/60 bg-[#0053D6]/10 text-[#0053D6]';
                                    $badgeLabel = 'muy alta';
                                } elseif ($score >= 70) {
                                    $badgeCls = 'border-sky-500/60 bg-sky-50 text-sky-700';
                                    $badgeLabel = 'alta';
                                } elseif ($score >= 50) {
                                    $badgeCls = 'border-amber-500/60 bg-amber-50 text-amber-700';
                                    $badgeLabel = 'media';
                                } else {
                                    $badgeCls = 'border-orange-500/60 bg-orange-50 text-orange-700';
                                    $badgeLabel = 'baja';
                                }
                            @endphp
                            <a href="{{ route('jobs.show', $prediction->job_id) }}"
                               class="block border border-ink-300 bg-ink-100/60 p-4 transition-colors hover:border-signal-mint/60 hover:bg-ink-100">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-serif text-sm leading-tight text-ink-900 sm:text-base">{{ $prediction->displayName() }}</h3>
                                        @if($prediction->organism)
                                            <p class="mt-1 font-serif text-[12px] italic text-ink-600">{{ $prediction->organism }}</p>
                                        @endif
                                    </div>
                                    @if($score !== null)
                                        <span class="inline-flex shrink-0 items-center gap-1 border px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-wider {{ $badgeCls }}">
                                            {{ number_format($score, 1) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-2 font-mono text-[10px] tabular-nums text-ink-400">
                                    {{ $prediction->completed_at?->format('Y-m-d') }}
                                    @if($prediction->sequence_length)
                                        &middot; {{ $prediction->sequence_length }} aa
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- ───────────────────────── TABLA DE MUESTRA ───────────────────────── --}}
@if(!empty($samples))
<section class="border-b border-ink-300">
    <div class="mx-auto max-w-[1400px] px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="mb-8 flex flex-col items-start gap-4 sm:flex-row sm:items-end sm:justify-between sm:gap-6">
            <div>
                <div class="label-tag">sección 04 · conjunto de referencia</div>
                <h2 class="mt-3 font-serif text-2xl text-ink-900 sm:text-3xl">Pruébalo con una proteína conocida</h2>
                <p class="mt-2 max-w-xl font-serif text-sm text-ink-600">
                    Entradas curadas con referencias cruzadas verificadas a UniProt y PDB.
                    Pulsa cualquier fila para enviar un trabajo de predicción usando su secuencia.
                </p>
            </div>
            <a href="{{ route('proteins.index') }}" class="btn-secondary hidden lg:inline-flex">
                catálogo completo →
            </a>
        </div>

        {{-- Vista en tarjetas · móvil --}}
        <div class="space-y-3 md:hidden">
            @foreach(array_slice($samples, 0, 8) as $i => $sample)
                <a href="{{ route('jobs.create', ['fasta' => $sample['fasta_ready'] ?? $sample['fasta_sequence'] ?? '', 'filename' => strtolower(str_replace(' ', '_', $sample['protein_name'] ?? 'protein')) . '.fasta']) }}"
                   class="block border border-ink-300 bg-ink-100/60 p-4 transition-colors hover:border-signal-mint/60 hover:bg-ink-100">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="font-mono text-[10px] uppercase tracking-wider text-ink-400">{{ str_pad($i + 1, 3, '0', STR_PAD_LEFT) }}</div>
                            <div class="mt-1 font-serif text-base text-ink-900">{{ $sample['protein_name'] ?? $sample['protein_id'] }}</div>
                            <div class="mt-1 font-serif text-xs italic text-ink-600">{{ $sample['organism'] ?? '—' }}</div>
                        </div>
                        <div class="shrink-0 text-right">
                            <div class="font-mono text-xs tabular-nums text-ink-800">{{ $sample['length'] ?? '?' }} <span class="text-ink-400">aa</span></div>
                            @if(!empty($sample['category']))
                                <div class="mt-2"><x-category-badge :category="$sample['category']" /></div>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 border-t border-dashed border-ink-300 pt-3 font-mono text-[10px] uppercase tracking-wider text-signal-mint">
                        predecir →
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Vista en tabla · tablet+ --}}
        <div class="panel hidden overflow-x-auto md:block">
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

        <div class="mt-6 md:hidden">
            <a href="{{ route('proteins.index') }}" class="btn-secondary w-full justify-center">
                catálogo completo →
            </a>
        </div>
    </div>
</section>
@endif

{{-- ───────────────────────── GLOSARIO ───────────────────────── --}}
<section>
    <div class="mx-auto grid max-w-[1400px] gap-8 px-4 py-12 sm:gap-10 sm:px-6 sm:py-16 lg:grid-cols-12 lg:gap-16 lg:px-8">
        <div class="lg:col-span-3">
            <div class="label-tag">apéndice a · glosario</div>
            <h2 class="mt-3 font-serif text-2xl text-ink-900 sm:text-3xl">Notas al margen</h2>
            <p class="mt-4 font-serif text-sm leading-relaxed text-ink-600">
                Definiciones rápidas para quien no es bioinformático. Citadas en el
                panel de resultados donde aparecen los términos por primera vez.
            </p>
        </div>

        <dl class="grid gap-px bg-ink-300 sm:grid-cols-3 lg:col-span-9">
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
