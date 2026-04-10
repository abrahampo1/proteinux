@extends('layouts.app')

@section('title', 'Job ' . $jobId)

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- PENDING / RUNNING state --}}
    <div id="progress-section" class="{{ $outputs ? 'hidden' : '' }}">
        <div class="mx-auto max-w-2xl text-center py-16">
            <div class="mb-6">
                <x-loading-spinner size="lg" class="mx-auto" />
            </div>
            <h1 class="text-2xl font-bold text-white mb-2" id="progress-title">Processing your sequence...</h1>
            <p class="text-slate-400 mb-8" id="progress-subtitle">Your job is being prepared for the CESGA Finis Terrae III supercomputer.</p>

            {{-- Progress steps --}}
            <div class="mx-auto max-w-md space-y-3 text-left" id="progress-steps">
                <div class="flex items-center gap-3 rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-3" data-step="PENDING">
                    <div class="h-3 w-3 rounded-full bg-amber-400 animate-pulse" id="step-pending-dot"></div>
                    <span class="text-sm text-slate-300">Queued &mdash; waiting for GPU resources</span>
                </div>
                <div class="flex items-center gap-3 rounded-lg border border-slate-800 bg-slate-900/50 px-4 py-3 opacity-40" data-step="RUNNING">
                    <div class="h-3 w-3 rounded-full bg-slate-600" id="step-running-dot"></div>
                    <span class="text-sm text-slate-400">Running AlphaFold2 inference</span>
                </div>
                <div class="flex items-center gap-3 rounded-lg border border-slate-800 bg-slate-900/50 px-4 py-3 opacity-40" data-step="POSTPROCESS">
                    <div class="h-3 w-3 rounded-full bg-slate-600" id="step-post-dot"></div>
                    <span class="text-sm text-slate-400">Generating structure files</span>
                </div>
            </div>

            {{-- Fun facts --}}
            <div class="mt-8 rounded-lg border border-slate-800 bg-slate-900/30 p-4">
                <p class="text-xs text-slate-500 mb-1">Did you know?</p>
                <p class="text-sm text-slate-400" id="fun-fact">Proteins are molecular machines — they digest food, move muscles, defend against infections, and copy DNA.</p>
            </div>
        </div>
    </div>

    {{-- COMPLETED state --}}
    <div id="results-section" class="{{ $outputs ? '' : 'hidden' }}">
        {{-- Summary header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white" id="result-title">
                    @if($outputs && ($outputs['protein_metadata'] ?? null))
                        {{ $outputs['protein_metadata']['protein_name'] }}
                    @else
                        Prediction Results
                    @endif
                </h1>
                <p class="mt-1 text-sm text-slate-400" id="result-subtitle">
                    @if($outputs && ($outputs['protein_metadata'] ?? null))
                        <span class="italic">{{ $outputs['protein_metadata']['organism'] ?? '' }}</span>
                        @if($outputs['protein_metadata']['uniprot_id'] ?? null)
                            &middot; UniProt: <a href="https://www.uniprot.org/uniprot/{{ $outputs['protein_metadata']['uniprot_id'] }}" target="_blank" class="text-teal-400 hover:underline">{{ $outputs['protein_metadata']['uniprot_id'] }}</a>
                        @endif
                        @if($outputs['protein_metadata']['pdb_id'] ?? null)
                            &middot; PDB: <a href="https://www.rcsb.org/structure/{{ $outputs['protein_metadata']['pdb_id'] }}" target="_blank" class="text-teal-400 hover:underline">{{ $outputs['protein_metadata']['pdb_id'] }}</a>
                        @endif
                    @else
                        Job {{ $jobId }}
                    @endif
                </p>
            </div>
            <div id="confidence-badge-container">
                @if($outputs)
                    <x-confidence-badge :score="$outputs['structural_data']['confidence']['plddt_mean'] ?? 0" />
                @endif
            </div>
        </div>

        {{-- Main grid: 3D viewer + sidebar --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- 3D Viewer --}}
            <div class="lg:col-span-2">
                <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-800 px-4 py-3">
                        <h2 class="text-sm font-semibold text-white">3D Structure</h2>
                        <div class="flex items-center gap-1" id="viewer-controls">
                            <button onclick="setViewerStyle('cartoon')" class="rounded px-2.5 py-1 text-xs font-medium bg-teal-600 text-white" id="btn-cartoon">Cartoon</button>
                            <button onclick="setViewerStyle('stick')" class="rounded px-2.5 py-1 text-xs font-medium text-slate-400 hover:bg-slate-700 hover:text-white" id="btn-stick">Stick</button>
                            <button onclick="setViewerStyle('sphere')" class="rounded px-2.5 py-1 text-xs font-medium text-slate-400 hover:bg-slate-700 hover:text-white" id="btn-sphere">Sphere</button>
                            <span class="mx-1 text-slate-700">|</span>
                            <button onclick="toggleSpin()" class="rounded px-2.5 py-1 text-xs font-medium text-slate-400 hover:bg-slate-700 hover:text-white" id="btn-spin">Spin</button>
                            <button onclick="resetViewer()" class="rounded px-2.5 py-1 text-xs font-medium text-slate-400 hover:bg-slate-700 hover:text-white">Reset</button>
                        </div>
                    </div>
                    <div id="viewer-container" class="h-[500px] w-full bg-slate-950"></div>
                    {{-- pLDDT color legend --}}
                    <div class="flex items-center justify-center gap-4 border-t border-slate-800 px-4 py-2.5 text-xs text-slate-400">
                        <span class="font-medium">pLDDT Confidence:</span>
                        <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded" style="background:#0053D6"></span> Very high (&gt;90)</span>
                        <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded" style="background:#65CBF3"></span> High (70-90)</span>
                        <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded" style="background:#FFDB13"></span> Medium (50-70)</span>
                        <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded" style="background:#FF7D45"></span> Low (&lt;50)</span>
                    </div>
                </div>

                {{-- pLDDT per-residue chart --}}
                <div class="mt-6 rounded-xl border border-slate-800 bg-slate-900 p-4">
                    <h2 class="mb-3 text-sm font-semibold text-white">
                        pLDDT per Residue
                        <x-tooltip text="Each bar represents how confident AlphaFold2 is about the predicted position of that amino acid. Higher is better. Blue bars (>90) are very reliable. Orange bars (<50) may indicate disordered regions.">
                            <span class="ml-1 cursor-help text-slate-500 font-normal">(?)</span>
                        </x-tooltip>
                    </h2>
                    <canvas id="plddt-chart" class="w-full" height="120"></canvas>
                </div>

                {{-- PAE Heatmap --}}
                <div class="mt-6 rounded-xl border border-slate-800 bg-slate-900 p-4">
                    <h2 class="mb-3 text-sm font-semibold text-white">
                        Predicted Aligned Error (PAE)
                        <x-tooltip text="This heatmap shows how confident AlphaFold2 is about the relative positions of each pair of amino acids. Dark colors mean high confidence. Light/yellow regions between domains indicate uncertain relative orientation.">
                            <span class="ml-1 cursor-help text-slate-500 font-normal">(?)</span>
                        </x-tooltip>
                    </h2>
                    <div class="flex items-start gap-4">
                        <div class="relative flex-1">
                            <canvas id="pae-heatmap" class="w-full rounded"></canvas>
                            <div id="pae-tooltip" class="pointer-events-none absolute hidden rounded bg-slate-800 px-2 py-1 text-xs text-white shadow-lg border border-slate-700"></div>
                        </div>
                        {{-- Color scale --}}
                        <div class="flex flex-col items-center gap-1 text-xs text-slate-500">
                            <span>0 A</span>
                            <div class="h-32 w-4 rounded" style="background: linear-gradient(to bottom, #0d4a3e, #14b8a6, #fbbf24, #ffffff)"></div>
                            <span id="pae-max-label">30 A</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar panels --}}
            <div class="space-y-6">
                {{-- Confidence summary --}}
                <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                    <h2 class="mb-3 text-sm font-semibold text-white">Confidence Summary</h2>
                    <div class="space-y-2" id="confidence-summary">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Average pLDDT</span>
                            <span class="font-semibold text-white" id="plddt-mean">—</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Mean PAE</span>
                            <span class="font-semibold text-white" id="mean-pae">—</span>
                        </div>
                        <div class="mt-3 space-y-1.5" id="plddt-histogram">
                            {{-- Filled by JS --}}
                        </div>
                    </div>
                </div>

                {{-- Biological data --}}
                <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                    <h2 class="mb-3 text-sm font-semibold text-white">Biological Properties</h2>
                    <div class="space-y-3" id="bio-data">
                        {{-- Filled by JS --}}
                        <p class="text-sm text-slate-500">Loading...</p>
                    </div>
                </div>

                {{-- Secondary Structure --}}
                <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                    <h2 class="mb-3 text-sm font-semibold text-white">Secondary Structure</h2>
                    <canvas id="secondary-structure-chart" class="mx-auto" width="160" height="160"></canvas>
                    <div class="mt-3 flex justify-center gap-4 text-xs text-slate-400" id="ss-legend"></div>
                </div>

                {{-- HPC Accounting --}}
                <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                    <h2 class="mb-3 text-sm font-semibold text-white">
                        HPC Resources
                        <x-tooltip text="Simulated computational resources consumed by this prediction on the CESGA Finis Terrae III supercomputer.">
                            <span class="ml-1 cursor-help text-slate-500 font-normal">(?)</span>
                        </x-tooltip>
                    </h2>
                    <div class="space-y-2" id="accounting-data">
                        <p class="text-sm text-slate-500">Loading...</p>
                    </div>
                </div>

                {{-- Downloads --}}
                <div class="rounded-xl border border-slate-800 bg-slate-900 p-4">
                    <h2 class="mb-3 text-sm font-semibold text-white">Downloads</h2>
                    <div class="space-y-2" id="downloads">
                        <button onclick="downloadFile('pdb')" class="flex w-full items-center gap-2 rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-slate-300 transition-colors hover:border-teal-500 hover:text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Download PDB
                        </button>
                        <button onclick="downloadFile('cif')" class="flex w-full items-center gap-2 rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-slate-300 transition-colors hover:border-teal-500 hover:text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Download mmCIF
                        </button>
                    </div>
                </div>

                {{-- Submit another --}}
                <a href="{{ route('jobs.create') }}" class="block rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-center text-sm font-medium text-slate-300 transition-colors hover:border-teal-500 hover:text-white">
                    Submit Another Sequence
                </a>
            </div>
        </div>
    </div>

    {{-- FAILED state --}}
    <div id="error-section" class="hidden">
        <div class="mx-auto max-w-lg py-16 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-500/20">
                <svg class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.834-2.694-.834-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white">Prediction Failed</h2>
            <p class="mt-2 text-slate-400" id="error-message">An error occurred during prediction.</p>
            <a href="{{ route('jobs.create') }}" class="mt-6 inline-block rounded-lg bg-teal-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-teal-500">
                Try Again
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
            // Cold start — retry
            setTimeout(poll, 5000);
        }
    };

    setTimeout(poll, 2000);
}

