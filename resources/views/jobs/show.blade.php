@extends('layouts.app')

@section('title', 'Trabajo ' . $jobId)

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 sm:py-10 lg:px-8">

    {{-- Cabecera del trabajo --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-x-3 gap-y-2 border-b border-ink-300 pb-4 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500 sm:mb-8 sm:text-[11px]">
        <div class="flex min-w-0 flex-wrap items-center gap-x-3 gap-y-1">
            <span class="text-ink-400">trabajo</span>
            <span class="truncate text-ink-900">{{ $jobId }}</span>
            <span class="hidden text-ink-400 sm:inline">|</span>
            <span class="hidden sm:inline">pipeline <span class="text-ink-800">af2-proteinux/2.3.1</span></span>
        </div>
        <a href="{{ route('jobs.create') }}" class="text-signal-mint hover:underline">→ nuevo envío</a>
    </div>

    {{-- ─────────── PENDIENTE / EN EJECUCIÓN ─────────── --}}
    <div id="progress-section" class="{{ $outputs ? 'hidden' : '' }}">
        <div class="mx-auto max-w-2xl py-8 sm:py-12">
            <div class="label-tag mb-4">en vivo · clúster ft3</div>

            <h1 class="font-serif text-3xl text-ink-900 sm:text-4xl" id="progress-title">Procesando tu secuencia…</h1>
            <p class="mt-3 max-w-xl font-serif text-base leading-relaxed text-ink-600" id="progress-subtitle">
                Tu trabajo se está preparando para el supercomputador CESGA Finis Terrae&nbsp;III.
            </p>

            <div class="mt-6">
                <x-loading-spinner size="lg" />
            </div>

            {{-- Trazado del pipeline --}}
            <ol class="mt-8 border border-ink-300 bg-ink-100/60" id="progress-steps">
                <li class="flex items-center justify-between gap-3 border-b border-ink-300 px-4 py-4 transition-colors sm:gap-4 sm:px-5" data-step="PENDING">
                    <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-signal-mint">§ 01</span>
                        <div class="h-2 w-2 shrink-0 animate-pulse rounded-full bg-amber-500" id="step-pending-dot"></div>
                        <span class="truncate font-mono text-[11px] uppercase tracking-wider text-ink-800 sm:text-xs">en cola · esperando gpu</span>
                    </div>
                    <span class="shrink-0 font-mono text-[10px] uppercase tracking-wider text-ink-400">01/03</span>
                </li>
                <li class="flex items-center justify-between gap-3 border-b border-ink-300 px-4 py-4 opacity-40 transition-opacity sm:gap-4 sm:px-5" data-step="RUNNING">
                    <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">§ 02</span>
                        <div class="h-2 w-2 shrink-0 rounded-full bg-ink-400" id="step-running-dot"></div>
                        <span class="truncate font-mono text-[11px] uppercase tracking-wider text-ink-600 sm:text-xs">ejecutando alphafold2</span>
                    </div>
                    <span class="shrink-0 font-mono text-[10px] uppercase tracking-wider text-ink-400">02/03</span>
                </li>
                <li class="flex items-center justify-between gap-3 px-4 py-4 opacity-40 transition-opacity sm:gap-4 sm:px-5" data-step="POSTPROCESS">
                    <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">§ 03</span>
                        <div class="h-2 w-2 shrink-0 rounded-full bg-ink-400" id="step-post-dot"></div>
                        <span class="truncate font-mono text-[11px] uppercase tracking-wider text-ink-600 sm:text-xs">generando ficheros</span>
                    </div>
                    <span class="shrink-0 font-mono text-[10px] uppercase tracking-wider text-ink-400">03/03</span>
                </li>
            </ol>

            {{-- Marginalia --}}
            <aside class="mt-6 border-l-2 border-signal-mint/50 bg-ink-100/50 px-5 py-4">
                <div class="label-tag mb-2">marginalia</div>
                <p class="font-serif text-sm leading-relaxed text-ink-800" id="fun-fact">
                    Las proteínas son máquinas moleculares: digieren los alimentos, mueven los músculos, defienden contra infecciones y copian el ADN.
                </p>
            </aside>
        </div>
    </div>

    {{-- ─────────── COMPLETADO ─────────── --}}
    <div id="results-section" class="{{ $outputs ? '' : 'hidden' }}">

        {{-- Bloque de título del resultado --}}
        <header class="mb-6 grid items-end gap-4 border-b border-ink-300 pb-5 sm:mb-8 sm:pb-6 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="label-tag">resultado · predicción de estructura</div>
                <h1 class="mt-3 font-serif text-2xl leading-tight text-ink-900 sm:text-3xl lg:text-4xl" id="result-title">
                    @if($outputs && ($outputs['protein_metadata'] ?? null))
                        {{ $outputs['protein_metadata']['protein_name'] }}
                    @else
                        Resultado de predicción
                    @endif
                </h1>
                <p class="mt-2 font-serif text-sm text-ink-600 sm:text-base" id="result-subtitle">
                    @if($outputs && ($outputs['protein_metadata'] ?? null))
                        <span class="italic">{{ $outputs['protein_metadata']['organism'] ?? '' }}</span>
                        @if($outputs['protein_metadata']['uniprot_id'] ?? null)
                            &middot; UniProt: <a href="https://www.uniprot.org/uniprot/{{ $outputs['protein_metadata']['uniprot_id'] }}" target="_blank" class="font-mono text-signal-mint hover:underline">{{ $outputs['protein_metadata']['uniprot_id'] }}</a>
                        @endif
                        @if($outputs['protein_metadata']['pdb_id'] ?? null)
                            &middot; PDB: <a href="https://www.rcsb.org/structure/{{ $outputs['protein_metadata']['pdb_id'] }}" target="_blank" class="font-mono text-signal-mint hover:underline">{{ $outputs['protein_metadata']['pdb_id'] }}</a>
                        @endif
                    @else
                        Trabajo {{ $jobId }}
                    @endif
                </p>
            </div>
            <div class="lg:col-span-3 lg:text-right" id="quality-header">
                @if($outputs)
                    <x-confidence-badge :score="$outputs['structural_data']['confidence']['plddt_mean'] ?? 0" />
                @endif
            </div>
        </header>

        {{-- Cuadrícula principal --}}
        <div class="grid gap-5 sm:gap-6 lg:grid-cols-12">

            {{-- Visor 3D + gráficas --}}
            <div class="space-y-5 sm:space-y-6 lg:col-span-8">

                {{-- Visor 3D --}}
                <figure class="panel">
                    <figcaption class="flex flex-col gap-2 border-b border-ink-300 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <span class="label-tag">fig. 1 · estructura 3d</span>
                        <div class="flex flex-wrap items-center gap-1" id="viewer-controls">
                            <button onclick="setViewerStyle('cartoon')" id="btn-cartoon"
                                    class="border border-signal-mint/40 bg-signal-mint/15 px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-signal-mint-deep">cartoon</button>
                            <button onclick="setViewerStyle('stick')" id="btn-stick"
                                    class="border border-transparent px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 hover:border-ink-400 hover:text-ink-800">varilla</button>
                            <button onclick="setViewerStyle('sphere')" id="btn-sphere"
                                    class="border border-transparent px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 hover:border-ink-400 hover:text-ink-800">esfera</button>
                            <button onclick="setViewerStyle('surface')" id="btn-surface"
                                    class="border border-transparent px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 hover:border-ink-400 hover:text-ink-800">superficie</button>
                            <span class="mx-1 hidden text-ink-300 sm:inline">|</span>
                            <button onclick="toggleSpin()" id="btn-spin"
                                    class="border border-transparent px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 hover:border-ink-400 hover:text-ink-800">girar</button>
                            <button onclick="resetViewer()"
                                    class="border border-transparent px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 hover:border-ink-400 hover:text-ink-800">reset</button>
                        </div>
                    </figcaption>
                    <div id="viewer-container" class="relative h-[320px] w-full bg-white sm:h-[440px] lg:h-[520px]"></div>
                    <div class="flex flex-col gap-1 border-t border-ink-300 px-4 py-2 font-mono text-[10px] uppercase tracking-wider text-ink-500 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:px-5">
                        <span id="viewer-source" class="truncate">procedencia · cargando…</span>
                        <span id="viewer-hover" class="tabular-nums text-ink-700">&nbsp;</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 border-t border-ink-300 px-4 py-3 font-mono text-[10px] uppercase tracking-wider text-ink-500 sm:grid-cols-4 sm:gap-4 sm:px-5">
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 shrink-0" style="background:#0053D6"></span>muy alta &gt;90</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 shrink-0" style="background:#65CBF3"></span>alta 70–90</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 shrink-0" style="background:#FFDB13"></span>media 50–70</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 shrink-0" style="background:#FF7D45"></span>baja &lt;50</span>
                    </div>
                </figure>

                {{-- Gráfica pLDDT --}}
                <figure class="panel p-4 sm:p-5">
                    <figcaption class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <span class="label-tag">fig. 2 · pLDDT por residuo <sup class="text-signal-mint">[2]</sup></span>
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">x · residuo · y · puntuación</span>
                    </figcaption>
                    <canvas id="plddt-chart" class="w-full" height="120"></canvas>
                </figure>

                {{-- Secuencia interactiva --}}
                <figure class="panel p-4 sm:p-5">
                    <figcaption class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <span class="label-tag">fig. 2b · secuencia interactiva</span>
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">hover · inspeccionar · click · centrar visor</span>
                    </figcaption>
                    <div class="relative">
                        <canvas id="sequence-track" class="w-full cursor-crosshair" height="44"></canvas>
                        <canvas id="hydro-track" class="mt-1 w-full" height="12"></canvas>
                        <div id="sequence-tooltip" class="pointer-events-none fixed z-50 hidden border border-ink-400 bg-ink-50 px-2 py-1 font-mono text-[10px] text-ink-900 shadow-lg"></div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-3 font-mono text-[10px] uppercase tracking-wider text-ink-500 sm:gap-4">
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2 w-2" style="background:#134e4a"></span>hidrofílico</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2 w-2" style="background:#b45309"></span>hidrofóbico</span>
                        <span class="text-ink-400">· kyte-doolittle</span>
                    </div>
                </figure>

                {{-- Mapa PAE --}}
                <figure class="panel p-4 sm:p-5">
                    <figcaption class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <span class="label-tag">fig. 3 · error alineado predicho</span>
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">unidad · ångström</span>
                    </figcaption>
                    <div class="flex items-start gap-3 sm:gap-5">
                        <div class="relative flex-1 overflow-hidden">
                            <canvas id="pae-heatmap" class="w-full"></canvas>
                            <div id="pae-tooltip" class="pointer-events-none absolute hidden border border-ink-400 bg-ink-50 px-2 py-1 font-mono text-[10px] text-ink-900 shadow-lg"></div>
                        </div>
                        <div class="flex shrink-0 flex-col items-center gap-1 font-mono text-[10px] uppercase tracking-wider text-ink-400">
                            <span>0 Å</span>
                            <div class="h-24 w-3 border border-ink-300 sm:h-32" style="background: linear-gradient(to bottom, #0d4a3e, #14b8a6, #fbbf24, #ffffff)"></div>
                            <span id="pae-max-label">30 Å</span>
                        </div>
                    </div>
                </figure>

                {{-- Mapa de contactos --}}
                <figure class="panel p-4 sm:p-5">
                    <figcaption class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <span class="label-tag">fig. 3b · mapa de contactos</span>
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">cα–cα &lt; 8 Å</span>
                    </figcaption>
                    <div class="relative mx-auto" style="max-width:440px">
                        <canvas id="contact-map" class="w-full"></canvas>
                        <div id="contact-map-tooltip" class="pointer-events-none fixed z-50 hidden border border-ink-400 bg-ink-50 px-2 py-1 font-mono text-[10px] text-ink-900 shadow-lg"></div>
                    </div>
                    <div id="contact-map-stats" class="mt-3 text-center font-mono text-[10px] uppercase tracking-wider text-ink-500"></div>
                </figure>
            </div>

            {{-- Barra lateral --}}
            <aside class="space-y-5 sm:space-y-6 lg:col-span-4">

                {{-- Resumen de confianza --}}
                <section class="panel p-4 sm:p-5">
                    <div class="label-tag mb-4">tab. 1 · resumen de confianza</div>
                    <div id="quality-score-block" class="mb-4 hidden"></div>
                    <dl class="space-y-0" id="confidence-summary">
                        <div class="field">
                            <dt>pLDDT promedio</dt>
                            <dd id="plddt-mean">—</dd>
                        </div>
                        <div class="field">
                            <dt>PAE medio</dt>
                            <dd id="mean-pae">—</dd>
                        </div>
                    </dl>
                    <div class="mt-4 space-y-2" id="plddt-histogram"></div>
                </section>

                {{-- Datos biológicos --}}
                <section class="panel p-4 sm:p-5">
                    <div class="label-tag mb-4">tab. 2 · propiedades biológicas</div>
                    <div class="space-y-2" id="bio-data">
                        <p class="font-mono text-xs text-ink-400">cargando…</p>
                    </div>
                </section>

                {{-- Regiones a inspeccionar --}}
                <section class="panel p-4 sm:p-5">
                    <div class="label-tag mb-4">tab. 5 · regiones a inspeccionar</div>
                    <ul id="low-confidence-regions" class="space-y-2 font-mono text-[11px] text-ink-700">
                        <li class="text-ink-400">cargando…</li>
                    </ul>
                </section>

                {{-- Estructura secundaria --}}
                <section class="panel p-4 sm:p-5">
                    <div class="label-tag mb-4">fig. 4 · estructura secundaria</div>
                    <canvas id="secondary-structure-chart" class="mx-auto max-w-full" width="160" height="160"></canvas>
                    <div class="mt-3 flex flex-wrap justify-center gap-3 font-mono text-[10px] uppercase tracking-wider text-ink-600 sm:gap-4" id="ss-legend"></div>
                </section>

                {{-- Facturación HPC --}}
                <section class="panel p-4 sm:p-5">
                    <div class="label-tag mb-4">tab. 3 · recursos hpc</div>
                    <div class="space-y-2" id="accounting-data">
                        <p class="font-mono text-xs text-ink-400">cargando…</p>
                    </div>
                </section>

                {{-- Referencias cruzadas --}}
                @php
                    $xrefMeta = $outputs['protein_metadata'] ?? [];
                    $xrefUniprot = $xrefMeta['uniprot_id'] ?? null;
                    $xrefPdb = $xrefMeta['pdb_id'] ?? null;
                @endphp
                <section class="panel p-4 sm:p-5">
                    <div class="label-tag mb-4">tab. 4 · referencias cruzadas</div>
                    @if($xrefUniprot || $xrefPdb)
                        <ul class="space-y-2 font-mono text-[11px] text-ink-700">
                            @if($xrefUniprot)
                                <li>
                                    <a href="https://www.uniprot.org/uniprotkb/{{ $xrefUniprot }}" target="_blank" rel="noopener"
                                       class="flex items-center justify-between gap-2 border-b border-dashed border-ink-300 pb-1 hover:text-signal-mint">
                                        <span class="text-ink-500">UniProt</span>
                                        <span class="tabular-nums">{{ $xrefUniprot }} ↗</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://alphafold.ebi.ac.uk/entry/{{ $xrefUniprot }}" target="_blank" rel="noopener"
                                       class="flex items-center justify-between gap-2 border-b border-dashed border-ink-300 pb-1 hover:text-signal-mint">
                                        <span class="text-ink-500">AlphaFold DB</span>
                                        <span>entrada ↗</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://www.ebi.ac.uk/interpro/protein/UniProt/{{ $xrefUniprot }}" target="_blank" rel="noopener"
                                       class="flex items-center justify-between gap-2 border-b border-dashed border-ink-300 pb-1 hover:text-signal-mint">
                                        <span class="text-ink-500">InterPro</span>
                                        <span>dominios ↗</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://www.ebi.ac.uk/interpro/entry/pfam/search/?q={{ $xrefUniprot }}" target="_blank" rel="noopener"
                                       class="flex items-center justify-between gap-2 border-b border-dashed border-ink-300 pb-1 hover:text-signal-mint">
                                        <span class="text-ink-500">Pfam</span>
                                        <span>búsqueda ↗</span>
                                    </a>
                                </li>
                            @endif
                            @if($xrefPdb)
                                <li>
                                    <a href="https://www.rcsb.org/structure/{{ $xrefPdb }}" target="_blank" rel="noopener"
                                       class="flex items-center justify-between gap-2 border-b border-dashed border-ink-300 pb-1 hover:text-signal-mint">
                                        <span class="text-ink-500">RCSB PDB</span>
                                        <span class="tabular-nums">{{ $xrefPdb }} ↗</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://www.ebi.ac.uk/pdbe/entry/pdb/{{ $xrefPdb }}" target="_blank" rel="noopener"
                                       class="flex items-center justify-between gap-2 hover:text-signal-mint">
                                        <span class="text-ink-500">PDBe</span>
                                        <span class="tabular-nums">{{ $xrefPdb }} ↗</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    @else
                        <p class="font-mono text-[11px] text-ink-400">
                            sin identificadores externos · proteína custom
                        </p>
                    @endif
                </section>

                {{-- Descargas --}}
                <section class="panel p-4 sm:p-5">
                    <div class="label-tag mb-4">descargas</div>
                    <div class="space-y-2" id="downloads">
                        <button onclick="downloadFile('pdb')" class="flex w-full items-center justify-between gap-2 border border-ink-300 bg-ink-50 px-3 py-2.5 font-mono text-[11px] uppercase tracking-wider text-ink-700 transition-colors hover:border-signal-mint hover:text-signal-mint-deep">
                            <span class="truncate">↓ structure.pdb</span>
                            <span class="shrink-0 text-ink-400">pdb</span>
                        </button>
                        <button onclick="downloadFile('cif')" class="flex w-full items-center justify-between gap-2 border border-ink-300 bg-ink-50 px-3 py-2.5 font-mono text-[11px] uppercase tracking-wider text-ink-700 transition-colors hover:border-signal-mint hover:text-signal-mint-deep">
                            <span class="truncate">↓ structure.cif</span>
                            <span class="shrink-0 text-ink-400">mmcif</span>
                        </button>
                        <button onclick="downloadSequence()" class="flex w-full items-center justify-between gap-2 border border-ink-300 bg-ink-50 px-3 py-2.5 font-mono text-[11px] uppercase tracking-wider text-ink-700 transition-colors hover:border-signal-mint hover:text-signal-mint-deep">
                            <span class="truncate">↓ sequence.fasta</span>
                            <span class="shrink-0 text-ink-400">fasta</span>
                        </button>
                        <button onclick="downloadPlddtCsv()" class="flex w-full items-center justify-between gap-2 border border-ink-300 bg-ink-50 px-3 py-2.5 font-mono text-[11px] uppercase tracking-wider text-ink-700 transition-colors hover:border-signal-mint hover:text-signal-mint-deep">
                            <span class="truncate">↓ plddt.csv</span>
                            <span class="shrink-0 text-ink-400">csv</span>
                        </button>
                        <button onclick="downloadOutputsJson()" class="flex w-full items-center justify-between gap-2 border border-ink-300 bg-ink-50 px-3 py-2.5 font-mono text-[11px] uppercase tracking-wider text-ink-700 transition-colors hover:border-signal-mint hover:text-signal-mint-deep">
                            <span class="truncate">↓ outputs.json</span>
                            <span class="shrink-0 text-ink-400">json</span>
                        </button>
                        <button onclick="downloadSnapshot()" class="flex w-full items-center justify-between gap-2 border border-ink-300 bg-ink-50 px-3 py-2.5 font-mono text-[11px] uppercase tracking-wider text-ink-700 transition-colors hover:border-signal-mint hover:text-signal-mint-deep">
                            <span class="truncate">↓ snapshot.png</span>
                            <span class="shrink-0 text-ink-400">png</span>
                        </button>
                    </div>
                </section>

                <a href="{{ route('jobs.create') }}" class="btn-secondary block w-full justify-center text-center">
                    + nuevo envío
                </a>
            </aside>
        </div>

        {{-- ─────────── ANÁLISIS CON IA ─────────── --}}
        <section id="ai-analysis-panel" class="panel mt-6 p-4 sm:mt-8 sm:p-6">
            <div class="mb-4 flex flex-wrap items-baseline justify-between gap-3">
                <div class="label-tag">§ interpretación ia</div>
                <span id="ai-analysis-meta" class="font-mono text-[10px] uppercase tracking-wider text-ink-400"></span>
            </div>

            <div id="ai-analysis-loading" class="hidden flex items-center gap-3 font-mono text-xs uppercase tracking-wider text-ink-500">
                <x-loading-spinner size="sm" />
                analizando resultados con ia…
            </div>

            <div id="ai-analysis-cta" class="hidden border-l-2 border-signal-mint/50 bg-ink-100/60 px-5 py-4">
                <p class="font-serif text-sm leading-relaxed text-ink-700">
                    Configura tu API key (Anthropic, OpenAI o Gemini) en
                    <a href="{{ route('settings.ai') }}" class="font-mono text-signal-mint hover:underline">/ajustes-ia</a>
                    para ver un análisis interpretativo de estos resultados y preguntar sobre los datos.
                </p>
            </div>

            <div id="ai-analysis-error" class="hidden border-l-2 border-signal-rust/60 bg-signal-rust/5 px-5 py-4">
                <p class="font-mono text-xs text-signal-rust"></p>
            </div>

            <pre id="ai-analysis-body" class="hidden whitespace-pre-wrap break-words font-serif text-[15px] leading-relaxed text-ink-800"></pre>
        </section>

        {{-- ─────────── CHAT Q&A ─────────── --}}
        <section id="ai-chat-panel" class="panel mt-4 p-4 sm:p-6 {{ $outputs ? '' : 'hidden' }}">
            <div class="mb-4 flex flex-wrap items-baseline justify-between gap-3">
                <div class="label-tag">§ preguntas sobre los datos</div>
                <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">historial sólo en memoria</span>
            </div>

            <div id="ai-chat-messages" class="mb-4 space-y-3 max-h-[480px] overflow-y-auto pr-2"></div>

            <form id="ai-chat-form" class="flex flex-col gap-2 sm:flex-row sm:items-end">
                <label class="sr-only" for="ai-chat-input">Pregunta</label>
                <textarea id="ai-chat-input" rows="2"
                          placeholder="¿Por qué el PAE es alto en el dominio central?"
                          class="input-lab flex-1 resize-y"></textarea>
                <button type="submit" id="ai-chat-send" class="btn-primary shrink-0 justify-center sm:w-auto">
                    <span class="status-dot bg-signal-mint"></span>
                    enviar
                </button>
            </form>
            <p id="ai-chat-hint" class="mt-2 font-mono text-[10px] uppercase tracking-wider text-ink-400">
                enter para enviar · shift+enter para salto de línea
            </p>
        </section>

        {{-- ─────────── PIE · RE-EJECUCIÓN ─────────── --}}
        @if(isset($libraryEntry) && $libraryEntry)
            <footer class="mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-ink-300 pt-6">
                <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">
                    · guardado el <span class="text-ink-700 tabular-nums">{{ $libraryEntry->created_at->format('Y-m-d') }}</span>
                    <span class="text-ink-400">en la biblioteca</span>
                </span>
                <form action="{{ route('library.rerun', $libraryEntry) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="btn-secondary"
                            onclick="return confirm('¿Relanzar esta predicción? Consumirá GPU nueva en el CESGA.')">
                        ↻ ejecutar de nuevo
                    </button>
                </form>
            </footer>
        @endif
    </div>

    {{-- ─────────── FALLIDO ─────────── --}}
    <div id="error-section" class="hidden">
        <div class="mx-auto max-w-lg py-12 sm:py-16">
            <div class="label-tag mb-4">! predicción fallida</div>
            <h2 class="font-serif text-2xl text-ink-900 sm:text-3xl">El pipeline no pudo completarse</h2>
            <p class="mt-3 font-serif text-ink-600" id="error-message">Ocurrió un error durante la predicción.</p>
            <a href="{{ route('jobs.create') }}" class="btn-primary mt-6 inline-flex">
                ↻ reintentar
            </a>
        </div>
    </div>

    {{-- ─────────── DISCUSIONES ─────────── --}}
    <section class="mt-10 border-t border-ink-300 pt-8 sm:mt-12 sm:pt-10">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <div class="label-tag">discusiones</div>
                <h2 class="mt-2 font-serif text-xl text-ink-900 sm:text-2xl">Hilos del foro</h2>
                <p class="mt-1 font-serif text-sm text-ink-600">
                    {{ $threadCount }} {{ $threadCount === 1 ? 'discusión vinculada' : 'discusiones vinculadas' }} a esta predicción.
                </p>
            </div>
            <div class="flex items-center gap-2">
                @auth
                    @if($libraryEntry)
                        <a href="{{ route('forum.create', ['predicted_job_id' => $libraryEntry->id]) }}" class="btn-primary">
                            + nuevo hilo
                        </a>
                    @endif
                @endauth
                @if($threadCount > 5)
                    <a href="{{ route('forum.index', ['job' => $jobId]) }}" class="btn-secondary">
                        ver todos →
                    </a>
                @endif
            </div>
        </div>

        @if($threads->isEmpty())
            <div class="border border-dashed border-ink-300 bg-ink-100/50 px-6 py-10 text-center">
                <p class="font-serif text-sm text-ink-600">
                    Aún no hay discusiones sobre esta predicción.
                    @auth
                        @if($libraryEntry)
                            <a href="{{ route('forum.create', ['predicted_job_id' => $libraryEntry->id]) }}" class="font-mono text-signal-mint hover:underline">Inicia la primera →</a>
                        @endif
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
                    <a href="{{ route('forum.index', ['job' => $jobId]) }}"
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
// Estado global
let viewer = null;
let spinning = true;
let currentOutputs = @json($outputs);
let currentAccounting = @json($accounting);
let currentPdbString = null;
let currentSequence = null;
let currentResidues = null;      // [{resi, letter}]
let currentCaCoords = null;       // Map<resi, [x,y,z]>
let currentHighlightShape = null; // handle for 3Dmol addSphere overlay
const jobId = @json($jobId);
const initialStatus = @json($status['status'] ?? 'PENDING');

const PLDDT_COLORS = {
    veryHigh: '#0053D6',
    high: '#65CBF3',
    medium: '#FFDB13',
    low: '#FF7D45',
};

// Estilos de botón de viewer (reutilizados por setViewerStyle y setBtnActive)
const BTN_ACTIVE   = 'border border-signal-mint/40 bg-signal-mint/15 px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-signal-mint-deep';
const BTN_INACTIVE = 'border border-transparent px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 hover:border-ink-400 hover:text-ink-800';

function setBtnActive(id, active) {
    const btn = document.getElementById(id);
    if (!btn) return;
    btn.className = active ? BTN_ACTIVE : BTN_INACTIVE;
}

// Diccionario de aminoácidos 3→1 (estándar IUPAC + selenocisteína, pirrolisina, unknown)
const AA_3_TO_1 = {
    ALA: 'A', ARG: 'R', ASN: 'N', ASP: 'D', CYS: 'C', GLN: 'Q', GLU: 'E', GLY: 'G',
    HIS: 'H', ILE: 'I', LEU: 'L', LYS: 'K', MET: 'M', PHE: 'F', PRO: 'P', SER: 'S',
    THR: 'T', TRP: 'W', TYR: 'Y', VAL: 'V', SEC: 'U', PYL: 'O', UNK: 'X',
};

// Escala de hidrofobicidad Kyte-Doolittle
const KYTE_DOOLITTLE = {
    A: 1.8, R: -4.5, N: -3.5, D: -3.5, C: 2.5, Q: -3.5, E: -3.5, G: -0.4,
    H: -3.2, I: 4.5, L: 3.8, K: -3.9, M: 1.9, F: 2.8, P: -1.6, S: -0.8,
    T: -0.7, W: -0.9, Y: -1.3, V: 4.2, X: 0, U: 2.5, O: -3.9,
};

const FUN_FACTS = [
    'Las proteínas son máquinas moleculares: digieren los alimentos, mueven los músculos y copian el ADN.',
    'El cuerpo humano contiene más de 20 000 proteínas distintas, cada una con una forma 3D única.',
    'AlphaFold2 resolvió el problema del plegamiento de proteínas, abierto durante 50 años, en 2020.',
    'Una proteína típica tiene entre 50 y 1 000 aminoácidos encadenados.',
    'El CESGA Finis Terrae III utiliza GPUs NVIDIA A100, los mismos chips que entrenan grandes modelos de IA.',
    'La ubiquitina (76 aminoácidos) es una de las proteínas más pequeñas y estudiadas.',
    'Las proteínas se pliegan en su forma 3D en milisegundos, más rápido de lo que ningún ordenador puede simular.',
];

// ====== POLLING ======
function startPolling() {
    let factIdx = 0;
    const factEl = document.getElementById('fun-fact');

    const factInterval = setInterval(() => {
        factIdx = (factIdx + 1) % FUN_FACTS.length;
        factEl.style.opacity = 0;
        setTimeout(() => {
            factEl.textContent = FUN_FACTS[factIdx];
            factEl.style.opacity = 1;
        }, 300);
    }, 5000);

    const poll = async () => {
        try {
            const resp = await axios.get(`/api/jobs/${jobId}/status`);
            const data = resp.data;
            updateProgressUI(data.status);

            if (data.status === 'COMPLETED') {
                clearInterval(factInterval);
                await loadResults();
            } else if (data.status === 'FAILED') {
                clearInterval(factInterval);
                showError(data.error_message || 'La predicción ha fallado.');
            } else {
                setTimeout(poll, 3000);
            }
        } catch (err) {
            setTimeout(poll, 5000);
        }
    };

    setTimeout(poll, 2000);
}

function updateProgressUI(status) {
    const steps = document.querySelectorAll('#progress-steps > li');
    const title = document.getElementById('progress-title');
    const subtitle = document.getElementById('progress-subtitle');

    if (status === 'PENDING') {
        title.textContent = 'En cola…';
        subtitle.textContent = 'Tu trabajo está en cola en el supercomputador CESGA. Los recursos GPU se asignarán en breve.';
    } else if (status === 'RUNNING') {
        title.textContent = 'AlphaFold2 está ejecutándose…';
        subtitle.textContent = 'La red neuronal está prediciendo la estructura 3D de tu proteína.';
        steps[0].classList.remove('opacity-40');
        steps[0].querySelector('div.h-2').classList.remove('animate-pulse');
        steps[0].querySelector('div.h-2').classList.replace('bg-amber-500', 'bg-emerald-500');
        steps[1].classList.remove('opacity-40');
        steps[1].querySelector('div.h-2').classList.add('animate-pulse');
        steps[1].querySelector('div.h-2').classList.replace('bg-ink-400', 'bg-amber-500');
    }
}

async function loadResults() {
    try {
        const [outputsResp, accountingResp] = await Promise.all([
            axios.get(`/api/jobs/${jobId}/outputs`),
            axios.get(`/api/jobs/${jobId}/accounting`),
        ]);
        currentOutputs = outputsResp.data;
        currentAccounting = accountingResp.data;
        renderResults();
    } catch (err) {
        showError('No se pudieron cargar los resultados. Recarga la página.');
    }
}

function showError(message) {
    document.getElementById('progress-section').classList.add('hidden');
    document.getElementById('results-section').classList.add('hidden');
    document.getElementById('error-section').classList.remove('hidden');
    document.getElementById('error-message').textContent = message;
}

// ====== RENDERIZAR RESULTADOS ======
function renderResults() {
    document.getElementById('progress-section').classList.add('hidden');
    document.getElementById('results-section').classList.remove('hidden');

    const meta = currentOutputs.protein_metadata;
    const structural = currentOutputs.structural_data;
    const confidence = structural.confidence;
    const bio = currentOutputs.biological_data;

    if (meta) {
        document.getElementById('result-title').textContent = meta.protein_name;
        let sub = `<em>${meta.organism || ''}</em>`;
        if (meta.uniprot_id) sub += ` &middot; UniProt: <a href="https://www.uniprot.org/uniprot/${meta.uniprot_id}" target="_blank" class="font-mono text-signal-mint hover:underline">${meta.uniprot_id}</a>`;
        if (meta.pdb_id) sub += ` &middot; PDB: <a href="https://www.rcsb.org/structure/${meta.pdb_id}" target="_blank" class="font-mono text-signal-mint hover:underline">${meta.pdb_id}</a>`;
        document.getElementById('result-subtitle').innerHTML = sub;
    }

    renderQualitySummary();

    // Esperar a un frame para que el layout esté asentado antes de inicializar el visor.
    // init3DViewer es async: extrae la secuencia del PDB resuelto, así que
    // después tenemos que pintar la sequence/hydro tracks y el contact map.
    requestAnimationFrame(async () => {
        await init3DViewer(structural.pdb_file, confidence.plddt_per_residue, meta);
        drawSequenceTrack();
        drawHydrophobicityTrack();
        drawContactMap();
        wireSequenceTrackInteractions();
        wireContactMapInteractions();
    });

    drawPlddtChart(confidence.plddt_per_residue);

    if (confidence.pae_matrix) {
        drawPaeHeatmap(confidence.pae_matrix);
    }

    document.getElementById('plddt-mean').textContent = confidence.plddt_mean.toFixed(1);
    document.getElementById('mean-pae').textContent = (confidence.mean_pae || 0).toFixed(1) + ' Å';
    renderPlddtHistogram(confidence.plddt_histogram);

    renderBioData(bio);
    renderLowConfidenceRegions(confidence.plddt_per_residue);

    if (bio.secondary_structure_prediction) {
        drawSecondaryStructureChart(bio.secondary_structure_prediction);
    }

    if (currentAccounting) {
        renderAccounting(currentAccounting);
    }

    loadAiAnalysis();
    setupAiChat();
}

// ====== VISOR 3D ======
let currentPlddt = null;
// El PDB en uso puede ser solo Cα (simulador) o backbone completo (RCSB).
// Cambia el estilo por defecto de cartoon y evita stick que no podría renderizar.
let currentPdbIsCaOnly = false;

/**
 * Analiza una cadena PDB y devuelve {residues, caOnly}.
 * caOnly = true cuando todos los ATOM presentes son Cα — es lo que devuelve
 * el mock del simulador para secuencias custom. Un PDB así NO puede
 * renderizarse con cartoon/rectangle ni con stick porque 3Dmol necesita
 * backbone (N, C, O) para construir el ribbon y enlaces inferidos para
 * los sticks.
 */
function analyzePdb(pdbString) {
    if (!pdbString) return { residues: 0, caOnly: false };
    let caCount = 0;
    let nonCaAtoms = 0;
    for (const line of pdbString.split('\n')) {
        if (!line.startsWith('ATOM')) continue;
        // Un ATOM válido mide al menos 54 chars (hasta coords z). Las líneas
        // tipo "ATOM    X Y Z CONF" del simulador son cabeceras y se descartan.
        if (line.length < 54) continue;
        const name = line.substring(12, 16).trim();
        if (!name) continue;
        // Requerimos que haya coords numéricas parseables, si no, no es ATOM real.
        const x = parseFloat(line.substring(30, 38));
        if (!Number.isFinite(x)) continue;
        if (name === 'CA') caCount++;
        else nonCaAtoms++;
    }
    return {
        residues: caCount,
        caOnly: caCount > 0 && nonCaAtoms === 0,
    };
}

/**
 * Si la metadata tiene pdb_id y el PDB local es trivial o solo Cα, se
 * descarga la estructura canónica de RCSB PDB (CORS abierto). Es la misma
 * práctica que usan AlphaFold DB y UniProt: mostrar la estructura
 * experimental cuando está disponible.
 */
async function resolvePdbSource(localPdb, metadata) {
    const pdbId = metadata && metadata.pdb_id;
    const info = analyzePdb(localPdb);
    const needsFallback = info.residues < 20 || info.caOnly;

    if (!needsFallback || !pdbId) {
        return {
            pdb: localPdb,
            caOnly: info.caOnly,
            source: pdbId ? `pdb ${pdbId} · simulador` : 'simulador cesga',
        };
    }

    try {
        const url = `https://files.rcsb.org/download/${encodeURIComponent(pdbId)}.pdb`;
        const resp = await fetch(url, { mode: 'cors' });
        if (!resp.ok) throw new Error(`rcsb ${resp.status}`);
        const realPdb = await resp.text();
        const realInfo = analyzePdb(realPdb);
        return {
            pdb: realPdb,
            caOnly: realInfo.caOnly,
            source: `pdb ${pdbId} · rcsb · estructura experimental`,
        };
    } catch (err) {
        console.warn('No se pudo descargar el PDB real desde RCSB, usando el local:', err);
        return {
            pdb: localPdb,
            caOnly: info.caOnly,
            source: `${pdbId} · fallback local`,
        };
    }
}

async function init3DViewer(pdbString, plddtArray, metadata) {
    const container = document.getElementById('viewer-container');
    if (!container) return;

    if (!window.$3Dmol) {
        container.innerHTML = '<p style="display:flex;align-items:center;justify-content:center;height:100%;color:#7a7461;font-family:monospace;font-size:11px;text-transform:uppercase;letter-spacing:0.1em">cargando visor 3d…</p>';
        setTimeout(() => init3DViewer(pdbString, plddtArray, metadata), 500);
        return;
    }

    if (container.clientWidth === 0 || container.clientHeight === 0) {
        // Fallback sólo si las clases responsive no han asignado alto (muy
        // improbable). Usamos un alto proporcional a la anchura disponible
        // con techo razonable para móvil.
        const fallback = Math.max(320, Math.min(520, Math.floor(container.clientWidth * 0.75)));
        container.style.width = '100%';
        container.style.height = fallback + 'px';
    }

    container.innerHTML = '';

    // Guardar pLDDT para usarlo desde cualquier cambio de estilo posterior
    currentPlddt = plddtArray || [];

    // Resolver qué PDB usar (real o stub)
    const sourceLabel = document.getElementById('viewer-source');
    if (sourceLabel) sourceLabel.textContent = 'procedencia · descargando pdb real…';

    const { pdb: resolvedPdb, source, caOnly } = await resolvePdbSource(pdbString, metadata);
    currentPdbIsCaOnly = !!caOnly;
    if (sourceLabel) {
        sourceLabel.textContent = caOnly
            ? `procedencia · ${source} · traza cα`
            : `procedencia · ${source}`;
    }

    // Extraer secuencia, residuos y coords Cα del PDB resuelto — lo usan la
    // sequence track, el contact map y el highlight bidireccional.
    currentPdbString = resolvedPdb;
    const extracted = extractPdbData(resolvedPdb);
    currentSequence = extracted.sequence;
    currentResidues = extracted.residues;
    currentCaCoords = extracted.caCoords;

    try {
        viewer = $3Dmol.createViewer(container, {
            backgroundColor: 'white',
            antialias: true,
            id: 'proteinux-viewer',
        });
    } catch (e) {
        console.error('No se pudo crear el visor 3Dmol:', e);
        container.innerHTML = '<p style="display:flex;align-items:center;justify-content:center;height:100%;color:#b91c1c;font-family:monospace;font-size:11px">error inicializando webgl</p>';
        return;
    }

    viewer.addModel(resolvedPdb, 'pdb', { keepH: true });

    const model = viewer.getModel(0);

    // Inyectamos el pLDDT en el campo b de cada átomo. El colorscheme del
    // estilo (ver setViewerStyle) lee prop:'b' y aplica el gradiente pLDDT
    // definido abajo. Forzamos la invalidación de la caché de geometría
    // llamando a setColorByFunction (aunque devuelva el color existente,
    // su efecto secundario es model.molObj = null, que obliga a cartoon/
    // stick a reconstruir la malla con los nuevos atom.b).
    model.setColorByFunction({}, function (atom) {
        const resIdx = atom.resi - 1;
        if (currentPlddt && currentPlddt.length > 0 && resIdx >= 0 && resIdx < currentPlddt.length) {
            atom.b = currentPlddt[resIdx];
        }
        return plddtToHexNumber(atom.b || 0);
    });

    setViewerStyle('cartoon');

    // Etiquetas al pasar el ratón — convención estándar de visores científicos
    viewer.setHoverable(
        {},
        true,
        function (atom) {
            const label = document.getElementById('viewer-hover');
            if (!label) return;
            const plddt = currentPlddt && currentPlddt[atom.resi - 1];
            label.textContent =
                `${atom.resn || ''}${atom.resi || ''} · ${atom.atom || ''}` +
                (plddt !== undefined ? ` · pLDDT ${plddt.toFixed(1)}` : '');
            // Resalta la celda correspondiente en la sequence track
            highlightSequenceCell(atom.resi);
        },
        function () {
            const label = document.getElementById('viewer-hover');
            if (label) label.innerHTML = '&nbsp;';
            clearSequenceHighlight();
        }
    );

    viewer.zoomTo();
    viewer.render();

    // Arranca el spin por defecto. El botón se marca como activo.
    viewer.spin(true);
    spinning = true;
    setBtnActive('btn-spin', true);

    // Forzar resize tras render — soluciona el bug de canvas vacío en algunos layouts
    requestAnimationFrame(() => {
        if (viewer) {
            viewer.resize();
            viewer.zoomTo();
            viewer.render();
        }
    });
}

// Re-render del visor y gráficas en cambios de tamaño de ventana.
// Debounce para evitar redibujo en cada píxel mientras se arrastra el
// borde de la ventana o se rota el dispositivo móvil.
let _resizeTimer = null;
window.addEventListener('resize', () => {
    if (viewer) {
        viewer.resize();
        viewer.render();
    }
    clearTimeout(_resizeTimer);
    _resizeTimer = setTimeout(() => {
        if (currentOutputs && currentOutputs.structural_data && currentOutputs.structural_data.confidence) {
            const c = currentOutputs.structural_data.confidence;
            if (c.plddt_per_residue) drawPlddtChart(c.plddt_per_residue);
            if (c.pae_matrix) drawPaeHeatmap(c.pae_matrix);
        }
    }, 150);
});

let currentStyle = 'cartoon';

// pLDDT → color AlphaFold, como número hex
function plddtToHexNumber(p) {
    if (p == null) return 0xffffff;
    if (p >= 90) return 0x0053D6;
    if (p >= 70) return 0x65CBF3;
    if (p >= 50) return 0xFFDB13;
    return 0xFF7D45;
}

// Gradiente custom que extiende $3Dmol.GradientType para poder pasarlo
// como `gradient` dentro de `colorscheme: {prop:'b', gradient: ...}`.
// Esta es la única vía documentada que funciona uniformemente en todos
// los estilos (cartoon, stick, sphere, surface, line). La
// comprobación `instanceof GradientType` dentro de getColorFromStyle
// exige que heredemos del tipo base.
let _plddtGradient = null;
function getPlddtGradient() {
    if (_plddtGradient) return _plddtGradient;
    if (!window.$3Dmol || !$3Dmol.GradientType) return null;
    class PlddtGradient extends $3Dmol.GradientType {
        range() { return [0, 100]; }
        valueToHex(val) {
            return plddtToHexNumber(val);
        }
    }
    _plddtGradient = new PlddtGradient();
    return _plddtGradient;
}

function plddtColorscheme() {
    return { prop: 'b', gradient: getPlddtGradient() };
}

function setViewerStyle(style) {
    if (!viewer) return;
    currentStyle = style;

    // Limpiar estilos y superficies previos
    viewer.setStyle({}, {});
    viewer.removeAllSurfaces();

    // Ocultar aguas; HETATM restantes como stick gris tenue
    viewer.setStyle({ resn: 'HOH' }, {});
    viewer.setStyle({ hetflag: true }, {
        stick: { color: 0x94908a, radius: 0.18 },
    });

    const scheme = plddtColorscheme();
    // Para traza Cα usamos el estilo 'trace' (tubo a lo largo de CAs) —
    // 'rectangle' requiere N, C, O para construir el ribbon. También
    // desactivamos las flechas porque no hay láminas β detectables.
    const cartoonStyleName = currentPdbIsCaOnly ? 'trace' : 'rectangle';
    const cartoonArrows = !currentPdbIsCaOnly;

    if (style === 'cartoon') {
        viewer.setStyle({ hetflag: false }, {
            cartoon: {
                colorscheme: scheme,
                thickness: 0.4,
                arrows: cartoonArrows,
                style: cartoonStyleName,
                opacity: 1.0,
            },
        });
    } else if (style === 'stick') {
        if (currentPdbIsCaOnly) {
            // Sin backbone no hay enlaces → stick invisible. Caemos a una
            // traza Cα con cilindros gruesos + esferas pequeñas en los CAs,
            // que es lo que realmente espera ver el usuario al pulsar
            // "varilla" sobre un modelo de solo Cα.
            viewer.setStyle({ hetflag: false }, {
                cartoon: {
                    colorscheme: scheme,
                    thickness: 0.5,
                    style: 'trace',
                },
                sphere: { colorscheme: scheme, radius: 0.55 },
            });
        } else {
            viewer.setStyle({ hetflag: false }, {
                stick: { colorscheme: scheme, radius: 0.22 },
                cartoon: {
                    colorscheme: scheme,
                    thickness: 0.15,
                    opacity: 0.35,
                    style: 'rectangle',
                },
            });
        }
    } else if (style === 'sphere') {
        viewer.setStyle({ hetflag: false }, {
            sphere: { colorscheme: scheme, scale: 0.32 },
        });
    } else if (style === 'surface') {
        viewer.setStyle({ hetflag: false }, {
            cartoon: {
                colorscheme: scheme,
                thickness: 0.4,
                arrows: cartoonArrows,
                style: cartoonStyleName,
            },
        });
        viewer.addSurface(
            $3Dmol.SurfaceType.VDW,
            { opacity: 0.72, colorscheme: scheme },
            { hetflag: false },
        );
    }

    viewer.render();

    ['cartoon', 'stick', 'sphere', 'surface'].forEach(s => {
        const btn = document.getElementById('btn-' + s);
        if (!btn) return;
        if (s === style) {
            btn.className = 'px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider bg-signal-mint/15 text-signal-mint-deep border border-signal-mint/40';
        } else {
            btn.className = 'px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 border border-transparent hover:border-ink-400 hover:text-ink-800';
        }
    });
}

function toggleSpin() {
    if (!viewer) return;
    spinning = !spinning;
    viewer.spin(spinning);
    const btn = document.getElementById('btn-spin');
    btn.className = spinning
        ? 'px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider bg-signal-mint/15 text-signal-mint-deep border border-signal-mint/40'
        : 'px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 border border-transparent hover:border-ink-400 hover:text-ink-800';
}

function resetViewer() {
    if (!viewer) return;
    viewer.zoomTo();
    viewer.render();
}

// ====== GRÁFICA pLDDT ======
function drawPlddtChart(plddtArray) {
    const canvas = document.getElementById('plddt-chart');
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = 120 * dpr;
    ctx.scale(dpr, dpr);
    const w = rect.width, h = 120;
    const barW = Math.max(1, w / plddtArray.length);

    ctx.clearRect(0, 0, w, h);

    [50, 70, 90].forEach(t => {
        const y = h - (t / 100) * h;
        ctx.strokeStyle = '#cdc6b0';
        ctx.lineWidth = 0.5;
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(w, y);
        ctx.stroke();
    });

    plddtArray.forEach((val, i) => {
        const x = i * barW;
        const barH = (val / 100) * h;
        if (val >= 90) ctx.fillStyle = PLDDT_COLORS.veryHigh;
        else if (val >= 70) ctx.fillStyle = PLDDT_COLORS.high;
        else if (val >= 50) ctx.fillStyle = PLDDT_COLORS.medium;
        else ctx.fillStyle = PLDDT_COLORS.low;
        ctx.fillRect(x, h - barH, Math.max(barW - 0.5, 1), barH);
    });
}

// ====== MAPA PAE ======
function drawPaeHeatmap(matrix) {
    const canvas = document.getElementById('pae-heatmap');
    const tooltip = document.getElementById('pae-tooltip');
    const n = matrix.length;
    // El parent flex-1 ya excluye la leyenda de gradiente (shrink-0) por lo
    // que no hace falta restar su anchura. Dejamos un pequeño margen para
    // que el canvas no pegue a la derecha.
    const available = Math.max(0, canvas.parentElement.clientWidth - 4);
    const size = Math.max(160, Math.min(420, available));
    canvas.width = size;
    canvas.height = size;
    canvas.style.width = size + 'px';
    canvas.style.height = size + 'px';

    const ctx = canvas.getContext('2d');
    const cellSize = size / n;

    let maxVal = 0;
    for (let i = 0; i < n; i++)
        for (let j = 0; j < n; j++)
            if (matrix[i][j] > maxVal) maxVal = matrix[i][j];

    document.getElementById('pae-max-label').textContent = maxVal.toFixed(0) + ' Å';

    const imageData = ctx.createImageData(n, n);
    for (let i = 0; i < n; i++) {
        for (let j = 0; j < n; j++) {
            const val = matrix[i][j] / maxVal;
            const idx = (i * n + j) * 4;
            const r = Math.round(13 + val * 242);
            const g = Math.round(74 + val * 181);
            const b = Math.round(62 + val * 193);
            imageData.data[idx] = r;
            imageData.data[idx + 1] = g;
            imageData.data[idx + 2] = b;
            imageData.data[idx + 3] = 255;
        }
    }

    const tmpCanvas = document.createElement('canvas');
    tmpCanvas.width = n;
    tmpCanvas.height = n;
    tmpCanvas.getContext('2d').putImageData(imageData, 0, 0);

    ctx.imageSmoothingEnabled = false;
    ctx.drawImage(tmpCanvas, 0, 0, size, size);

    canvas.addEventListener('mousemove', (e) => {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const i = Math.floor(y / cellSize);
        const j = Math.floor(x / cellSize);
        if (i >= 0 && i < n && j >= 0 && j < n) {
            tooltip.textContent = `res ${i + 1} × ${j + 1} : ${matrix[i][j].toFixed(2)} Å`;
            tooltip.style.left = (x + 10) + 'px';
            tooltip.style.top = (y - 30) + 'px';
            tooltip.classList.remove('hidden');
        }
    });
    canvas.addEventListener('mouseleave', () => tooltip.classList.add('hidden'));
}

// ====== HISTOGRAMA ======
function renderPlddtHistogram(hist) {
    if (!hist) return;
    const container = document.getElementById('plddt-histogram');
    const total = (hist.very_high || 0) + (hist.high || 0) + (hist.medium || 0) + (hist.low || 0);
    const items = [
        { label: 'muy alta &gt;90', count: hist.very_high || 0, color: PLDDT_COLORS.veryHigh },
        { label: 'alta 70–90', count: hist.high || 0, color: PLDDT_COLORS.high },
        { label: 'media 50–70', count: hist.medium || 0, color: PLDDT_COLORS.medium },
        { label: 'baja &lt;50', count: hist.low || 0, color: PLDDT_COLORS.low },
    ];

    container.innerHTML = items.map(item => {
        const pct = total > 0 ? (item.count / total * 100).toFixed(0) : 0;
        return `<div>
            <div class="flex justify-between font-mono text-[10px] uppercase tracking-wider mb-1">
                <span class="text-ink-500">${item.label}</span>
                <span class="text-ink-800 tabular-nums">${item.count} · ${pct}%</span>
            </div>
            <div class="h-1 bg-ink-200 overflow-hidden">
                <div class="h-full" style="width:${pct}%; background:${item.color}"></div>
            </div>
        </div>`;
    }).join('');
}

// ====== DATOS BIOLÓGICOS ======
function renderBioData(bio) {
    if (!bio) return;
    const container = document.getElementById('bio-data');
    let html = '';

    const solColor = bio.solubility_score >= 50 ? '#0f766e' : '#b45309';
    html += `<div class="field">
        <dt>solubilidad</dt>
        <dd style="color:${solColor}">${bio.solubility_score?.toFixed(1) ?? '—'}/100 · ${bio.solubility_prediction || '—'}</dd>
    </div>`;

    const stabColor = bio.stability_status === 'stable' ? '#0f766e' : '#b45309';
    html += `<div class="field">
        <dt>índice de inestabilidad</dt>
        <dd style="color:${stabColor}">${bio.instability_index?.toFixed(1) ?? '—'} · ${bio.stability_status || '—'}</dd>
    </div>`;

    if (bio.toxicity_alerts && bio.toxicity_alerts.length > 0) {
        html += `<div class="mt-3 border border-signal-rust/40 bg-signal-rust/5 p-2">
            <div class="font-mono text-[10px] uppercase tracking-wider text-signal-rust">! alertas de toxicidad</div>
            <div class="mt-1 flex flex-wrap gap-1">${bio.toxicity_alerts.map(a => `<span class="border border-signal-rust/40 px-1.5 py-0.5 font-mono text-[10px] text-signal-rust">${a}</span>`).join('')}</div>
        </div>`;
    } else {
        html += `<div class="field">
            <dt>toxicidad</dt>
            <dd class="text-signal-mint-deep">sin alertas</dd>
        </div>`;
    }

    if (bio.allergenicity_alerts && bio.allergenicity_alerts.length > 0) {
        html += `<div class="mt-3 border border-signal-amber/40 bg-signal-amber/5 p-2">
            <div class="font-mono text-[10px] uppercase tracking-wider text-signal-amber">! alergenicidad</div>
            <div class="mt-1 flex flex-wrap gap-1">${bio.allergenicity_alerts.map(a => `<span class="border border-signal-amber/40 px-1.5 py-0.5 font-mono text-[10px] text-signal-amber">${a}</span>`).join('')}</div>
        </div>`;
    }

    container.innerHTML = html;
}

// ====== ESTRUCTURA SECUNDARIA ======
function drawSecondaryStructureChart(ss) {
    const canvas = document.getElementById('secondary-structure-chart');
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    canvas.width = 160 * dpr;
    canvas.height = 160 * dpr;
    ctx.scale(dpr, dpr);

    const data = [
        { label: 'hélice', pct: ss.helix_percent || 0, color: '#b91c1c' },
        { label: 'lámina', pct: ss.strand_percent || 0, color: '#0d9488' },
        { label: 'bucle', pct: ss.coil_percent || 0, color: '#7a7461' },
    ];

    const cx = 80, cy = 80, r = 60, innerR = 40;
    let startAngle = -Math.PI / 2;

    data.forEach(d => {
        const angle = (d.pct / 100) * Math.PI * 2;
        ctx.beginPath();
        ctx.arc(cx, cy, r, startAngle, startAngle + angle);
        ctx.arc(cx, cy, innerR, startAngle + angle, startAngle, true);
        ctx.closePath();
        ctx.fillStyle = d.color;
        ctx.fill();
        startAngle += angle;
    });

    ctx.fillStyle = '#16140e';
    ctx.font = '600 11px "IBM Plex Mono", monospace';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('ESTRUCTURA', cx, cy - 6);
    ctx.font = '10px "IBM Plex Mono", monospace';
    ctx.fillStyle = '#7a7461';
    ctx.fillText('PREDICCIÓN', cx, cy + 10);

    const legend = document.getElementById('ss-legend');
    legend.innerHTML = data.map(d =>
        `<span class="flex items-center gap-1.5"><span class="inline-block h-2 w-2" style="background:${d.color}"></span>${d.label} ${d.pct.toFixed(0)}%</span>`
    ).join('');
}

// ====== FACTURACIÓN ======
function renderAccounting(acc) {
    if (!acc || !acc.accounting) return;
    const a = acc.accounting;
    const container = document.getElementById('accounting-data');
    container.innerHTML = `
        <dl class="space-y-0">
            <div class="field"><dt>horas cpu</dt><dd>${a.cpu_hours?.toFixed(4) ?? '—'}</dd></div>
            <div class="field"><dt>horas gpu</dt><dd>${a.gpu_hours?.toFixed(4) ?? '—'}</dd></div>
            <div class="field"><dt>memoria · gb·h</dt><dd>${a.memory_gb_hours?.toFixed(3) ?? '—'}</dd></div>
            <div class="field"><dt>tiempo de pared</dt><dd>${a.total_wall_time_seconds ?? '—'}<span class="text-ink-500">s</span></dd></div>
        </dl>
        <div class="mt-4 space-y-2">
            ${buildEfficiencyBar('eficiencia cpu', a.cpu_efficiency_percent)}
            ${buildEfficiencyBar('eficiencia gpu', a.gpu_efficiency_percent)}
            ${buildEfficiencyBar('eficiencia memoria', a.memory_efficiency_percent)}
        </div>
    `;
}

function buildEfficiencyBar(label, pct) {
    if (pct == null) return '';
    const color = pct >= 80 ? '#0d9488' : pct >= 50 ? '#b45309' : '#b91c1c';
    return `<div>
        <div class="flex justify-between font-mono text-[10px] uppercase tracking-wider mb-1">
            <span class="text-ink-500">${label}</span>
            <span class="text-ink-800 tabular-nums">${pct.toFixed(1)}%</span>
        </div>
        <div class="h-1 bg-ink-200 overflow-hidden">
            <div class="h-full" style="width:${pct}%; background:${color}"></div>
        </div>
    </div>`;
}

function buildConfidenceBadge(score) {
    score = parseFloat(score).toFixed(1);
    let cls, label;
    if (score >= 90) { cls = 'border-[#0053D6]/60 text-[#0053D6] bg-[#0053D6]/10'; label = 'muy alta'; }
    else if (score >= 70) { cls = 'border-sky-500/60 text-sky-700 bg-sky-500/10'; label = 'alta'; }
    else if (score >= 50) { cls = 'border-amber-500/60 text-amber-700 bg-amber-500/10'; label = 'media'; }
    else { cls = 'border-orange-500/60 text-orange-700 bg-orange-500/10'; label = 'baja'; }
    return `<span class="inline-flex items-center gap-2 border px-3 py-1.5 font-mono text-[11px] uppercase tracking-wider ${cls}">
        <span class="status-dot" style="background:currentColor"></span>
        pLDDT ${score} · ${label}
    </span>`;
}

// ====== DESCARGAS ======
function downloadBlob(content, filename, mime = 'text/plain') {
    const blob = new Blob([content], { type: mime });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}

function downloadDataUri(dataUri, filename) {
    const a = document.createElement('a');
    a.href = dataUri;
    a.download = filename;
    a.click();
}

function downloadFile(type) {
    if (!currentOutputs) return;
    const content = type === 'pdb'
        ? currentOutputs.structural_data.pdb_file
        : currentOutputs.structural_data.cif_file;
    if (!content) return;
    downloadBlob(content, `structure.${type}`, 'text/plain');
}

function downloadSequence() {
    if (!currentSequence) return;
    const name = currentOutputs?.protein_metadata?.protein_name || 'protein';
    const safe = String(name).replace(/[^a-zA-Z0-9_\-]/g, '_');
    downloadBlob(`>${safe}\n${currentSequence}\n`, 'sequence.fasta', 'text/plain');
}

function downloadPlddtCsv() {
    const arr = currentOutputs?.structural_data?.confidence?.plddt_per_residue;
    if (!Array.isArray(arr)) return;
    const lines = ['residue,plddt'];
    arr.forEach((p, i) => lines.push(`${i + 1},${p}`));
    downloadBlob(lines.join('\n') + '\n', 'plddt.csv', 'text/csv');
}

function downloadOutputsJson() {
    if (!currentOutputs) return;
    downloadBlob(JSON.stringify(currentOutputs, null, 2), 'outputs.json', 'application/json');
}

function downloadSnapshot() {
    if (!viewer) return;
    try {
        const uri = viewer.pngURI();
        downloadDataUri(uri, 'snapshot.png');
    } catch (e) {
        console.warn('pngURI failed:', e);
    }
}

// ====== HERRAMIENTAS DE INVESTIGACIÓN ======
// Parseo de PDB: extrae sequence, residuos y coords Cα en una sola pasada.
function extractPdbData(pdb) {
    const residues = [];
    const caCoords = new Map();
    if (!pdb) return { sequence: '', residues, caCoords };
    const seen = new Set();
    for (const line of pdb.split('\n')) {
        if (!line.startsWith('ATOM') || line.length < 54) continue;
        const name = line.substring(12, 16).trim();
        if (name !== 'CA') continue;
        const resi = parseInt(line.substring(22, 26).trim(), 10);
        if (!Number.isFinite(resi) || seen.has(resi)) continue;
        const x = parseFloat(line.substring(30, 38));
        const y = parseFloat(line.substring(38, 46));
        const z = parseFloat(line.substring(46, 54));
        if (!Number.isFinite(x) || !Number.isFinite(y) || !Number.isFinite(z)) continue;
        seen.add(resi);
        const resName = line.substring(17, 20).trim().toUpperCase();
        residues.push({ resi, letter: AA_3_TO_1[resName] || 'X' });
        caCoords.set(resi, [x, y, z]);
    }
    residues.sort((a, b) => a.resi - b.resi);
    return { sequence: residues.map(r => r.letter).join(''), residues, caCoords };
}

// pLDDT → color hex de la paleta AlphaFold canónica.
function plddtColor(p) {
    if (p == null) return '#cdc6b0';
    if (p >= 90) return PLDDT_COLORS.veryHigh;
    if (p >= 70) return PLDDT_COLORS.high;
    if (p >= 50) return PLDDT_COLORS.medium;
    return PLDDT_COLORS.low;
}

// Interpolación lineal entre dos colores hex (#rrggbb).
function mixHex(hex1, hex2, t) {
    const a = parseInt(hex1.slice(1), 16);
    const b = parseInt(hex2.slice(1), 16);
    const r = Math.round(((a >> 16) & 0xff) * (1 - t) + ((b >> 16) & 0xff) * t);
    const g = Math.round(((a >> 8) & 0xff) * (1 - t) + ((b >> 8) & 0xff) * t);
    const bl = Math.round((a & 0xff) * (1 - t) + (b & 0xff) * t);
    return `rgb(${r},${g},${bl})`;
}

// ------ Quality Summary ------
function buildQualitySummary(confidence, bio) {
    const plddt = confidence?.plddt_mean ?? 0;
    const meanPae = confidence?.mean_pae ?? 30;
    const paeScore = Math.max(0, 100 - Math.min(meanPae, 30) / 30 * 100);
    const bioPenalty =
          (bio?.stability_status === 'unstable' ? 15 : 0)
        + (bio?.toxicity_alerts && bio.toxicity_alerts.length ? 10 : 0)
        + (bio?.allergenicity_alerts && bio.allergenicity_alerts.length ? 5 : 0);
    const bioScore = Math.max(0, 100 - bioPenalty);
    const score = Math.round(plddt * 0.5 + paeScore * 0.3 + bioScore * 0.2);
    const tier =
        score >= 85 ? { label: 'muy alta', color: PLDDT_COLORS.veryHigh } :
        score >= 70 ? { label: 'alta',     color: PLDDT_COLORS.high } :
        score >= 50 ? { label: 'media',    color: PLDDT_COLORS.medium } :
                      { label: 'baja',     color: PLDDT_COLORS.low };
    return { score, tier, plddt, meanPae, bioOk: bioPenalty === 0 };
}

function renderQualitySummary() {
    if (!currentOutputs) return;
    const q = buildQualitySummary(
        currentOutputs.structural_data?.confidence || {},
        currentOutputs.biological_data || {},
    );

    // Header: pequeño badge compacto de pLDDT (un solo vistazo).
    const header = document.getElementById('quality-header');
    if (header) {
        header.innerHTML = buildConfidenceBadge(q.plddt);
    }

    // Sidebar: bloque destacado dentro de "tab. 1 · resumen de confianza".
    // Muestra la puntuación global (0-100) y el tier; los desgloses
    // por métrica siguen viéndose en la <dl> inmediatamente debajo.
    const block = document.getElementById('quality-score-block');
    if (block) {
        block.classList.remove('hidden');
        block.innerHTML = `
            <div class="border-b border-dashed border-ink-300 pb-4">
                <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500">puntuación global</div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-serif text-[44px] leading-none text-ink-900 tabular-nums">${q.score}</span>
                    <span class="font-mono text-[11px] uppercase tracking-wider text-ink-500">/ 100</span>
                    <span class="ml-auto flex items-center gap-1.5 font-mono text-[10px] uppercase tracking-wider"
                          style="color:${q.tier.color}">
                        <span class="inline-block h-1.5 w-1.5 rounded-full" style="background:${q.tier.color}"></span>
                        ${q.tier.label}
                    </span>
                </div>
                ${q.bioOk ? '' : `
                    <div class="mt-2 font-mono text-[10px] uppercase tracking-wider text-signal-rust">
                        · alertas biológicas detectadas
                    </div>
                `}
            </div>
        `;
    }
}

// ------ Sequence Track + Hydrophobicity ------
function drawSequenceTrack() {
    const canvas = document.getElementById('sequence-track');
    if (!canvas || !currentSequence || !currentResidues) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const cssW = canvas.clientWidth || canvas.parentElement.clientWidth || 600;
    const cssH = 44;
    canvas.width  = cssW * dpr;
    canvas.height = cssH * dpr;
    canvas.style.height = cssH + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, cssW, cssH);

    const plddt = currentOutputs?.structural_data?.confidence?.plddt_per_residue || [];
    const n = currentSequence.length;
    if (n === 0) return;
    const cellW = cssW / n;
    const cellH = 28;

    for (let i = 0; i < n; i++) {
        const p = plddt[i] != null ? plddt[i] : 0;
        ctx.fillStyle = plddtColor(p);
        ctx.fillRect(i * cellW, 0, Math.max(1, cellW + 0.5), cellH);

        if (cellW >= 10) {
            ctx.fillStyle = p >= 70 ? '#ffffff' : '#16140e';
            ctx.font = '600 10px "IBM Plex Mono", monospace';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(currentSequence[i], i * cellW + cellW / 2, cellH / 2);
        }
    }

    // Numeración cada N residuos
    ctx.fillStyle = '#7a7461';
    ctx.font = '9px "IBM Plex Mono", monospace';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    const step = n <= 50 ? 10 : n <= 200 ? 20 : 50;
    for (let i = step; i < n; i += step) {
        const resi = currentResidues[i]?.resi ?? (i + 1);
        ctx.fillText(String(resi), i * cellW, cellH + 2);
    }
}

function drawHydrophobicityTrack() {
    const canvas = document.getElementById('hydro-track');
    if (!canvas || !currentSequence) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const cssW = canvas.clientWidth || canvas.parentElement.clientWidth || 600;
    const cssH = 12;
    canvas.width  = cssW * dpr;
    canvas.height = cssH * dpr;
    canvas.style.height = cssH + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, cssW, cssH);

    const n = currentSequence.length;
    if (n === 0) return;
    const cellW = cssW / n;
    for (let i = 0; i < n; i++) {
        const h = KYTE_DOOLITTLE[currentSequence[i]] ?? 0;
        const t = Math.max(0, Math.min(1, (h + 4.5) / 9));
        ctx.fillStyle = mixHex('#134e4a', '#b45309', t);
        ctx.fillRect(i * cellW, 0, Math.max(1, cellW + 0.5), cssH);
    }
}

