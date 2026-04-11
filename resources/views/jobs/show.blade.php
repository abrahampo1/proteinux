@extends('layouts.app')

@section('title', 'Trabajo ' . $jobId)

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-10 sm:px-6 lg:px-8">

    {{-- Cabecera del trabajo --}}
    <div class="mb-8 flex flex-wrap items-center justify-between gap-3 border-b border-ink-300 pb-4 font-mono text-[11px] uppercase tracking-[0.14em] text-ink-500">
        <div class="flex items-center gap-3">
            <span class="text-ink-400">trabajo</span>
            <span class="text-ink-900">{{ $jobId }}</span>
            <span class="text-ink-400">|</span>
            <span>pipeline <span class="text-ink-800">af2-proteinux/2.3.1</span></span>
        </div>
        <a href="{{ route('jobs.create') }}" class="text-signal-mint hover:underline">→ nuevo envío</a>
    </div>

    {{-- ─────────── PENDIENTE / EN EJECUCIÓN ─────────── --}}
    <div id="progress-section" class="{{ $outputs ? 'hidden' : '' }}">
        <div class="mx-auto max-w-2xl py-12">
            <div class="label-tag mb-4">en vivo · clúster ft3</div>

            <h1 class="font-serif text-4xl text-ink-900" id="progress-title">Procesando tu secuencia…</h1>
            <p class="mt-3 max-w-xl font-serif text-base leading-relaxed text-ink-600" id="progress-subtitle">
                Tu trabajo se está preparando para el supercomputador CESGA Finis Terrae&nbsp;III.
            </p>

            <div class="mt-6">
                <x-loading-spinner size="lg" />
            </div>

            {{-- Trazado del pipeline --}}
            <ol class="mt-8 border border-ink-300 bg-ink-100/60" id="progress-steps">
                <li class="flex items-center justify-between gap-4 border-b border-ink-300 px-5 py-4 transition-colors" data-step="PENDING">
                    <div class="flex items-center gap-4">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-signal-mint">§ 01</span>
                        <div class="h-2 w-2 rounded-full bg-amber-500 animate-pulse" id="step-pending-dot"></div>
                        <span class="font-mono text-xs uppercase tracking-wider text-ink-800">en cola · esperando gpu</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">etapa 01/03</span>
                </li>
                <li class="flex items-center justify-between gap-4 border-b border-ink-300 px-5 py-4 opacity-40 transition-opacity" data-step="RUNNING">
                    <div class="flex items-center gap-4">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">§ 02</span>
                        <div class="h-2 w-2 rounded-full bg-ink-400" id="step-running-dot"></div>
                        <span class="font-mono text-xs uppercase tracking-wider text-ink-600">ejecutando inferencia alphafold2</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">etapa 02/03</span>
                </li>
                <li class="flex items-center justify-between gap-4 px-5 py-4 opacity-40 transition-opacity" data-step="POSTPROCESS">
                    <div class="flex items-center gap-4">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">§ 03</span>
                        <div class="h-2 w-2 rounded-full bg-ink-400" id="step-post-dot"></div>
                        <span class="font-mono text-xs uppercase tracking-wider text-ink-600">generando ficheros de estructura</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">etapa 03/03</span>
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
        <header class="mb-8 grid items-end gap-4 border-b border-ink-300 pb-6 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="label-tag">resultado · predicción de estructura</div>
                <h1 class="mt-3 font-serif text-4xl text-ink-900" id="result-title">
                    @if($outputs && ($outputs['protein_metadata'] ?? null))
                        {{ $outputs['protein_metadata']['protein_name'] }}
                    @else
                        Resultado de predicción
                    @endif
                </h1>
                <p class="mt-2 font-serif text-base text-ink-600" id="result-subtitle">
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
            <div class="lg:col-span-3 lg:text-right" id="confidence-badge-container">
                @if($outputs)
                    <x-confidence-badge :score="$outputs['structural_data']['confidence']['plddt_mean'] ?? 0" />
                @endif
            </div>
        </header>

        {{-- Cuadrícula principal --}}
        <div class="grid gap-6 lg:grid-cols-12">

            {{-- Visor 3D + gráficas --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- Visor 3D --}}
                <figure class="panel">
                    <figcaption class="flex items-center justify-between border-b border-ink-300 px-5 py-3">
                        <span class="label-tag">fig. 1 · estructura 3d</span>
                        <div class="flex flex-wrap items-center gap-1" id="viewer-controls">
                            <button onclick="setViewerStyle('cartoon')" id="btn-cartoon"
                                    class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider bg-signal-mint/15 text-signal-mint-deep border border-signal-mint/40">cartoon</button>
                            <button onclick="setViewerStyle('stick')" id="btn-stick"
                                    class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 border border-transparent hover:border-ink-400 hover:text-ink-800">varilla</button>
                            <button onclick="setViewerStyle('sphere')" id="btn-sphere"
                                    class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 border border-transparent hover:border-ink-400 hover:text-ink-800">esfera</button>
                            <button onclick="setViewerStyle('surface')" id="btn-surface"
                                    class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 border border-transparent hover:border-ink-400 hover:text-ink-800">superficie</button>
                            <span class="mx-1 text-ink-300">|</span>
                            <button onclick="toggleSpin()" id="btn-spin"
                                    class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 border border-transparent hover:border-ink-400 hover:text-ink-800">girar</button>
                            <button onclick="resetViewer()"
                                    class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-500 border border-transparent hover:border-ink-400 hover:text-ink-800">reset</button>
                        </div>
                    </figcaption>
                    <div id="viewer-container" style="position:relative;width:100%;height:520px;background:#ffffff"></div>
                    <div class="flex items-center justify-between gap-4 border-t border-ink-300 px-5 py-2 font-mono text-[10px] uppercase tracking-wider text-ink-500">
                        <span id="viewer-source">procedencia · cargando…</span>
                        <span id="viewer-hover" class="text-ink-700 tabular-nums">&nbsp;</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 border-t border-ink-300 px-5 py-3 font-mono text-[10px] uppercase tracking-wider text-ink-500 sm:grid-cols-4">
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5" style="background:#0053D6"></span>muy alta &gt;90</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5" style="background:#65CBF3"></span>alta 70–90</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5" style="background:#FFDB13"></span>media 50–70</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5" style="background:#FF7D45"></span>baja &lt;50</span>
                    </div>
                </figure>

                {{-- Gráfica pLDDT --}}
                <figure class="panel p-5">
                    <figcaption class="mb-3 flex items-center justify-between">
                        <span class="label-tag">fig. 2 · pLDDT por residuo <sup class="text-signal-mint">[2]</sup></span>
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">x · índice del residuo · y · puntuación</span>
                    </figcaption>
                    <canvas id="plddt-chart" class="w-full" height="120"></canvas>
                </figure>

                {{-- Mapa PAE --}}
                <figure class="panel p-5">
                    <figcaption class="mb-3 flex items-center justify-between">
                        <span class="label-tag">fig. 3 · error alineado predicho</span>
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">unidad · ångström</span>
                    </figcaption>
                    <div class="flex items-start gap-5">
                        <div class="relative flex-1">
                            <canvas id="pae-heatmap" class="w-full"></canvas>
                            <div id="pae-tooltip" class="pointer-events-none absolute hidden border border-ink-400 bg-ink-50 px-2 py-1 font-mono text-[10px] text-ink-900 shadow-lg"></div>
                        </div>
                        <div class="flex flex-col items-center gap-1 font-mono text-[10px] uppercase tracking-wider text-ink-400">
                            <span>0 Å</span>
                            <div class="h-32 w-3 border border-ink-300" style="background: linear-gradient(to bottom, #0d4a3e, #14b8a6, #fbbf24, #ffffff)"></div>
                            <span id="pae-max-label">30 Å</span>
                        </div>
                    </div>
                </figure>
            </div>

            {{-- Barra lateral --}}
            <aside class="lg:col-span-4 space-y-6">

                {{-- Resumen de confianza --}}
                <section class="panel p-5">
                    <div class="label-tag mb-4">tab. 1 · resumen de confianza</div>
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
                <section class="panel p-5">
                    <div class="label-tag mb-4">tab. 2 · propiedades biológicas</div>
                    <div class="space-y-2" id="bio-data">
                        <p class="font-mono text-xs text-ink-400">cargando…</p>
                    </div>
                </section>

                {{-- Estructura secundaria --}}
                <section class="panel p-5">
                    <div class="label-tag mb-4">fig. 4 · estructura secundaria</div>
                    <canvas id="secondary-structure-chart" class="mx-auto" width="160" height="160"></canvas>
                    <div class="mt-3 flex justify-center gap-4 font-mono text-[10px] uppercase tracking-wider text-ink-600" id="ss-legend"></div>
                </section>

                {{-- Facturación HPC --}}
                <section class="panel p-5">
                    <div class="label-tag mb-4">tab. 3 · recursos hpc</div>
                    <div class="space-y-2" id="accounting-data">
                        <p class="font-mono text-xs text-ink-400">cargando…</p>
                    </div>
                </section>

                {{-- Descargas --}}
                <section class="panel p-5">
                    <div class="label-tag mb-4">descargas</div>
                    <div class="space-y-2" id="downloads">
                        <button onclick="downloadFile('pdb')" class="flex w-full items-center justify-between border border-ink-300 bg-ink-50 px-3 py-2.5 font-mono text-[11px] uppercase tracking-wider text-ink-700 transition-colors hover:border-signal-mint hover:text-signal-mint-deep">
                            <span>↓ structure.pdb</span>
                            <span class="text-ink-400">protein data bank</span>
                        </button>
                        <button onclick="downloadFile('cif')" class="flex w-full items-center justify-between border border-ink-300 bg-ink-50 px-3 py-2.5 font-mono text-[11px] uppercase tracking-wider text-ink-700 transition-colors hover:border-signal-mint hover:text-signal-mint-deep">
                            <span>↓ structure.cif</span>
                            <span class="text-ink-400">mmcif</span>
                        </button>
                    </div>
                </section>

                <a href="{{ route('jobs.create') }}" class="block btn-secondary justify-center text-center">
                    + nuevo envío
                </a>
            </aside>
        </div>
    </div>

    {{-- ─────────── FALLIDO ─────────── --}}
    <div id="error-section" class="hidden">
        <div class="mx-auto max-w-lg py-16">
            <div class="label-tag mb-4">! predicción fallida</div>
            <h2 class="font-serif text-3xl text-ink-900">El pipeline no pudo completarse</h2>
            <p class="mt-3 font-serif text-ink-600" id="error-message">Ocurrió un error durante la predicción.</p>
            <a href="{{ route('jobs.create') }}" class="mt-6 inline-flex btn-primary">
                ↻ reintentar
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Estado global
let viewer = null;
let spinning = false;
let currentOutputs = @json($outputs);
let currentAccounting = @json($accounting);
const jobId = @json($jobId);
const initialStatus = @json($status['status'] ?? 'PENDING');