function updateProgressUI(status) {
    const steps = document.querySelectorAll('#progress-steps > div');
    const title = document.getElementById('progress-title');
    const subtitle = document.getElementById('progress-subtitle');

    if (status === 'PENDING') {
        title.textContent = 'Waiting in queue...';
        subtitle.textContent = 'Your job is queued on the CESGA supercomputer. GPU resources will be allocated shortly.';
    } else if (status === 'RUNNING') {
        title.textContent = 'AlphaFold2 is running...';
        subtitle.textContent = 'The neural network is predicting the 3D structure of your protein.';
        steps[0].classList.remove('opacity-40');
        steps[0].querySelector('div').classList.remove('animate-pulse');
        steps[0].querySelector('div').classList.replace('bg-amber-400', 'bg-emerald-400');
        steps[1].classList.remove('opacity-40');
        steps[1].querySelector('div').classList.add('animate-pulse');
        steps[1].querySelector('div').classList.replace('bg-slate-600', 'bg-amber-400');
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

    // Title
    if (meta) {
        document.getElementById('result-title').textContent = meta.protein_name;
        let sub = `<em>${meta.organism || ''}</em>`;
        if (meta.uniprot_id) sub += ` &middot; UniProt: <a href="https://www.uniprot.org/uniprot/${meta.uniprot_id}" target="_blank" class="text-teal-400 hover:underline">${meta.uniprot_id}</a>`;
        if (meta.pdb_id) sub += ` &middot; PDB: <a href="https://www.rcsb.org/structure/${meta.pdb_id}" target="_blank" class="text-teal-400 hover:underline">${meta.pdb_id}</a>`;
        document.getElementById('result-subtitle').innerHTML = sub;
    }

    // Badge
    document.getElementById('confidence-badge-container').innerHTML = buildConfidenceBadge(confidence.plddt_mean);

    // 3D Viewer
    init3DViewer(structural.pdb_file, confidence.plddt_per_residue);

    // pLDDT chart
    drawPlddtChart(confidence.plddt_per_residue);

    // PAE heatmap
    if (confidence.pae_matrix) {
        drawPaeHeatmap(confidence.pae_matrix);
    }

    // Confidence summary
    document.getElementById('plddt-mean').textContent = confidence.plddt_mean.toFixed(1);
    document.getElementById('mean-pae').textContent = (confidence.mean_pae || 0).toFixed(1) + ' A';
    renderPlddtHistogram(confidence.plddt_histogram);

    // Biological data
    renderBioData(bio);

    // Secondary structure
    if (bio.secondary_structure_prediction) {
        drawSecondaryStructureChart(bio.secondary_structure_prediction);
    }

    // Accounting
    if (currentAccounting) {
        renderAccounting(currentAccounting);
    }
}

// ====== 3D VIEWER ======
function init3DViewer(pdbString, plddtArray) {
    const container = document.getElementById('viewer-container');
    if (!window.$3Dmol) {
        container.innerHTML = '<p class="flex items-center justify-center h-full text-slate-500 text-sm">3D viewer loading... please wait.</p>';
        setTimeout(() => init3DViewer(pdbString, plddtArray), 500);
        return;
    }
    viewer = $3Dmol.createViewer(container, {
        backgroundColor: '#020617',
        antialias: true,
    });
    viewer.addModel(pdbString, 'pdb');

    // Apply pLDDT coloring via B-factor
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

    // Update button states
    ['cartoon', 'stick', 'sphere'].forEach(s => {
        const btn = document.getElementById('btn-' + s);
        if (s === style) {
            btn.className = 'rounded px-2.5 py-1 text-xs font-medium bg-teal-600 text-white';
        } else {
            btn.className = 'rounded px-2.5 py-1 text-xs font-medium text-slate-400 hover:bg-slate-700 hover:text-white';
        }
    });
}

function toggleSpin() {
    if (!viewer) return;
    spinning = !spinning;
    viewer.spin(spinning);
    const btn = document.getElementById('btn-spin');
    btn.className = spinning
        ? 'rounded px-2.5 py-1 text-xs font-medium bg-teal-600 text-white'
        : 'rounded px-2.5 py-1 text-xs font-medium text-slate-400 hover:bg-slate-700 hover:text-white';
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

    // Threshold lines
    [50, 70, 90].forEach(t => {
        const y = h - (t / 100) * h;
        ctx.strokeStyle = '#334155';
        ctx.lineWidth = 0.5;
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(w, y);
        ctx.stroke();
    });

    // Bars
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
    const size = Math.min(400, canvas.parentElement.clientWidth - 60);
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

    document.getElementById('pae-max-label').textContent = maxVal.toFixed(0) + ' A';

    const imageData = ctx.createImageData(n, n);
    for (let i = 0; i < n; i++) {
        for (let j = 0; j < n; j++) {
            const val = matrix[i][j] / maxVal;
            const idx = (i * n + j) * 4;
            // Green to white
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

    // Hover tooltip
    canvas.addEventListener('mousemove', (e) => {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const i = Math.floor(y / cellSize);
        const j = Math.floor(x / cellSize);
        if (i >= 0 && i < n && j >= 0 && j < n) {
            tooltip.textContent = `Residue ${i + 1} vs ${j + 1}: ${matrix[i][j].toFixed(2)} A`;
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
        { label: 'Very high (>90)', count: hist.very_high || 0, color: PLDDT_COLORS.veryHigh },
        { label: 'High (70-90)', count: hist.high || 0, color: PLDDT_COLORS.high },
        { label: 'Medium (50-70)', count: hist.medium || 0, color: PLDDT_COLORS.medium },
        { label: 'Low (<50)', count: hist.low || 0, color: PLDDT_COLORS.low },
    ];

    container.innerHTML = items.map(item => {
        const pct = total > 0 ? (item.count / total * 100).toFixed(0) : 0;
        return `<div>
            <div class="flex justify-between text-xs mb-0.5">
                <span class="text-slate-400">${item.label}</span>
                <span class="text-slate-300">${item.count} (${pct}%)</span>
            </div>
            <div class="h-1.5 rounded-full bg-slate-800 overflow-hidden">
                <div class="h-full rounded-full" style="width:${pct}%; background:${item.color}"></div>
            </div>
        </div>`;
    }).join('');
}

// ====== BIO DATA ======
function renderBioData(bio) {
    if (!bio) return;
    const container = document.getElementById('bio-data');
    let html = '';

    // Solubility
    const solColor = bio.solubility_score >= 50 ? 'text-emerald-400' : 'text-amber-400';
    html += `<div class="flex justify-between text-sm">
        <span class="text-slate-400">Solubility</span>
        <span class="${solColor} font-medium">${bio.solubility_score?.toFixed(1) ?? '—'}/100 (${bio.solubility_prediction || '—'})</span>
    </div>`;

    // Stability
    const stabColor = bio.stability_status === 'stable' ? 'text-emerald-400' : 'text-amber-400';
    html += `<div class="flex justify-between text-sm">
        <span class="text-slate-400">Instability Index</span>
        <span class="${stabColor} font-medium">${bio.instability_index?.toFixed(1) ?? '—'} (${bio.stability_status || '—'})</span>
    </div>`;

    // Toxicity
    if (bio.toxicity_alerts && bio.toxicity_alerts.length > 0) {
        html += `<div class="mt-2"><span class="text-xs font-medium text-red-400">Toxicity Alerts:</span>
            <div class="mt-1 flex flex-wrap gap-1">${bio.toxicity_alerts.map(a => `<span class="rounded-full border border-red-500/30 bg-red-500/10 px-2 py-0.5 text-xs text-red-400">${a}</span>`).join('')}</div>
        </div>`;
    } else {
        html += `<div class="flex justify-between text-sm">
            <span class="text-slate-400">Toxicity</span>
            <span class="text-emerald-400 font-medium">No alerts</span>
        </div>`;
    }

    // Allergenicity
    if (bio.allergenicity_alerts && bio.allergenicity_alerts.length > 0) {
        html += `<div class="mt-2"><span class="text-xs font-medium text-amber-400">Allergenicity Alerts:</span>
            <div class="mt-1 flex flex-wrap gap-1">${bio.allergenicity_alerts.map(a => `<span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs text-amber-400">${a}</span>`).join('')}</div>
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
        { label: 'Helix', pct: ss.helix_percent || 0, color: '#f43f5e' },
        { label: 'Strand', pct: ss.strand_percent || 0, color: '#3b82f6' },
        { label: 'Coil', pct: ss.coil_percent || 0, color: '#6b7280' },
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

    // Center text
    ctx.fillStyle = '#e2e8f0';
    ctx.font = 'bold 14px Inter, sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('Structure', cx, cy - 6);
    ctx.font = '11px Inter, sans-serif';
    ctx.fillStyle = '#94a3b8';
    ctx.fillText('Prediction', cx, cy + 10);

    // Legend
    const legend = document.getElementById('ss-legend');
    legend.innerHTML = data.map(d =>
        `<span class="flex items-center gap-1"><span class="inline-block h-2.5 w-2.5 rounded" style="background:${d.color}"></span>${d.label} ${d.pct.toFixed(0)}%</span>`
    ).join('');
}

// ====== ACCOUNTING ======
function renderAccounting(acc) {
    if (!acc || !acc.accounting) return;
    const a = acc.accounting;
    const container = document.getElementById('accounting-data');
    container.innerHTML = `
        <div class="grid grid-cols-2 gap-2 text-sm">
            <span class="text-slate-400">CPU Hours</span>
            <span class="text-white font-medium text-right">${a.cpu_hours?.toFixed(4) ?? '—'}</span>
            <span class="text-slate-400">GPU Hours</span>
            <span class="text-white font-medium text-right">${a.gpu_hours?.toFixed(4) ?? '—'}</span>
            <span class="text-slate-400">Memory (GB&middot;h)</span>
            <span class="text-white font-medium text-right">${a.memory_gb_hours?.toFixed(3) ?? '—'}</span>
            <span class="text-slate-400">Wall Time</span>
            <span class="text-white font-medium text-right">${a.total_wall_time_seconds ?? '—'}s</span>
        </div>
        <div class="mt-3 space-y-1.5">
            ${buildEfficiencyBar('CPU Efficiency', a.cpu_efficiency_percent)}
            ${buildEfficiencyBar('GPU Efficiency', a.gpu_efficiency_percent)}
            ${buildEfficiencyBar('Memory Efficiency', a.memory_efficiency_percent)}
        </div>
    `;
}

function buildEfficiencyBar(label, pct) {
    if (pct == null) return '';
    const color = pct >= 80 ? '#10b981' : pct >= 50 ? '#f59e0b' : '#ef4444';
    return `<div>
        <div class="flex justify-between text-xs mb-0.5">
            <span class="text-slate-400">${label}</span>
            <span class="text-slate-300">${pct.toFixed(1)}%</span>
        </div>
        <div class="h-1.5 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full rounded-full" style="width:${pct}%; background:${color}"></div>
        </div>
    </div>`;
}

function buildConfidenceBadge(score) {
    score = parseFloat(score).toFixed(1);
    let cls, label;
    if (score >= 90) { cls = 'bg-[#0053D6]/20 text-[#65CBF3] border-[#0053D6]/40'; label = 'Very High'; }
    else if (score >= 70) { cls = 'bg-sky-500/20 text-sky-300 border-sky-500/40'; label = 'High'; }
    else if (score >= 50) { cls = 'bg-amber-500/20 text-amber-300 border-amber-500/40'; label = 'Medium'; }
    else { cls = 'bg-orange-500/20 text-orange-300 border-orange-500/40'; label = 'Low'; }
    return `<span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold ${cls}">pLDDT ${score} — ${label}</span>`;
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