// ------ Sequence ↔ 3D bridge ------
function indexFromCanvasX(canvas, pageX) {
    const rect = canvas.getBoundingClientRect();
    const x = pageX - rect.left;
    const n = currentSequence ? currentSequence.length : 0;
    if (n === 0) return -1;
    const idx = Math.floor((x / rect.width) * n);
    return Math.max(0, Math.min(n - 1, idx));
}

function highlightResidue3D(resi) {
    if (!viewer || !currentCaCoords) return;
    const coords = currentCaCoords.get(resi);
    if (!coords) return;
    clearResidue3DHighlight();
    try {
        currentHighlightShape = viewer.addSphere({
            center: { x: coords[0], y: coords[1], z: coords[2] },
            radius: 2.2,
            color: '#0d9488',
            opacity: 0.8,
        });
        viewer.render();
    } catch (e) { /* noop */ }
}

function clearResidue3DHighlight() {
    if (!viewer || !currentHighlightShape) return;
    try {
        viewer.removeShape(currentHighlightShape);
        viewer.render();
    } catch (e) { /* noop */ }
    currentHighlightShape = null;
}

function focusResidueRange(start, end) {
    if (!viewer) return;
    const resis = [];
    for (let i = start; i <= end; i++) resis.push(i);
    try {
        viewer.zoomTo({ resi: resis });
        viewer.render();
    } catch (e) { /* noop */ }
}

