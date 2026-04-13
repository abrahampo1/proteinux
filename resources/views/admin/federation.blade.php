@extends('layouts.app')

@section('title', 'Federación')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">

    <header class="mb-8 border-b border-ink-300 pb-6 sm:mb-10">
        <div class="label-tag">admin · federación</div>
        <h1 class="mt-3 font-serif text-3xl text-ink-900 sm:text-4xl">Red federada</h1>
        <p class="mt-3 max-w-2xl font-serif text-sm leading-relaxed text-ink-600">
            Gestión de la red federada de instancias Proteinux. Conecta con otros nodos
            para compartir predicciones, hilos del foro y documentos científicos.
        </p>
    </header>

    {{-- Local instance info --}}
    <div class="panel mb-8 px-5 py-5 sm:px-6 sm:py-6">
        <div class="label-tag mb-3">instancia local</div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <span class="block font-mono text-[10px] uppercase tracking-wider text-ink-500">dominio</span>
                <span class="mt-1 block font-mono text-sm text-ink-900">{{ $localInfo['domain'] }}</span>
            </div>
            <div>
                <span class="block font-mono text-[10px] uppercase tracking-wider text-ink-500">nombre</span>
                <span class="mt-1 block font-mono text-sm text-ink-900">{{ $localInfo['name'] }}</span>
            </div>
            <div>
                <span class="block font-mono text-[10px] uppercase tracking-wider text-ink-500">versión</span>
                <span class="mt-1 block font-mono text-sm text-ink-900">{{ $localInfo['proteinux_version'] }}</span>
            </div>
            <div>
                <span class="block font-mono text-[10px] uppercase tracking-wider text-ink-500">estado</span>
                <span class="mt-1 inline-flex items-center gap-1.5 font-mono text-sm">
                    @if($federationEnabled)
                        <span class="status-dot bg-signal-mint shadow-[0_0_6px_rgba(13,148,136,0.55)]"></span>
                        <span class="text-signal-mint-deep">activa</span>
                    @else
                        <span class="status-dot bg-ink-400"></span>
                        <span class="text-ink-500">desactivada</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- Connect to new instance --}}
    <div class="panel mb-8 px-5 py-5 sm:px-6 sm:py-6">
        <div class="label-tag mb-3">conectar nueva instancia</div>
        <form action="{{ route('admin.federation.connect') }}" method="POST" class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-[250px]">
                <label for="domain" class="mb-1 block font-mono text-[11px] uppercase tracking-wider text-ink-600">
                    dominio remoto
                </label>
                <input type="text"
                       id="domain"
                       name="domain"
                       placeholder="proteinux.example.org"
                       required
                       value="{{ old('domain') }}"
                       class="input-lab w-full">
                @error('domain')
                    <p class="mt-1 font-mono text-[10px] text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn-primary">
                <span class="status-dot bg-signal-mint"></span>
                conectar
            </button>
        </form>
    </div>

    {{-- Peer list --}}
    <div class="label-tag mb-3">instancias federadas · {{ $instances->count() }}</div>

    @if($instances->isEmpty())
        <div class="panel px-5 py-8 text-center sm:px-6">
            <p class="font-mono text-[11px] uppercase tracking-wider text-ink-400">
                no hay instancias federadas configuradas
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($instances as $instance)
                <div class="panel px-5 py-4 sm:px-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm font-medium text-ink-900">{{ $instance->domain }}</span>
                                @php
                                    $statusColors = [
                                        'active' => 'bg-signal-mint/10 text-signal-mint-deep border-signal-mint/30',
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-300',
                                        'suspended' => 'bg-orange-50 text-orange-700 border-orange-300',
                                        'rejected' => 'bg-red-50 text-red-700 border-red-300',
                                    ];
                                    $colorClass = $statusColors[$instance->status] ?? 'bg-ink-100 text-ink-600 border-ink-300';
                                @endphp
                                <span class="inline-block border px-2 py-0.5 font-mono text-[10px] uppercase tracking-wider {{ $colorClass }}">
                                    {{ $instance->status }}
                                </span>
                            </div>
                            <div class="mt-1 font-mono text-[10px] uppercase tracking-wider text-ink-500">
                                {{ $instance->name }}
                                @if($instance->last_seen_at)
                                    · visto {{ $instance->last_seen_at->diffForHumans() }}
                                @endif
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            @if($instance->status === 'pending')
                                <form action="{{ route('admin.federation.accept', $instance) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-primary text-[10px]">aceptar</button>
                                </form>
                                <form action="{{ route('admin.federation.reject', $instance) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-secondary text-[10px]">rechazar</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.federation.destroy', $instance) }}" method="POST" class="inline"
                                  onsubmit="return confirm('¿Eliminar la instancia {{ $instance->domain }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-secondary text-[10px] text-red-600 hover:text-red-800">eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
