@extends('layouts.app')

@section('title', 'Job ' . $jobId)

@section('content')
<div class="mx-auto max-w-[1400px] px-4 py-10 sm:px-6 lg:px-8">

    {{-- Job header strip — always present --}}
    <div class="mb-8 flex flex-wrap items-center justify-between gap-3 border-b border-ink-700 pb-4 font-mono text-[11px] uppercase tracking-[0.14em] text-ink-400">
        <div class="flex items-center gap-3">
            <span class="text-ink-500">job</span>
            <span class="text-ink-50">{{ $jobId }}</span>
            <span class="text-ink-500">|</span>
            <span>pipeline <span class="text-ink-200">af2-proteinux/2.3.1</span></span>
        </div>
        <a href="{{ route('jobs.create') }}" class="text-signal-mint hover:underline">→ new submission</a>
    </div>

    {{-- ─────────── PENDING / RUNNING ─────────── --}}
    <div id="progress-section" class="{{ $outputs ? 'hidden' : '' }}">
        <div class="mx-auto max-w-2xl py-12">
            <div class="label-tag mb-4">live · ft3 cluster</div>

            <h1 class="font-serif text-4xl text-ink-50" id="progress-title">Processing your sequence…</h1>
            <p class="mt-3 max-w-xl font-serif text-base leading-relaxed text-ink-300" id="progress-subtitle">
                Your job is being prepared for the CESGA Finis Terrae&nbsp;III supercomputer.
            </p>

            <div class="mt-6">
                <x-loading-spinner size="lg" />
            </div>

            {{-- Pipeline trace --}}
            <ol class="mt-8 border border-ink-700 bg-ink-900/40" id="progress-steps">
                <li class="flex items-center justify-between gap-4 border-b border-ink-700 px-5 py-4 transition-colors" data-step="PENDING">
                    <div class="flex items-center gap-4">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-signal-mint">§ 01</span>
                        <div class="h-2 w-2 rounded-full bg-amber-400 animate-pulse" id="step-pending-dot"></div>
                        <span class="font-mono text-xs uppercase tracking-wider text-ink-100">queued · waiting for gpu</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-500">stage 01/03</span>
                </li>
                <li class="flex items-center justify-between gap-4 border-b border-ink-700 px-5 py-4 opacity-40 transition-opacity" data-step="RUNNING">
                    <div class="flex items-center gap-4">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-500">§ 02</span>
                        <div class="h-2 w-2 rounded-full bg-slate-600" id="step-running-dot"></div>
                        <span class="font-mono text-xs uppercase tracking-wider text-ink-300">running alphafold2 inference</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-500">stage 02/03</span>
                </li>
                <li class="flex items-center justify-between gap-4 px-5 py-4 opacity-40 transition-opacity" data-step="POSTPROCESS">
                    <div class="flex items-center gap-4">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-500">§ 03</span>
                        <div class="h-2 w-2 rounded-full bg-slate-600" id="step-post-dot"></div>
                        <span class="font-mono text-xs uppercase tracking-wider text-ink-300">generating structure files</span>
                    </div>
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-500">stage 03/03</span>
                </li>
            </ol>

            {{-- Marginalia / context --}}
            <aside class="mt-6 border-l-2 border-signal-mint/40 bg-ink-900/30 px-5 py-4">
                <div class="label-tag mb-2">marginalia</div>
                <p class="font-serif text-sm leading-relaxed text-ink-200" id="fun-fact">
                    Proteins are molecular machines — they digest food, move muscles, defend against infections, and copy DNA.
                </p>
            </aside>
        </div>
    </div>

    {{-- ─────────── COMPLETED ─────────── --}}
    <div id="results-section" class="{{ $outputs ? '' : 'hidden' }}">

        {{-- Result title block --}}
        <header class="mb-8 grid items-end gap-4 border-b border-ink-700 pb-6 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="label-tag">result · structure prediction</div>
                <h1 class="mt-3 font-serif text-4xl text-ink-50" id="result-title">
                    @if($outputs && ($outputs['protein_metadata'] ?? null))
                        {{ $outputs['protein_metadata']['protein_name'] }}
                    @else
                        Prediction Results
                    @endif
                </h1>
                <p class="mt-2 font-serif text-base text-ink-300" id="result-subtitle">
                    @if($outputs && ($outputs['protein_metadata'] ?? null))
                        <span class="italic">{{ $outputs['protein_metadata']['organism'] ?? '' }}</span>
                        @if($outputs['protein_metadata']['uniprot_id'] ?? null)
                            &middot; UniProt: <a href="https://www.uniprot.org/uniprot/{{ $outputs['protein_metadata']['uniprot_id'] }}" target="_blank" class="font-mono text-signal-mint hover:underline">{{ $outputs['protein_metadata']['uniprot_id'] }}</a>
                        @endif
                        @if($outputs['protein_metadata']['pdb_id'] ?? null)
                            &middot; PDB: <a href="https://www.rcsb.org/structure/{{ $outputs['protein_metadata']['pdb_id'] }}" target="_blank" class="font-mono text-signal-mint hover:underline">{{ $outputs['protein_metadata']['pdb_id'] }}</a>
                        @endif
                    @else
                        Job {{ $jobId }}
                    @endif
                </p>
            </div>
            <div class="lg:col-span-3 lg:text-right" id="confidence-badge-container">
                @if($outputs)
                    <x-confidence-badge :score="$outputs['structural_data']['confidence']['plddt_mean'] ?? 0" />
                @endif
            </div>
        </header>

        {{-- Main grid --}}
        <div class="grid gap-6 lg:grid-cols-12">

            {{-- 3D viewer + plots --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- 3D viewer --}}
                <figure class="panel">
                    <figcaption class="flex items-center justify-between border-b border-ink-700 px-5 py-3">
                        <span class="label-tag">fig. 1 · 3d structure</span>
                        <div class="flex items-center gap-1" id="viewer-controls">
                            <button onclick="setViewerStyle('cartoon')" id="btn-cartoon"
                                    class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider bg-signal-mint/20 text-signal-mint border border-signal-mint/40">cartoon</button>
                            <button onclick="setViewerStyle('stick')" id="btn-stick"
                                    class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-400 border border-transparent hover:border-ink-600 hover:text-ink-100">stick</button>
                            <button onclick="setViewerStyle('sphere')" id="btn-sphere"
                                    class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-400 border border-transparent hover:border-ink-600 hover:text-ink-100">sphere</button>
                            <span class="mx-1 text-ink-700">|</span>
                            <button onclick="toggleSpin()" id="btn-spin"
                                    class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-400 border border-transparent hover:border-ink-600 hover:text-ink-100">spin</button>
                            <button onclick="resetViewer()"
                                    class="px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-400 border border-transparent hover:border-ink-600 hover:text-ink-100">reset</button>
                        </div>
                    </figcaption>
                    <div id="viewer-container" class="h-[520px] w-full bg-ink-950"></div>
                    <div class="grid grid-cols-2 gap-4 border-t border-ink-700 px-5 py-3 font-mono text-[10px] uppercase tracking-wider text-ink-400 sm:grid-cols-4">
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5" style="background:#0053D6"></span>very high &gt;90</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5" style="background:#65CBF3"></span>high 70–90</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5" style="background:#FFDB13"></span>medium 50–70</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5" style="background:#FF7D45"></span>low &lt;50</span>
                    </div>
                </figure>

                {{-- pLDDT plot --}}
                <figure class="panel p-5">
                    <figcaption class="mb-3 flex items-center justify-between">
                        <span class="label-tag">fig. 2 · pLDDT per residue <sup class="text-signal-mint">[2]</sup></span>
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-500">x · residue index · y · score</span>
                    </figcaption>
                    <canvas id="plddt-chart" class="w-full" height="120"></canvas>
                </figure>

                {{-- PAE heatmap --}}
                <figure class="panel p-5">
                    <figcaption class="mb-3 flex items-center justify-between">
                        <span class="label-tag">fig. 3 · predicted aligned error</span>
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-500">unit · ångström</span>
                    </figcaption>
                    <div class="flex items-start gap-5">
                        <div class="relative flex-1">
                            <canvas id="pae-heatmap" class="w-full"></canvas>
                            <div id="pae-tooltip" class="pointer-events-none absolute hidden border border-ink-600 bg-ink-900 px-2 py-1 font-mono text-[10px] text-ink-50 shadow-lg"></div>
                        </div>
                        <div class="flex flex-col items-center gap-1 font-mono text-[10px] uppercase tracking-wider text-ink-500">
                            <span>0 Å</span>
                            <div class="h-32 w-3" style="background: linear-gradient(to bottom, #0d4a3e, #14b8a6, #fbbf24, #ffffff)"></div>
                            <span id="pae-max-label">30 Å</span>
                        </div>
                    </div>
                </figure>
            </div>

            {{-- Sidebar --}}
            <aside class="lg:col-span-4 space-y-6">

                {{-- Confidence summary --}}
                <section class="panel p-5">
                    <div class="label-tag mb-4">tab. 1 · confidence summary</div>
                    <dl class="space-y-0" id="confidence-summary">
                        <div class="field">
                            <dt>average pLDDT</dt>
                            <dd id="plddt-mean">—</dd>
                        </div>
                        <div class="field">
                            <dt>mean PAE</dt>
                            <dd id="mean-pae">—</dd>
                        </div>
                    </dl>
                    <div class="mt-4 space-y-2" id="plddt-histogram"></div>
                </section>

                {{-- Bio --}}
                <section class="panel p-5">
                    <div class="label-tag mb-4">tab. 2 · biological properties</div>
                    <div class="space-y-2" id="bio-data">
                        <p class="font-mono text-xs text-ink-500">loading…</p>
                    </div>
                </section>

                {{-- Secondary structure --}}
                <section class="panel p-5">
                    <div class="label-tag mb-4">fig. 4 · secondary structure</div>
                    <canvas id="secondary-structure-chart" class="mx-auto" width="160" height="160"></canvas>
                    <div class="mt-3 flex justify-center gap-4 font-mono text-[10px] uppercase tracking-wider text-ink-300" id="ss-legend"></div>
                </section>

                {{-- HPC accounting --}}
                <section class="panel p-5">
                    <div class="label-tag mb-4">tab. 3 · hpc resources</div>
                    <div class="space-y-2" id="accounting-data">
                        <p class="font-mono text-xs text-ink-500">loading…</p>
                    </div>
                </section>

                {{-- Downloads --}}
                <section class="panel p-5">
                    <div class="label-tag mb-4">downloads</div>
                    <div class="space-y-2" id="downloads">
                        <button onclick="downloadFile('pdb')" class="flex w-full items-center justify-between border border-ink-700 bg-ink-900 px-3 py-2.5 font-mono text-[11px] uppercase tracking-wider text-ink-200 transition-colors hover:border-signal-mint hover:text-signal-mint">
                            <span>↓ structure.pdb</span>
                            <span class="text-ink-500">protein data bank</span>
                        </button>
                        <button onclick="downloadFile('cif')" class="flex w-full items-center justify-between border border-ink-700 bg-ink-900 px-3 py-2.5 font-mono text-[11px] uppercase tracking-wider text-ink-200 transition-colors hover:border-signal-mint hover:text-signal-mint">
                            <span>↓ structure.cif</span>
                            <span class="text-ink-500">mmcif</span>
                        </button>
                    </div>
                </section>

                <a href="{{ route('jobs.create') }}" class="block btn-secondary justify-center text-center">
                    + new submission
                </a>
            </aside>
        </div>
    </div>

    {{-- ─────────── FAILED ─────────── --}}
    <div id="error-section" class="hidden">
        <div class="mx-auto max-w-lg py-16">
            <div class="label-tag mb-4">! prediction failed</div>
            <h2 class="font-serif text-3xl text-ink-50">The pipeline could not complete</h2>
            <p class="mt-3 font-serif text-ink-300" id="error-message">An error occurred during prediction.</p>
            <a href="{{ route('jobs.create') }}" class="mt-6 inline-flex btn-primary">
                ↻ try again
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global state
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
    'Proteins are molecular machines — they digest food, move muscles, and copy DNA.',
    'The human body contains over 20,000 different proteins, each with a unique 3D shape.',
    'AlphaFold2 solved the 50-year-old protein folding problem in 2020.',
    'A typical protein has between 50 and 1,000 amino acids chained together.',
    'The CESGA Finis Terrae III uses NVIDIA A100 GPUs — the same chips training large AI models.',
    'Ubiquitin (76 amino acids) is one of the smallest and most studied proteins.',
    'Proteins fold into their 3D shape in milliseconds — faster than any computer can simulate.',
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
                showError(data.error_message || 'The prediction failed.');
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
        title.textContent = 'Waiting in queue…';
        subtitle.textContent = 'Your job is queued on the CESGA supercomputer. GPU resources will be allocated shortly.';
    } else if (status === 'RUNNING') {
        title.textContent = 'AlphaFold2 is running…';
        subtitle.textContent = 'The neural network is predicting the 3D structure of your protein.';
        steps[0].classList.remove('opacity-40');
        steps[0].querySelector('div.h-2').classList.remove('animate-pulse');
        steps[0].querySelector('div.h-2').classList.replace('bg-amber-400', 'bg-emerald-400');
        steps[1].classList.remove('opacity-40');
        steps[1].querySelector('div.h-2').classList.add('animate-pulse');
        steps[1].querySelector('div.h-2').classList.replace('bg-slate-600', 'bg-amber-400');
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
        showError('Failed to load results. Please refresh the page.');
    }
}