// Overlay mint para resaltar la celda correspondiente a un residuo desde el visor 3D.
let _sequenceHighlightOverlay = null;
function highlightSequenceCell(resi) {
    if (!currentResidues || !currentSequence) return;
    const idx = currentResidues.findIndex(r => r.resi === resi);
    if (idx < 0) return;
    const canvas = document.getElementById('sequence-track');
    if (!canvas) return;
    const rect = canvas.getBoundingClientRect();
    const cellW = rect.width / currentSequence.length;
    if (!_sequenceHighlightOverlay) {
        _sequenceHighlightOverlay = document.createElement('div');
        _sequenceHighlightOverlay.className = 'pointer-events-none absolute border-2 border-signal-mint';
        _sequenceHighlightOverlay.style.top = '0';
        _sequenceHighlightOverlay.style.height = '28px';
        canvas.parentElement.appendChild(_sequenceHighlightOverlay);
    }
    _sequenceHighlightOverlay.style.display = 'block';
    _sequenceHighlightOverlay.style.left = (idx * cellW) + 'px';
    _sequenceHighlightOverlay.style.width = Math.max(2, cellW) + 'px';
}

function clearSequenceHighlight() {
    if (_sequenceHighlightOverlay) {
        _sequenceHighlightOverlay.style.display = 'none';
    }
}

