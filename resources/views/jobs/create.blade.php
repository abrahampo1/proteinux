@extends('layouts.app')

@section('title', 'Submit Prediction Job')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">

    {{-- Header --}}
    <header class="mb-10 border-b border-ink-700 pb-6">
        <div class="label-tag">section 04 · job submission</div>
        <h1 class="mt-3 font-serif text-4xl text-ink-50">Submit prediction</h1>
        <p class="mt-3 max-w-2xl font-serif text-sm leading-relaxed text-ink-300">
            Paste a single-chain protein sequence in FASTA format. The job will be
            dispatched to the GPU partition of CESGA Finis Terrae&nbsp;III and the
            structure returned with full annotation.
        </p>
    </header>

    <form action="{{ route('jobs.store') }}" method="POST" id="submit-form" class="space-y-10">
        @csrf

        {{-- Sample selector --}}
        @if(!empty($samples))
        <fieldset>
            <legend class="label-tag mb-2">§ 1 — quick start</legend>
            <select id="sample-selector" class="input-lab">
                <option value="">— select a reference protein —</option>
                @foreach($samples as $sample)
                    <option value="{{ $sample['fasta_ready'] ?? $sample['fasta_sequence'] ?? '' }}"
                            data-name="{{ $sample['protein_name'] ?? $sample['protein_id'] ?? 'protein' }}">
                        {{ $sample['protein_name'] ?? $sample['protein_id'] }} · {{ $sample['length'] ?? '?' }} aa · {{ $sample['organism'] ?? '' }}
                    </option>
                @endforeach
            </select>
            <p class="mt-2 font-mono text-[10px] uppercase tracking-wider text-ink-500">
                pre-fills the sequence and filename below
            </p>
        </fieldset>
        @endif

        {{-- FASTA --}}
        <fieldset>
            <div class="mb-2 flex items-center justify-between">
                <legend class="label-tag">§ 2 — fasta sequence <sup class="text-signal-mint">[1]</sup></legend>
                <span id="char-count" class="font-mono text-[10px] uppercase tracking-wider text-ink-500 tabular-nums">0 chars</span>
            </div>
            <textarea name="fasta_sequence" id="fasta_sequence" rows="10"
                      class="input-lab leading-relaxed @error('fasta_sequence') !border-signal-rust @enderror"
                      placeholder=">sp|P0CG47|UBQ_HUMAN Ubiquitin OS=Homo sapiens&#10;MQIFVKTLTGKTITLEVEPSDTIENVKAKIQDKEGIPPDQQRLIFAGKQLEDGRTLSDYNIQKESTLHLVLRLRGG"
            >{{ old('fasta_sequence', $prefillFasta ?? '') }}</textarea>
            @error('fasta_sequence')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror

            <details class="mt-3 border border-ink-700 bg-ink-900/40">
                <summary class="cursor-pointer px-4 py-2 font-mono text-[11px] uppercase tracking-wider text-ink-300 hover:text-signal-mint">
                    [?] what is fasta format
                </summary>
                <div class="border-t border-ink-700 px-4 py-3 font-serif text-[13px] leading-relaxed text-ink-300">
                    A plain-text format for biological sequences. The first line begins with
                    <code class="font-mono text-signal-mint">&gt;</code> and contains an identifier;
                    subsequent lines hold the amino-acid sequence in single-letter code.
                    <pre class="mt-3 border border-ink-700 bg-ink-950 p-3 font-mono text-[11px] text-signal-mint">&gt;protein_name description
MQIFVKTLTGKTITLEVEPSDTIENK...</pre>
                </div>
            </details>
        </fieldset>

        {{-- Filename --}}
        <fieldset>
            <legend class="label-tag mb-2">§ 3 — output filename</legend>
            <input type="text" name="fasta_filename" id="fasta_filename"
                   value="{{ old('fasta_filename', $prefillFilename ?? '') }}"
                   class="input-lab @error('fasta_filename') !border-signal-rust @enderror"
                   placeholder="my_protein.fasta">
            @error('fasta_filename')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- HPC params --}}
        <fieldset class="border border-ink-700">
            <legend class="ml-3 px-2">
                <span class="label-tag">§ 4 — hpc parameters · optional</span>
            </legend>
            <div class="grid grid-cols-3 gap-px bg-ink-700">
                <label class="bg-ink-900 p-4">
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">gpus · a100</span>
                    <input type="number" name="gpus" id="gpus" value="{{ old('gpus', 1) }}" min="0" max="4"
                           class="mt-1 w-full bg-transparent font-mono text-2xl text-ink-50 tabular-nums focus:outline-none">
                </label>
                <label class="bg-ink-900 p-4">
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">cpus · cores</span>
                    <input type="number" name="cpus" id="cpus" value="{{ old('cpus', 8) }}" min="1" max="64"
                           class="mt-1 w-full bg-transparent font-mono text-2xl text-ink-50 tabular-nums focus:outline-none">
                </label>
                <label class="bg-ink-900 p-4">
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-400">memory · gb</span>
                    <input type="number" name="memory_gb" id="memory_gb" value="{{ old('memory_gb', 32) }}" min="1" max="256" step="0.1"
                           class="mt-1 w-full bg-transparent font-mono text-2xl text-ink-50 tabular-nums focus:outline-none">
                </label>
            </div>
            <p class="border-t border-ink-700 bg-ink-900/40 px-4 py-2 font-mono text-[10px] uppercase tracking-wider text-ink-500">
                defaults are calibrated for proteins under 1 024 residues
            </p>
        </fieldset>

        {{-- Submit --}}
        <div class="border-t border-ink-700 pt-6">
            <button type="submit" id="submit-btn" class="btn-primary w-full justify-center !py-4 !text-sm">
                <span id="btn-text">→ dispatch to ft3 cluster</span>
                <span id="btn-loading" class="hidden items-center justify-center gap-2">
                    <x-loading-spinner size="sm" />
                    submitting…
                </span>
            </button>
            <p class="mt-3 text-center font-mono text-[10px] uppercase tracking-wider text-ink-500">
                you will be redirected to the live job page
            </p>
        </div>
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

    function updateCharCount() {
        charCount.textContent = textarea.value.length + ' chars';
    }
    textarea.addEventListener('input', updateCharCount);
    updateCharCount();

    textarea.addEventListener('blur', function() {
        if (!filenameInput.value && textarea.value.startsWith('>')) {
            const header = textarea.value.split('\n')[0].substring(1).trim();
            const name = header.split(/[\s|]/)[0] || 'protein';
            filenameInput.value = name.toLowerCase().replace(/[^a-z0-9]/g, '_') + '.fasta';
        }
    });

    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        btnLoading.classList.add('inline-flex');
    });
});
</script>
@endpush