function showError(message) {
    document.getElementById('progress-section').classList.add('hidden');
    document.getElementById('results-section').classList.add('hidden');
    document.getElementById('error-section').classList.remove('hidden');
    document.getElementById('error-message').textContent = message;
}

// ====== RENDER RESULTS ======
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

    init3DViewer(structural.pdb_file, confidence.plddt_per_residue);
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

// ====== 3D VIEWER ======
function init3DViewer(pdbString, plddtArray) {
    const container = document.getElementById('viewer-container');
    if (!window.$3Dmol) {
        container.innerHTML = '<p class="flex items-center justify-center h-full text-ink-500 font-mono text-xs uppercase tracking-wider">3d viewer loading…</p>';
        setTimeout(() => init3DViewer(pdbString, plddtArray), 500);
        return;
    }
    viewer = $3Dmol.createViewer(container, {
        backgroundColor: '#07090d',
        antialias: true,
    });
    viewer.addModel(pdbString, 'pdb');

    const atoms = viewer.getModel(0).selectedAtoms({});
    if (plddtArray && plddtArray.length > 0) {
        atoms.forEach(atom => {
            const resIdx = atom.resi - 1;
            if (resIdx >= 0 && resIdx < plddtArray.length) {
                atom.b = plddtArray[resIdx];
            }
        });
    }

    setViewerStyle('cartoon');
    viewer.zoomTo();
    viewer.render();
}