function showSequenceTooltip(idx, pageX, pageY) {
    const tip = document.getElementById('sequence-tooltip');
    if (!tip) return;
    const letter = currentSequence[idx];
    const resi = currentResidues[idx]?.resi ?? (idx + 1);
    const plddt = currentOutputs?.structural_data?.confidence?.plddt_per_residue?.[idx];
    const hydro = KYTE_DOOLITTLE[letter] ?? 0;
    tip.textContent = `${letter}${resi} · pLDDT ${plddt != null ? plddt.toFixed(1) : '—'} · KD ${hydro.toFixed(1)}`;
    tip.style.left = (pageX + 12) + 'px';
    tip.style.top  = (pageY + 12) + 'px';
    tip.classList.remove('hidden');
}

function hideSequenceTooltip() {
    const tip = document.getElementById('sequence-tooltip');
    if (tip) tip.classList.add('hidden');
}

function wireSequenceTrackInteractions() {
    const canvas = document.getElementById('sequence-track');
    if (!canvas || canvas.dataset.wired) return;
    canvas.dataset.wired = '1';

    canvas.addEventListener('mousemove', (e) => {
        const idx = indexFromCanvasX(canvas, e.clientX);
        if (idx < 0) return;
        showSequenceTooltip(idx, e.clientX, e.clientY);
        const resi = currentResidues[idx]?.resi;
        if (resi != null) highlightResidue3D(resi);
    });
    canvas.addEventListener('mouseleave', () => {
        hideSequenceTooltip();
        clearResidue3DHighlight();
    });
    canvas.addEventListener('click', (e) => {
        const idx = indexFromCanvasX(canvas, e.clientX);
        if (idx < 0) return;
        const resi = currentResidues[idx]?.resi;
        if (resi != null) focusResidueRange(resi, resi);
    });
}

