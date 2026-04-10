@extends('layouts.app')

@section('title', 'Submit Prediction Job')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Submit a Structure Prediction</h1>
        <p class="mt-2 text-slate-400">Paste a protein sequence in FASTA format and our simulated AlphaFold2 pipeline on CESGA Finis Terrae III will predict its 3D structure.</p>
    </div>

    <form action="{{ route('jobs.store') }}" method="POST" class="space-y-6" id="submit-form">
        @csrf

        {{-- Sample protein selector --}}
        @if(!empty($samples))
        <div>
            <label for="sample-selector" class="block text-sm font-medium text-slate-300 mb-1.5">Quick start &mdash; choose a sample protein</label>
            <select id="sample-selector"
                    class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2.5 text-sm text-slate-200 transition-colors focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                <option value="">Select a sample protein...</option>
                @foreach($samples as $sample)
                    <option value="{{ $sample['fasta_ready'] ?? $sample['fasta_sequence'] ?? '' }}"
                            data-name="{{ $sample['protein_name'] ?? $sample['protein_id'] ?? 'protein' }}">
                        {{ $sample['protein_name'] ?? $sample['protein_id'] }} ({{ $sample['length'] ?? '?' }} aa) &mdash; {{ $sample['organism'] ?? '' }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- FASTA Sequence --}}
        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <label for="fasta_sequence" class="block text-sm font-medium text-slate-300">
                    FASTA Sequence
                    <x-tooltip text="FASTA is the standard text format for protein sequences. It starts with a '>' header line followed by the amino acid sequence using single-letter codes (M, Q, I, F, V, K, etc.).">
                        <span class="ml-1 cursor-help text-slate-500">(?)</span>
                    </x-tooltip>
                </label>
                <span id="char-count" class="text-xs text-slate-500">0 characters</span>
            </div>
            <textarea name="fasta_sequence" id="fasta_sequence" rows="8"
                      class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 font-mono text-sm text-slate-200 placeholder-slate-500 transition-colors focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 @error('fasta_sequence') border-red-500 @enderror"
                      placeholder=">sp|P0CG47|UBQ_HUMAN Ubiquitin OS=Homo sapiens&#10;MQIFVKTLTGKTITLEVEPSDTIENVKAKIQDKEGIPPDQQRLIFAGKQLEDGRTLSDYNIQKESTLHLVLRLRGG"
            >{{ old('fasta_sequence', $prefillFasta ?? '') }}</textarea>
            @error('fasta_sequence')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror

            {{-- Help section --}}
            <details class="mt-2">
                <summary class="cursor-pointer text-xs text-teal-400 hover:text-teal-300">What is FASTA format?</summary>
                <div class="mt-2 rounded-lg border border-slate-700 bg-slate-800/50 p-3 text-xs text-slate-400">
                    <p class="mb-2">FASTA is a text format for representing protein or DNA sequences:</p>
                    <pre class="rounded bg-slate-900 p-2 text-emerald-400">&gt;protein_name description
MQIFVKTLTGKTITLEVEPSDTIENK...</pre>
                    <p class="mt-2">The first line starts with <code class="text-teal-400">&gt;</code> and contains an identifier. The following lines contain the amino acid sequence using single-letter codes.</p>
                </div>
            </details>
        </div>

        {{-- Filename --}}
        <div>
            <label for="fasta_filename" class="block text-sm font-medium text-slate-300 mb-1.5">Filename</label>
            <input type="text" name="fasta_filename" id="fasta_filename"
                   value="{{ old('fasta_filename', $prefillFilename ?? '') }}"
                   class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-2.5 text-sm text-slate-200 placeholder-slate-500 transition-colors focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500 @error('fasta_filename') border-red-500 @enderror"
                   placeholder="my_protein.fasta">
            @error('fasta_filename')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Advanced HPC Settings --}}
        <details class="rounded-lg border border-slate-700 bg-slate-800/30">
            <summary class="cursor-pointer px-4 py-3 text-sm font-medium text-slate-300 hover:text-white">
                Advanced HPC Settings
                <span class="ml-1 text-xs text-slate-500">(optional &mdash; sensible defaults are pre-filled)</span>
            </summary>
            <div class="grid grid-cols-3 gap-4 px-4 pb-4 pt-2">
                <div>
                    <label for="gpus" class="block text-xs font-medium text-slate-400 mb-1">
                        GPUs
                        <x-tooltip text="Number of NVIDIA A100 GPUs to allocate. More GPUs can speed up prediction for large proteins.">
                            <span class="cursor-help text-slate-500">(?)</span>
                        </x-tooltip>
                    </label>
                    <input type="number" name="gpus" id="gpus" value="{{ old('gpus', 1) }}" min="0" max="4"
                           class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-200 focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label for="cpus" class="block text-xs font-medium text-slate-400 mb-1">CPUs</label>
                    <input type="number" name="cpus" id="cpus" value="{{ old('cpus', 8) }}" min="1" max="64"
                           class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-200 focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label for="memory_gb" class="block text-xs font-medium text-slate-400 mb-1">Memory (GB)</label>
                    <input type="number" name="memory_gb" id="memory_gb" value="{{ old('memory_gb', 32) }}" min="1" max="256" step="0.1"
                           class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-200 focus:border-teal-500 focus:outline-none">
                </div>
            </div>
        </details>

        {{-- Submit --}}
        <button type="submit" id="submit-btn"
                class="w-full rounded-lg bg-teal-600 px-6 py-3 text-sm font-semibold text-white transition-all hover:bg-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950 disabled:opacity-50 disabled:cursor-not-allowed">
            <span id="btn-text">Submit Prediction Job</span>
            <span id="btn-loading" class="hidden items-center justify-center gap-2">
                <x-loading-spinner size="sm" />
                Submitting...
            </span>
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.getElementById('sample-selector');
    const textarea = document.getElementById('fasta_sequence');
    const filenameInput = document.getElementById('fasta_filename');
    const charCount = document.getElementById('char-count');
    const form = document.getElementById('submit-form');
    const btnText = document.getElementById('btn-text');
    const btnLoading = document.getElementById('btn-loading');
    const submitBtn = document.getElementById('submit-btn');

    // Sample selector
    if (selector) {
        selector.addEventListener('change', function() {
            if (this.value) {
                textarea.value = this.value;
                const name = this.options[this.selectedIndex].dataset.name || 'protein';
                filenameInput.value = name.toLowerCase().replace(/[^a-z0-9]/g, '_') + '.fasta';
                updateCharCount();
            }
        });
    }

    // Character counter
    function updateCharCount() {
        charCount.textContent = textarea.value.length + ' characters';
    }
    textarea.addEventListener('input', updateCharCount);
    updateCharCount();

    // Auto-generate filename from FASTA header
    textarea.addEventListener('blur', function() {
        if (!filenameInput.value && textarea.value.startsWith('>')) {
            const header = textarea.value.split('\n')[0].substring(1).trim();
            const name = header.split(/[\s|]/)[0] || 'protein';
            filenameInput.value = name.toLowerCase().replace(/[^a-z0-9]/g, '_') + '.fasta';
        }
    });

    // Submit loading state
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        btnLoading.classList.add('inline-flex');
    });
});
</script>
@endpush
