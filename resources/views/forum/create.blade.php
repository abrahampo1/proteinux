@extends('layouts.app')

@section('title', 'Nuevo hilo')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">

    {{-- Cabecera --}}
    <header class="mb-8 border-b border-ink-300 pb-6 sm:mb-10">
        <div class="label-tag">foro · nuevo hilo</div>
        <h1 class="mt-3 font-serif text-3xl text-ink-900 sm:text-4xl">Nuevo hilo</h1>
        <p class="mt-3 max-w-2xl font-serif text-sm leading-relaxed text-ink-600">
            Inicia una discusi&#243;n sobre prote&#237;nas, predicciones o biolog&#237;a estructural.
            Opcionalmente puedes vincular el hilo a una predicci&#243;n existente.
        </p>
    </header>

    @if(!empty($prefilledReference))
        <div class="mb-8 flex items-center gap-3 border border-signal-mint/40 bg-signal-mint/5 p-4">
            <span class="status-dot bg-signal-mint"></span>
            <p class="font-mono text-[11px] uppercase tracking-wider text-signal-mint-deep">
                vinculado a prote&#237;na: <span class="text-ink-900">{{ $prefilledReference }}</span>
            </p>
        </div>
    @endif

    <form action="{{ route('forum.store') }}" method="POST" class="space-y-8 sm:space-y-10">
        @csrf

        @if(!empty($prefilledReference))
            <input type="hidden" name="protein_reference" value="{{ $prefilledReference }}">
        @endif

        {{-- T&#237;tulo --}}
        <fieldset>
            <legend class="label-tag mb-2">&sect; 1 &mdash; t&#237;tulo</legend>
            <input type="text" name="title" id="title"
                   value="{{ old('title', $prefilledTitle ?? '') }}"
                   class="input-lab @error('title') !border-signal-rust @enderror"
                   placeholder="T&#237;tulo del hilo"
                   required>
            @error('title')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Cuerpo --}}
        <fieldset>
            <legend class="label-tag mb-2">&sect; 2 &mdash; contenido</legend>
            <textarea name="body" id="body" rows="8" required
                      class="input-lab leading-relaxed @error('body') !border-signal-rust @enderror"
                      placeholder="Escribe el contenido de tu hilo...">{{ old('body') }}</textarea>
            @error('body')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Prote&#237;na vinculada --}}
        <fieldset>
            <legend class="label-tag mb-2">&sect; 3 &mdash; prote&#237;na vinculada · opcional</legend>
            <select name="predicted_job_id" id="predicted_job_id" class="input-lab">
                <option value="">&mdash; sin vincular a predicci&#243;n &mdash;</option>
                @foreach($predictedJobs as $job)
                    <option value="{{ $job->id }}" @selected(old('predicted_job_id', $preselectedJobId ?? '') == $job->id)>
                        {{ $job->displayName() }} &middot; {{ $job->organism ?? 'sin organismo' }}
                    </option>
                @endforeach
            </select>
            @error('predicted_job_id')
                <p class="mt-2 font-mono text-[11px] uppercase tracking-wider text-signal-rust">! {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Enviar --}}
        <div class="border-t border-ink-300 pt-6">
            <button type="submit" class="btn-primary w-full justify-center !py-4 !text-sm">
                &rarr; publicar hilo
            </button>
            <p class="mt-3 text-center font-mono text-[10px] uppercase tracking-wider text-ink-400">
                ser&#225;s redirigido al hilo una vez creado
            </p>
        </div>
    </form>
</div>
@endsection