// ------ Contact Map ------
function computeContactMap(threshold = 8) {
    if (!currentCaCoords || currentCaCoords.size === 0) return null;
    const entries = [...currentCaCoords.entries()].sort((a, b) => a[0] - b[0]);
    const n = entries.length;
    const contacts = new Array(n);
    for (let i = 0; i < n; i++) contacts[i] = new Uint8Array(n);
    let count = 0;
    const t2 = threshold * threshold;
    for (let i = 0; i < n; i++) {
        const a = entries[i][1];
        for (let j = i + 1; j < n; j++) {
            const b = entries[j][1];
            const dx = a[0] - b[0], dy = a[1] - b[1], dz = a[2] - b[2];
            if (dx * dx + dy * dy + dz * dz < t2) {
                contacts[i][j] = 1;
                contacts[j][i] = 1;
                count++;
            }
        }
    }
    const resiList = entries.map(e => e[0]);
    const density = n > 0 ? (2 * count) / (n * n) : 0;
    return { n, contacts, count, density, resiList };
}

function drawContactMap() {
    const canvas = document.getElementById('contact-map');
    const stats = document.getElementById('contact-map-stats');
    if (!canvas) return;
    const data = computeContactMap(8);
    if (!data || data.n === 0) {
        canvas.style.display = 'none';
        if (stats) stats.textContent = 'sin coordenadas suficientes para el mapa';
        return;
    }
    canvas.style.display = 'block';

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const cssW = Math.min(canvas.clientWidth || 400, 440);
    const size = cssW;
    canvas.width = size * dpr;
    canvas.height = size * dpr;
    canvas.style.height = size + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    ctx.fillStyle = '#faf7ee';
    ctx.fillRect(0, 0, size, size);

    const cell = size / data.n;
    ctx.fillStyle = 'rgba(22, 20, 14, 0.88)';
    for (let i = 0; i < data.n; i++) {
        for (let j = 0; j < data.n; j++) {
            if (data.contacts[i][j]) {
                ctx.fillRect(j * cell, i * cell, Math.max(1, cell), Math.max(1, cell));
            }
        }
    }

    // Diagonal suave como referencia visual
    ctx.strokeStyle = 'rgba(13, 148, 136, 0.35)';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.lineTo(size, size);
    ctx.stroke();

    if (stats) {
        stats.textContent = `densidad ${(data.density * 100).toFixed(1)}% · ${data.count} contactos · threshold 8 Å`;
    }

    canvas._contactMapData = data;
}