function setViewerStyle(style) {
    if (!viewer) return;
    const colorFunc = function(atom) {
        const b = atom.b || 0;
        if (b >= 90) return PLDDT_COLORS.veryHigh;
        if (b >= 70) return PLDDT_COLORS.high;
        if (b >= 50) return PLDDT_COLORS.medium;
        return PLDDT_COLORS.low;
    };

    viewer.setStyle({}, {});
    if (style === 'cartoon') {
        viewer.setStyle({}, { cartoon: { colorfunc: colorFunc } });
    } else if (style === 'stick') {
        viewer.setStyle({}, { stick: { colorfunc: colorFunc, radius: 0.15 } });
    } else if (style === 'sphere') {
        viewer.setStyle({}, { sphere: { colorfunc: colorFunc, scale: 0.3 } });
    }
    viewer.render();

    ['cartoon', 'stick', 'sphere'].forEach(s => {
        const btn = document.getElementById('btn-' + s);
        if (s === style) {
            btn.className = 'px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider bg-signal-mint/20 text-signal-mint border border-signal-mint/40';
        } else {
            btn.className = 'px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-400 border border-transparent hover:border-ink-600 hover:text-ink-100';
        }
    });
}

function toggleSpin() {
    if (!viewer) return;
    spinning = !spinning;
    viewer.spin(spinning);
    const btn = document.getElementById('btn-spin');
    btn.className = spinning
        ? 'px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider bg-signal-mint/20 text-signal-mint border border-signal-mint/40'
        : 'px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-ink-400 border border-transparent hover:border-ink-600 hover:text-ink-100';
}

