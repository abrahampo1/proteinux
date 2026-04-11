@extends('layouts.app')

@section('title', 'Enviar trabajo de predicción')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">

    {{-- Cabecera --}}
    <header class="mb-8 border-b border-ink-300 pb-6 sm:mb-10">
        <div class="label-tag">sección 04 · envío de trabajo</div>
        <h1 class="mt-3 font-serif text-3xl text-ink-900 sm:text-4xl">Enviar predicción</h1>
        <p class="mt-3 max-w-2xl font-serif text-sm leading-relaxed text-ink-600">
            Pega una secuencia proteica monocadena en formato FASTA. El trabajo se
            despachará a la partición GPU del CESGA Finis Terrae&nbsp;III y la
            estructura se devolverá completamente anotada.
        </p>
    </header>

    <form action="{{ route('jobs.store') }}" method="POST" id="submit-form" class="space-y-8 sm:space-y-10">
        @csrf

        {{-- Selector de muestra --}}
        @if(!empty($samples))
        <fieldset>
            <legend class="label-tag mb-2">§ 1 — inicio rápido</legend>
            <select id="sample-selector" class="input-lab">
                <option value="">— selecciona una proteína de referencia —</option>
                @foreach($samples as $sample)
                    <option value="{{ $sample['fasta_ready'] ?? $sample['fasta_sequence'] ?? '' }}"
                            data-name="{{ $sample['protein_name'] ?? $sample['protein_id'] ?? 'protein' }}">
                        {{ $sample['protein_name'] ?? $sample['protein_id'] }} · {{ $sample['length'] ?? '?' }} aa · {{ $sample['organism'] ?? '' }}
                    </option>
                @endforeach
            </select>
            <p class="mt-2 font-mono text-[10px] uppercase tracking-wider text-ink-400">
                rellena la secuencia y el nombre de archivo de abajo
            </p>
        </fieldset>
        @endif

        {{-- FASTA --}}
        <fieldset>
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <legend class="label-tag">§ 2 — secuencia fasta <sup class="text-signal-mint">[1]</sup></legend>
                <span id="char-count" class="font-mono text-[10px] uppercase tracking-wider tabular-nums text-ink-400">0 caracteres</span>
            </div>
            <textarea name="fasta_sequence" id="fasta_sequence" rows="8"
                      class="input-lab leading-relaxed @error('fasta_sequence') !border-signal-rust @enderror"
                      placeholder=">sp|P0CG47|UBQ_HUMAN Ubiquitin OS=Homo sapiens&#10;MQIFVKTLTGKTITLEVEPSDTIENVKAKIQDKEGIPPDQQRLIFAGKQLEDGRTLSDYNIQKESTLHLVLRLRGG"
            >{{ old('fasta_sequence', $prefillFasta ?? '') }}</textarea>
            @error('fasta_sequence')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror

            <details class="mt-3 border border-ink-300 bg-ink-100/60">
                <summary class="cursor-pointer px-4 py-2 font-mono text-[11px] uppercase tracking-wider text-ink-700 hover:text-signal-mint">
                    [?] qué es el formato fasta
                </summary>
                <div class="border-t border-ink-300 px-4 py-3 font-serif text-[13px] leading-relaxed text-ink-600">
                    Un formato de texto plano para secuencias biológicas. La primera línea empieza por
                    <code class="font-mono text-signal-mint-deep">&gt;</code> y contiene un identificador;
                    las siguientes líneas son la secuencia de aminoácidos en código de una sola letra.
                    <pre class="mt-3 border border-ink-300 bg-ink-100 p-3 font-mono text-[11px] text-signal-mint-deep">&gt;nombre_proteina descripcion
MQIFVKTLTGKTITLEVEPSDTIENK...</pre>
                </div>
            </details>
        </fieldset>

        {{-- Nombre de archivo --}}
        <fieldset>
            <legend class="label-tag mb-2">§ 3 — nombre del archivo de salida</legend>
            <input type="text" name="fasta_filename" id="fasta_filename"
                   value="{{ old('fasta_filename', $prefillFilename ?? '') }}"
                   class="input-lab @error('fasta_filename') !border-signal-rust @enderror"
                   placeholder="mi_proteina.fasta">
            @error('fasta_filename')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Parámetros HPC --}}
        <fieldset class="border border-ink-300">
            <legend class="ml-3 bg-ink-50 px-2">
                <span class="label-tag">§ 4 — parámetros hpc · opcional</span>
            </legend>
            <div class="grid grid-cols-1 gap-px bg-ink-300 sm:grid-cols-3">
                <label class="bg-ink-100 p-4">
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-500">gpus · a100</span>
                    <input type="number" name="gpus" id="gpus" value="{{ old('gpus', 1) }}" min="0" max="4"
                           class="mt-1 w-full bg-transparent font-mono text-xl tabular-nums text-ink-900 focus:outline-none sm:text-2xl">
                </label>
                <label class="bg-ink-100 p-4">
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-500">cpus · núcleos</span>
                    <input type="number" name="cpus" id="cpus" value="{{ old('cpus', 8) }}" min="1" max="64"
                           class="mt-1 w-full bg-transparent font-mono text-xl tabular-nums text-ink-900 focus:outline-none sm:text-2xl">
                </label>
                <label class="bg-ink-100 p-4">
                    <span class="font-mono text-[10px] uppercase tracking-wider text-ink-500">memoria · gb</span>
                    <input type="number" name="memory_gb" id="memory_gb" value="{{ old('memory_gb', 32) }}" min="1" max="256" step="0.1"
                           class="mt-1 w-full bg-transparent font-mono text-xl tabular-nums text-ink-900 focus:outline-none sm:text-2xl">
                </label>
            </div>
            <p class="border-t border-ink-300 bg-ink-100/60 px-4 py-2 font-mono text-[10px] uppercase leading-relaxed tracking-wider text-ink-400">
                los valores por defecto están calibrados para proteínas de menos de 1 024 residuos
            </p>
        </fieldset>

        {{-- Enviar --}}
        <div class="border-t border-ink-300 pt-6">
            <button type="submit" id="submit-btn" class="btn-primary w-full justify-center !py-4 !text-sm">
                <span id="btn-text">→ despachar al clúster ft3</span>
                <span id="btn-loading" class="hidden items-center justify-center gap-2">
                    <x-loading-spinner size="sm" />
                    enviando…
                </span>
            </button>
            <p class="mt-3 text-center font-mono text-[10px] uppercase tracking-wider text-ink-400">
                serás redirigido a la página del trabajo en vivo
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
        charCount.textContent = textarea.value.length + ' caracteres';
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