function wireContactMapInteractions() {
    const canvas = document.getElementById('contact-map');
    const tip = document.getElementById('contact-map-tooltip');
    if (!canvas || !tip || canvas.dataset.wired) return;
    canvas.dataset.wired = '1';

    canvas.addEventListener('mousemove', (e) => {
        const data = canvas._contactMapData;
        if (!data) return;
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const cell = rect.width / data.n;
        const j = Math.max(0, Math.min(data.n - 1, Math.floor(x / cell)));
        const i = Math.max(0, Math.min(data.n - 1, Math.floor(y / cell)));
        const resiA = data.resiList[i];
        const resiB = data.resiList[j];
        const coordsA = currentCaCoords.get(resiA);
        const coordsB = currentCaCoords.get(resiB);
        let dist = null;
        if (coordsA && coordsB) {
            const dx = coordsA[0] - coordsB[0], dy = coordsA[1] - coordsB[1], dz = coordsA[2] - coordsB[2];
            dist = Math.sqrt(dx * dx + dy * dy + dz * dz);
        }
        tip.textContent = `res ${resiA} ↔ ${resiB}` + (dist != null ? ` · ${dist.toFixed(2)} Å` : '');
        tip.style.left = (e.clientX + 12) + 'px';
        tip.style.top  = (e.clientY + 12) + 'px';
        tip.classList.remove('hidden');
    });
    canvas.addEventListener('mouseleave', () => tip.classList.add('hidden'));
}