const PLDDT_COLORS = {
    veryHigh: '#0053D6',
    high: '#65CBF3',
    medium: '#FFDB13',
    low: '#FF7D45',
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

    document.getElementById('confidence-badge-container').innerHTML = buildConfidenceBadge(confidence.plddt_mean);

    // Esperar a un frame para que el layout esté asentado antes de inicializar el visor
    requestAnimationFrame(() => init3DViewer(structural.pdb_file, confidence.plddt_per_residue, meta));

    drawPlddtChart(confidence.plddt_per_residue);

    if (confidence.pae_matrix) {
        drawPaeHeatmap(confidence.pae_matrix);
    }

    document.getElementById('plddt-mean').textContent = confidence.plddt_mean.toFixed(1);
    document.getElementById('mean-pae').textContent = (confidence.mean_pae || 0).toFixed(1) + ' Å';
    renderPlddtHistogram(confidence.plddt_histogram);

    renderBioData(bio);

    if (bio.secondary_structure_prediction) {
        drawSecondaryStructureChart(bio.secondary_structure_prediction);
    }

    if (currentAccounting) {
        renderAccounting(currentAccounting);
    }
}

// ====== VISOR 3D ======
// pLDDT global (se usa dentro de colorfunc y tras fetch del PDB real)
let currentPlddt = null;