function resetViewer() {
    if (!viewer) return;
    viewer.zoomTo();
    viewer.render();
}

// ====== pLDDT CHART ======
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
        ctx.strokeStyle = '#1d2330';
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

// ====== PAE HEATMAP ======
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

// ====== HISTOGRAM ======
function renderPlddtHistogram(hist) {
    if (!hist) return;
    const container = document.getElementById('plddt-histogram');
    const total = (hist.very_high || 0) + (hist.high || 0) + (hist.medium || 0) + (hist.low || 0);
    const items = [
        { label: 'very high &gt;90', count: hist.very_high || 0, color: PLDDT_COLORS.veryHigh },
        { label: 'high 70–90', count: hist.high || 0, color: PLDDT_COLORS.high },
        { label: 'medium 50–70', count: hist.medium || 0, color: PLDDT_COLORS.medium },
        { label: 'low &lt;50', count: hist.low || 0, color: PLDDT_COLORS.low },
    ];

    container.innerHTML = items.map(item => {
        const pct = total > 0 ? (item.count / total * 100).toFixed(0) : 0;
        return `<div>
            <div class="flex justify-between font-mono text-[10px] uppercase tracking-wider mb-1">
                <span class="text-ink-400">${item.label}</span>
                <span class="text-ink-200 tabular-nums">${item.count} · ${pct}%</span>
            </div>
            <div class="h-1 bg-ink-800 overflow-hidden">
                <div class="h-full" style="width:${pct}%; background:${item.color}"></div>
            </div>
        </div>`;
    }).join('');
}