// ------ Low Confidence Regions ------
function detectLowConfidenceRegions(plddtArr, threshold = 70) {
    if (!Array.isArray(plddtArr)) return [];
    const regions = [];
    let start = null;
    let sum = 0;
    let cnt = 0;
    for (let i = 0; i < plddtArr.length; i++) {
        const p = plddtArr[i];
        if (p < threshold) {
            if (start === null) { start = i + 1; sum = 0; cnt = 0; }
            sum += p;
            cnt++;
        } else if (start !== null) {
            regions.push({ start, end: i, mean: cnt > 0 ? sum / cnt : 0 });
            start = null;
        }
    }
    if (start !== null) {
        regions.push({ start, end: plddtArr.length, mean: cnt > 0 ? sum / cnt : 0 });
    }
    return regions.slice(0, 10);
}

function renderLowConfidenceRegions(plddtArr) {
    const target = document.getElementById('low-confidence-regions');
    if (!target) return;
    const regions = detectLowConfidenceRegions(plddtArr);
    if (regions.length === 0) {
        target.innerHTML = '<li class="text-ink-400">estructura uniformemente fiable — ningún residuo &lt; 70</li>';
        return;
    }
    target.innerHTML = regions.map((r) => {
        const label = r.start === r.end ? `${r.start}` : `${r.start}–${r.end}`;
        return `
            <li class="flex items-center justify-between gap-2 border-b border-dashed border-ink-300 pb-1 last:border-b-0">
                <span class="flex items-baseline gap-2">
                    <span class="text-ink-800 tabular-nums">${label}</span>
                    <span class="text-ink-500">pLDDT ${r.mean.toFixed(1)}</span>
                </span>
                <button type="button" data-start="${r.start}" data-end="${r.end}"
                        class="text-signal-mint hover:underline">→ centrar</button>
            </li>`;
    }).join('');
    target.querySelectorAll('button[data-start]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const s = parseInt(btn.dataset.start, 10);
            const e = parseInt(btn.dataset.end, 10);
            if (Number.isFinite(s) && Number.isFinite(e)) focusResidueRange(s, e);
        });
    });
}

