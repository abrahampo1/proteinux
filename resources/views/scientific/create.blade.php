@extends('layouts.app')

@section('title', 'Subir documento')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">

    {{-- Cabecera --}}
    <header class="mb-8 border-b border-ink-300 pb-6 sm:mb-10">
        <div class="label-tag">seccion 08 . nuevo documento</div>
        <h1 class="mt-3 font-serif text-3xl text-ink-900 sm:text-4xl">Subir documento</h1>
        <p class="mt-3 max-w-2xl font-serif text-sm leading-relaxed text-ink-600">
            Comparte un documento cientifico con la comunidad. Puedes adjuntar un
            archivo o enlazar un recurso externo (DOI, arXiv, repositorio).
        </p>
    </header>

    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8 sm:space-y-10">
        @csrf

        {{-- Titulo --}}
        <fieldset>
            <legend class="label-tag mb-2">titulo</legend>
            <input type="text" name="title" value="{{ old('title') }}"
                   class="input-lab @error('title') !border-signal-rust @enderror"
                   placeholder="Titulo del documento..."
                   required>
            @error('title')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Descripcion --}}
        <fieldset>
            <legend class="label-tag mb-2">descripcion</legend>
            <textarea name="description" rows="4"
                      class="input-lab @error('description') !border-signal-rust @enderror"
                      placeholder="Descripcion breve del contenido...">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Tipo --}}
        <fieldset>
            <legend class="label-tag mb-2">tipo de documento</legend>
            <select name="type" class="input-lab @error('type') !border-signal-rust @enderror" required>
                <option value="">-- seleccionar tipo --</option>
                <option value="paper" {{ old('type') === 'paper' ? 'selected' : '' }}>Articulo</option>
                <option value="note" {{ old('type') === 'note' ? 'selected' : '' }}>Apunte</option>
                <option value="protocol" {{ old('type') === 'protocol' ? 'selected' : '' }}>Protocolo</option>
                <option value="dataset" {{ old('type') === 'dataset' ? 'selected' : '' }}>Dataset</option>
            </select>
            @error('type')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Archivo --}}
        <fieldset>
            <legend class="label-tag mb-2">archivo (opcional)</legend>
            <input type="file" name="file"
                   accept=".pdf,.doc,.docx,.txt,.md,.csv"
                   class="w-full border border-ink-300 bg-ink-50 px-3 py-2.5 font-mono text-sm text-ink-700 file:mr-3 file:border-0 file:bg-signal-mint/10 file:px-3 file:py-1 file:font-mono file:text-[11px] file:uppercase file:tracking-wider file:text-signal-mint-deep @error('file') !border-signal-rust @enderror">
            <p class="mt-2 font-mono text-[10px] uppercase tracking-wider text-ink-400">
                pdf, doc, docx, txt, md, csv . max 50 mb
            </p>
            @error('file')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- URL externa --}}
        <fieldset>
            <legend class="label-tag mb-2">url externa (opcional)</legend>
            <input type="url" name="url" value="{{ old('url') }}"
                   class="input-lab @error('url') !border-signal-rust @enderror"
                   placeholder="https://doi.org/10.1234/... o https://arxiv.org/abs/...">
            @error('url')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- DOI --}}
        <fieldset>
            <legend class="label-tag mb-2">doi (opcional)</legend>
            <input type="text" name="doi" value="{{ old('doi') }}"
                   class="input-lab @error('doi') !border-signal-rust @enderror"
                   placeholder="10.1234/ejemplo.2026">
            @error('doi')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Proteinas vinculadas --}}
        <fieldset class="border border-ink-300">
            <legend class="ml-3 bg-ink-50 px-2">
                <span class="label-tag">proteinas vinculadas (opcional)</span>
            </legend>
            <div class="max-h-64 overflow-y-auto p-4">
                @if($predictedJobs->isEmpty())
                    <p class="font-mono text-[11px] uppercase tracking-wider text-ink-400">
                        no hay predicciones completadas disponibles
                    </p>
                @else
                    <div class="space-y-2">
                        @foreach($predictedJobs as $job)
                            <label class="flex items-center gap-3 border-b border-dashed border-ink-300 py-2 last:border-b-0">
                                <input type="checkbox" name="protein_ids[]" value="{{ $job->id }}"
                                       {{ in_array($job->id, old('protein_ids', [])) ? 'checked' : '' }}
                                       class="h-4 w-4 border-ink-300 text-signal-mint focus:ring-signal-mint/30">
                                <span class="font-mono text-sm text-ink-900">{{ $job->displayName() }}</span>
                                @if($job->organism)
                                    <span class="font-serif text-xs italic text-ink-500">{{ $job->organism }}</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
            @error('protein_ids')
                <p class="border-t border-ink-300 px-4 py-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Compartir por federacion --}}
        <fieldset>
            <label class="flex items-center gap-3">
                <input type="hidden" name="is_shared" value="0">
                <input type="checkbox" name="is_shared" value="1"
                       {{ old('is_shared') ? 'checked' : '' }}
                       class="h-4 w-4 border-ink-300 text-signal-mint focus:ring-signal-mint/30">
                <span class="font-mono text-sm text-ink-900">Compartir con instancias federadas</span>
            </label>
            <p class="mt-2 pl-7 font-mono text-[10px] uppercase tracking-wider text-ink-400">
                los metadatos se enviaran a las instancias conectadas . los archivos permanecen en esta instancia
            </p>
        </fieldset>

        {{-- Enviar --}}
        <div class="border-t border-ink-300 pt-6">
            <button type="submit" class="btn-primary w-full justify-center !py-4 !text-sm">
                -> subir documento
            </button>
            <p class="mt-3 text-center font-mono text-[10px] uppercase tracking-wider text-ink-400">
                seras redirigido a la pagina del documento
            </p>
        </div>
    </form>
</div>
@endsection