// ====== BIO DATA ======
function renderBioData(bio) {
    if (!bio) return;
    const container = document.getElementById('bio-data');
    let html = '';

    const solColor = bio.solubility_score >= 50 ? '#5eead4' : '#f5b849';
    html += `<div class="field">
        <dt>solubility</dt>
        <dd style="color:${solColor}">${bio.solubility_score?.toFixed(1) ?? '—'}/100 · ${bio.solubility_prediction || '—'}</dd>
    </div>`;

    const stabColor = bio.stability_status === 'stable' ? '#5eead4' : '#f5b849';
    html += `<div class="field">
        <dt>instability index</dt>
        <dd style="color:${stabColor}">${bio.instability_index?.toFixed(1) ?? '—'} · ${bio.stability_status || '—'}</dd>
    </div>`;

    if (bio.toxicity_alerts && bio.toxicity_alerts.length > 0) {
        html += `<div class="mt-3 border border-signal-rust/40 bg-signal-rust/5 p-2">
            <div class="font-mono text-[10px] uppercase tracking-wider text-signal-rust">! toxicity alerts</div>
            <div class="mt-1 flex flex-wrap gap-1">${bio.toxicity_alerts.map(a => `<span class="border border-signal-rust/40 px-1.5 py-0.5 font-mono text-[10px] text-signal-rust">${a}</span>`).join('')}</div>
        </div>`;
    } else {
        html += `<div class="field">
            <dt>toxicity</dt>
            <dd class="text-signal-mint">no alerts</dd>
        </div>`;
    }

    if (bio.allergenicity_alerts && bio.allergenicity_alerts.length > 0) {
        html += `<div class="mt-3 border border-signal-amber/40 bg-signal-amber/5 p-2">
            <div class="font-mono text-[10px] uppercase tracking-wider text-signal-amber">! allergenicity</div>
            <div class="mt-1 flex flex-wrap gap-1">${bio.allergenicity_alerts.map(a => `<span class="border border-signal-amber/40 px-1.5 py-0.5 font-mono text-[10px] text-signal-amber">${a}</span>`).join('')}</div>
        </div>`;
    }

    container.innerHTML = html;
}