// ====== ANÁLISIS IA ======
let aiAnalysisLoaded = false;
let chatHistory = [];

function showAiState(state) {
    const states = ['ai-analysis-loading', 'ai-analysis-cta', 'ai-analysis-error', 'ai-analysis-body'];
    states.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (id === state) {
            el.classList.remove('hidden');
            if (id === 'ai-analysis-loading') el.classList.add('flex');
        } else {
            el.classList.add('hidden');
            if (id === 'ai-analysis-loading') el.classList.remove('flex');
        }
    });
}

async function loadAiAnalysis() {
    if (aiAnalysisLoaded) return;
    aiAnalysisLoaded = true;

    showAiState('ai-analysis-loading');

    try {
        const resp = await axios.get(`/api/jobs/${jobId}/ai-analysis`);
        const body = document.getElementById('ai-analysis-body');
        body.textContent = resp.data.analysis || '';
        showAiState('ai-analysis-body');
        document.getElementById('ai-analysis-meta').textContent =
            `via ${resp.data.provider || ''} · ${resp.data.model || ''}`;
        document.getElementById('ai-chat-panel').classList.remove('hidden');
    } catch (err) {
        aiAnalysisLoaded = false;
        if (err.response?.status === 409) {
            showAiState('ai-analysis-cta');
            document.getElementById('ai-chat-panel').classList.add('hidden');
            return;
        }
        const errBox = document.getElementById('ai-analysis-error');
        const msg = err.response?.data?.error || 'No se pudo generar el análisis.';
        errBox.querySelector('p').textContent = msg;
        showAiState('ai-analysis-error');
    }
}

// ====== CHAT Q&A ======
function setupAiChat() {
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-chat-input');
    if (!form || form.dataset.bound) return;
    form.dataset.bound = '1';

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        sendChatMessage();
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendChatMessage();
        }
    });
}

function renderChatMessage(role, content) {
    const container = document.getElementById('ai-chat-messages');
    const isUser = role === 'user';
    const wrapper = document.createElement('div');
    wrapper.className = isUser
        ? 'ml-auto max-w-[85%] border-l-2 border-ink-400 bg-ink-100/60 px-4 py-3'
        : 'mr-auto max-w-[90%] border-l-2 border-signal-mint bg-signal-mint/5 px-4 py-3';

    const label = document.createElement('div');
    label.className = 'mb-1 font-mono text-[10px] uppercase tracking-wider ' + (isUser ? 'text-ink-500' : 'text-signal-mint-deep');
    label.textContent = isUser ? '> tú' : '· asistente';
    wrapper.appendChild(label);

    const body = document.createElement('div');
    body.className = 'whitespace-pre-wrap font-serif text-[14px] leading-relaxed text-ink-800';
    body.textContent = content;
    wrapper.appendChild(body);

    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
    return wrapper;
}

async function sendChatMessage() {
    const input = document.getElementById('ai-chat-input');
    const button = document.getElementById('ai-chat-send');
    const text = input.value.trim();
    if (!text) return;

    input.value = '';
    input.disabled = true;
    button.disabled = true;
    button.style.opacity = '0.6';

    chatHistory.push({ role: 'user', content: text });
    renderChatMessage('user', text);

    const pending = renderChatMessage('assistant', '…');

    try {
        const resp = await axios.post(`/api/jobs/${jobId}/ai-chat`, {
            messages: chatHistory,
        });
        const reply = resp.data.reply || '';
        pending.querySelector('div:last-child').textContent = reply;
        chatHistory.push({ role: 'assistant', content: reply });
    } catch (err) {
        const msg = err.response?.data?.error || 'No se pudo obtener respuesta.';
        pending.querySelector('div:last-child').textContent = '[error] ' + msg;
        pending.className = 'mr-auto max-w-[90%] border-l-2 border-signal-rust/60 bg-signal-rust/5 px-4 py-3';
        chatHistory.pop();
    } finally {
        input.disabled = false;
        button.disabled = false;
        button.style.opacity = '1';
        input.focus();
    }
}

// ====== INIT ======
document.addEventListener('DOMContentLoaded', function() {
    if (currentOutputs) {
        renderResults();
    } else if (initialStatus !== 'FAILED') {
        startPolling();
    } else {
        showError('{{ $status["error_message"] ?? "La predicción ha fallado." }}');
    }
});
</script>
@endpush