/**
 * Cuenta cuántos residuos tiene una cadena PDB contando las líneas ATOM
 * con átomo CA (una por residuo).
 */
function countResiduesInPdb(pdbString) {
    if (!pdbString) return 0;
    let count = 0;
    const lines = pdbString.split('\n');
    for (const line of lines) {
        if (line.startsWith('ATOM') && line.substring(12, 16).trim() === 'CA') {
            count++;
        }
    }
    return count;
}

/**
 * Si la metadata tiene pdb_id, intenta descargar la estructura canónica de
 * RCSB PDB. Es la misma práctica que usan los visores de AlphaFold / UniProt:
 * mostrar la estructura experimental como referencia cuando está disponible.
 * Si falla, devuelve el PDB local.
 */
async function resolvePdbSource(localPdb, metadata) {
    const pdbId = metadata && metadata.pdb_id;
    const localResidues = countResiduesInPdb(localPdb);

    // Si el PDB local ya tiene bastantes residuos, úsalo directamente.
    if (localResidues >= 20 || !pdbId) {
        return { pdb: localPdb, source: pdbId ? `pdb ${pdbId} · simulador` : 'simulador cesga' };
    }

    // El PDB del simulador es un stub — pedimos el real a RCSB.
    try {
        const url = `https://files.rcsb.org/download/${encodeURIComponent(pdbId)}.pdb`;
        const resp = await fetch(url, { mode: 'cors' });
        if (!resp.ok) throw new Error(`rcsb ${resp.status}`);
        const realPdb = await resp.text();
        return { pdb: realPdb, source: `pdb ${pdbId} · rcsb · estructura experimental` };
    } catch (err) {
        console.warn('No se pudo descargar el PDB real desde RCSB, usando el local:', err);
        return { pdb: localPdb, source: `${pdbId} · fallback local` };
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
        container.style.width = '100%';
        container.style.height = '520px';
    }

    container.innerHTML = '';

    // Guardar pLDDT para usarlo desde cualquier cambio de estilo posterior
    currentPlddt = plddtArray || [];

    // Resolver qué PDB usar (real o stub)
    const sourceLabel = document.getElementById('viewer-source');
    if (sourceLabel) sourceLabel.textContent = 'procedencia · descargando pdb real…';

    const { pdb: resolvedPdb, source } = await resolvePdbSource(pdbString, metadata);
    if (sourceLabel) sourceLabel.textContent = `procedencia · ${source}`;

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

    // Teñir cada átomo según el pLDDT de su residuo.
    // IMPORTANTE: 3Dmol soporta colorfunc solo para sphere/surface — para
    // cartoon y stick necesitamos fijar atom.color directamente antes de
    // aplicar el estilo, y en el estilo NO especificar color/colorscheme.
    const atoms = viewer.getModel(0).selectedAtoms({});
    atoms.forEach(atom => {
        const resIdx = atom.resi - 1;
        let plddt = atom.b || 0;
        if (currentPlddt && currentPlddt.length > 0 && resIdx >= 0 && resIdx < currentPlddt.length) {
            plddt = currentPlddt[resIdx];
        }
        atom.b = plddt;
        atom.color = plddtToHexNumber(plddt);
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
        },
        function () {
            const label = document.getElementById('viewer-hover');
            if (label) label.innerHTML = '&nbsp;';
        }
    );

    viewer.zoomTo();
    viewer.render();

    // Forzar resize tras render — soluciona el bug de canvas vacío en algunos layouts
    requestAnimationFrame(() => {
        if (viewer) {
            viewer.resize();
            viewer.zoomTo();
            viewer.render();
        }
    });
}