// ====== SECONDARY STRUCTURE CHART ======
function drawSecondaryStructureChart(ss) {
    const canvas = document.getElementById('secondary-structure-chart');
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    canvas.width = 160 * dpr;
    canvas.height = 160 * dpr;
    ctx.scale(dpr, dpr);

    const data = [
        { label: 'helix', pct: ss.helix_percent || 0, color: '#ef6f4a' },
        { label: 'strand', pct: ss.strand_percent || 0, color: '#5eead4' },
        { label: 'coil', pct: ss.coil_percent || 0, color: '#6b7585' },
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

    ctx.fillStyle = '#ecedf2';
    ctx.font = '600 11px "IBM Plex Mono", monospace';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('STRUCTURE', cx, cy - 6);
    ctx.font = '10px "IBM Plex Mono", monospace';
    ctx.fillStyle = '#6b7585';
    ctx.fillText('PREDICTION', cx, cy + 10);

    const legend = document.getElementById('ss-legend');
    legend.innerHTML = data.map(d =>
        `<span class="flex items-center gap-1.5"><span class="inline-block h-2 w-2" style="background:${d.color}"></span>${d.label} ${d.pct.toFixed(0)}%</span>`
    ).join('');
}

// ====== ACCOUNTING ======
function renderAccounting(acc) {
    if (!acc || !acc.accounting) return;
    const a = acc.accounting;
    const container = document.getElementById('accounting-data');
    container.innerHTML = `
        <dl class="space-y-0">
            <div class="field"><dt>cpu hours</dt><dd>${a.cpu_hours?.toFixed(4) ?? '—'}</dd></div>
            <div class="field"><dt>gpu hours</dt><dd>${a.gpu_hours?.toFixed(4) ?? '—'}</dd></div>
            <div class="field"><dt>memory · gb·h</dt><dd>${a.memory_gb_hours?.toFixed(3) ?? '—'}</dd></div>
            <div class="field"><dt>wall time</dt><dd>${a.total_wall_time_seconds ?? '—'}<span class="text-ink-400">s</span></dd></div>
        </dl>
        <div class="mt-4 space-y-2">
            ${buildEfficiencyBar('cpu efficiency', a.cpu_efficiency_percent)}
            ${buildEfficiencyBar('gpu efficiency', a.gpu_efficiency_percent)}
            ${buildEfficiencyBar('memory efficiency', a.memory_efficiency_percent)}
        </div>
    `;
}

function buildEfficiencyBar(label, pct) {
    if (pct == null) return '';
    const color = pct >= 80 ? '#5eead4' : pct >= 50 ? '#f5b849' : '#ef6f4a';
    return `<div>
        <div class="flex justify-between font-mono text-[10px] uppercase tracking-wider mb-1">
            <span class="text-ink-400">${label}</span>
            <span class="text-ink-200 tabular-nums">${pct.toFixed(1)}%</span>
        </div>
        <div class="h-1 bg-ink-800 overflow-hidden">
            <div class="h-full" style="width:${pct}%; background:${color}"></div>
        </div>
    </div>`;
}

function buildConfidenceBadge(score) {
    score = parseFloat(score).toFixed(1);
    let cls, label;
    if (score >= 90) { cls = 'bg-[#0053D6]/20 text-[#65CBF3] border-[#0053D6]/40'; label = 'very high'; }
    else if (score >= 70) { cls = 'bg-sky-500/20 text-sky-300 border-sky-500/40'; label = 'high'; }
    else if (score >= 50) { cls = 'bg-amber-500/20 text-amber-300 border-amber-500/40'; label = 'medium'; }
    else { cls = 'bg-orange-500/20 text-orange-300 border-orange-500/40'; label = 'low'; }
    return `<span class="inline-flex items-center gap-2 border px-3 py-1.5 font-mono text-[11px] uppercase tracking-wider ${cls}">
        <span class="status-dot" style="background:currentColor"></span>
        pLDDT ${score} · ${label}
    </span>`;
}

// ====== DOWNLOADS ======
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
        showError('{{ $status["error_message"] ?? "The prediction failed." }}');
    }
});
</script>
@endpush
