@extends('layouts.app')

@section('title', 'Ajustes de IA')

@php
    /** @var \App\Support\Ai\AiSettings $settings */
    $active = $settings->activeProvider();

    $providerLabels = [
        'anthropic' => 'Anthropic',
        'openai' => 'OpenAI',
        'gemini' => 'Google Gemini',
    ];

    $providerDocs = [
        'anthropic' => 'https://console.anthropic.com/settings/keys',
        'openai' => 'https://platform.openai.com/api-keys',
        'gemini' => 'https://aistudio.google.com/app/apikey',
    ];
@endphp

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">

    <header class="mb-8 border-b border-ink-300 pb-6 sm:mb-10">
        <div class="label-tag">sección 05 · configuración de ia</div>
        <h1 class="mt-3 font-serif text-3xl text-ink-900 sm:text-4xl">Ajustes de IA</h1>
        <p class="mt-3 max-w-2xl font-serif text-sm leading-relaxed text-ink-600">
            Proteinux puede generar un análisis interpretativo de cada predicción y
            responder preguntas sobre los resultados usando un modelo de lenguaje.
            Trae tu propia API key: soportamos Anthropic, OpenAI y Google Gemini.
            Las keys se cifran con <code class="font-mono text-signal-mint-deep">AES&#8209;256&#8209;GCM</code>
            y se guardan únicamente en tu sesión de navegador.
        </p>
    </header>

    @if(session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <form action="{{ route('settings.ai.update') }}" method="POST" class="space-y-8 sm:space-y-10">
        @csrf

        {{-- Selector de proveedor activo --}}
        <fieldset>
            <legend class="label-tag mb-3">§ 1 — proveedor activo</legend>
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach($providerLabels as $key => $label)
                    @php $hasKey = $settings->hasKeyFor($key); @endphp
                    <label class="relative block cursor-pointer border {{ $active === $key ? 'border-signal-mint bg-signal-mint/5' : 'border-ink-300 bg-ink-50' }} px-4 py-3 transition-colors hover:border-signal-mint">
                        <input type="radio" name="active_provider" value="{{ $key }}"
                               {{ $active === $key ? 'checked' : '' }}
                               class="sr-only">
                        <div class="font-mono text-[11px] uppercase tracking-[0.14em] {{ $active === $key ? 'text-signal-mint-deep' : 'text-ink-700' }}">
                            {{ $label }}
                        </div>
                        <div class="mt-1 font-mono text-[10px] uppercase tracking-wider {{ $hasKey ? 'text-signal-mint' : 'text-ink-400' }}">
                            {{ $hasKey ? '· key configurada' : '· sin key' }}
                        </div>
                    </label>
                @endforeach
            </div>
        </fieldset>

        {{-- Bloques por proveedor --}}
        @foreach($providerLabels as $key => $label)
            @php
                $cfg = $providers[$key] ?? [];
                $masked = $settings->maskedKeyFor($key);
                $model = $settings->modelFor($key);
                $models = $cfg['models'] ?? [];
            @endphp
            <fieldset class="panel px-5 py-5 sm:px-6 sm:py-6">
                <div class="mb-4 flex flex-wrap items-baseline justify-between gap-3">
                    <legend class="label-tag">§ 2.{{ $loop->iteration }} — {{ strtolower($label) }}</legend>
                    <a href="{{ $providerDocs[$key] }}" target="_blank" rel="noopener"
                       class="font-mono text-[10px] uppercase tracking-wider text-ink-400 hover:text-signal-mint">
                        [↗] obtener key
                    </a>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block font-mono text-[11px] uppercase tracking-wider text-ink-600">
                            api key
                        </label>
                        <div class="flex flex-wrap items-center gap-2">
                            <input type="password" name="providers[{{ $key }}][api_key]"
                                   autocomplete="off" spellcheck="false"
                                   placeholder="{{ $masked ?? 'sin configurar' }}"
                                   class="input-lab flex-1 min-w-[220px]">
                            @if($masked)
                                <span class="font-mono text-[10px] uppercase tracking-wider text-signal-mint">[ok]</span>
                            @endif
                        </div>
                        @if($masked)
                            <p class="mt-1 font-mono text-[10px] uppercase tracking-wider text-ink-400">
                                déjalo vacío para conservar la key actual
                            </p>
                        @endif
                    </div>

                    <div>
                        <label class="mb-1 block font-mono text-[11px] uppercase tracking-wider text-ink-600">
                            modelo
                        </label>
                        <input type="text" name="providers[{{ $key }}][model]"
                               value="{{ $model }}"
                               list="models-{{ $key }}"
                               spellcheck="false"
                               class="input-lab">
                        <datalist id="models-{{ $key }}">
                            @foreach($models as $m)
                                <option value="{{ $m }}"></option>
                            @endforeach
                        </datalist>
                        <p class="mt-1 font-mono text-[10px] uppercase tracking-wider text-ink-400">
                            sugerencias: {{ implode(' · ', $models) }}
                        </p>
                    </div>
                </div>
            </fieldset>
        @endforeach

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-ink-300 pt-6">
            <p class="font-mono text-[10px] uppercase tracking-wider text-ink-500">
                las keys no salen de tu sesión · cifrado aes-256-gcm
            </p>
            <button type="submit" class="btn-primary">
                <span class="status-dot bg-signal-mint"></span>
                guardar ajustes
            </button>
        </div>
    </form>

    {{-- Borrado individual por proveedor --}}
    <div class="mt-10 border-t border-ink-300 pt-6">
        <div class="label-tag mb-3">§ 3 — borrar keys guardadas</div>
        <div class="flex flex-wrap gap-2">
            @foreach($providerLabels as $key => $label)
                @if($settings->hasKeyFor($key))
                    <form action="{{ route('settings.ai.destroy', $key) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary"
                                onclick="return confirm('¿Borrar la key de {{ $label }}?')">
                            borrar {{ strtolower($label) }}
                        </button>
                    </form>
                @endif
            @endforeach
            @if(!$settings->hasKeyFor('anthropic') && !$settings->hasKeyFor('openai') && !$settings->hasKeyFor('gemini'))
                <p class="font-mono text-[10px] uppercase tracking-wider text-ink-400">
                    no hay keys guardadas
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