// Re-render del visor en cambios de tamaño de ventana
window.addEventListener('resize', () => {
    if (viewer) {
        viewer.resize();
        viewer.render();
    }
});

let currentStyle = 'cartoon';

// Convierte un pLDDT (0–100) al color AlphaFold, como número hex para
// poder asignarlo directamente a atom.color (3Dmol usa ese campo).
function plddtToHexNumber(p) {
    if (p >= 90) return 0x0053D6;
    if (p >= 70) return 0x65CBF3;
    if (p >= 50) return 0xFFDB13;
    return 0xFF7D45;
}

function setViewerStyle(style) {
    if (!viewer) return;
    currentStyle = style;

    // Limpiar estilos y superficies previos
    viewer.setStyle({}, {});
    viewer.removeAllSurfaces();

    // Ocultar aguas y ligar HETATM como sticks gris tenue
    viewer.setStyle({ resn: 'HOH' }, {});
    viewer.setStyle({ hetflag: true, byres: true }, {
        stick: { color: 0x94908a, radius: 0.18 },
    });

    // Para cartoon/stick: NO especificamos color ni colorscheme — 3Dmol usa
    // atom.color, que ya hemos teñido con el pLDDT en init3DViewer.
    if (style === 'cartoon') {
        viewer.setStyle({ hetflag: false }, {
            cartoon: {
                thickness: 0.4,
                arrows: true,
                style: 'rectangle',
                opacity: 1.0,
            },
        });
    } else if (style === 'stick') {
        viewer.setStyle({ hetflag: false }, {
            stick: { radius: 0.22 },
            cartoon: {
                thickness: 0.15,
                opacity: 0.35,
                style: 'rectangle',
            },
        });
    } else if (style === 'sphere') {
        viewer.setStyle({ hetflag: false }, {
            sphere: { scale: 0.32 },
        });
    } else if (style === 'surface') {
        viewer.setStyle({ hetflag: false }, {
            cartoon: {
                thickness: 0.4,
                arrows: true,
                style: 'rectangle',
            },
        });
        // Superficie VDW semitransparente; la coloreamos residuo a residuo
        // vía colorfunc (superficies sí lo aceptan).
        viewer.addSurface(
            $3Dmol.SurfaceType.VDW,
            {
                opacity: 0.72,
                colorfunc: function (atom) {
                    return plddtToHexNumber(atom.b || 0);
                },
            },
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
    const size = Math.min(420, canvas.parentElement.clientWidth - 60);
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
function downloadFile(type) {
    if (!currentOutputs) return;
    const content = type === 'pdb'
        ? currentOutputs.structural_data.pdb_file
        : currentOutputs.structural_data.cif_file;
    if (!content) return;
    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `structure.${type === 'pdb' ? 'pdb' : 'cif'}`;
    a.click();
    URL.revokeObjectURL(url);
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
