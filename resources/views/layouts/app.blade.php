<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Proteinux') · Predicción de estructuras proteicas</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600,700|ibm-plex-mono:400,500,600|ibm-plex-serif:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://3Dmol.org/build/3Dmol-min.js" defer></script>

    @stack('head')
</head>
<body class="min-h-screen antialiased">

    {{-- Banda superior de estado --}}
    <div class="border-b border-ink-300 bg-ink-100/70 backdrop-blur">
        <div class="mx-auto flex max-w-[1400px] items-center justify-between px-4 py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5">
                    <span class="status-dot bg-signal-mint shadow-[0_0_6px_rgba(13,148,136,0.55)]"></span>
                    <span class="text-ink-700">nodo · cesga.ft3</span>
                </span>
                <span class="hidden sm:inline text-ink-400">|</span>
                <span class="hidden sm:inline">pipeline <span class="text-ink-800">af2-proteinux/2.3.1</span></span>
                <span class="hidden md:inline text-ink-400">|</span>
                <span class="hidden md:inline">gpu <span class="text-ink-800">a100/40gb</span></span>
            </div>
            <div class="flex items-center gap-4">
                <span class="hidden sm:inline">{{ now()->format('Y-m-d') }}<span class="text-ink-400"> · </span>{{ now()->format('H:i') }} <span class="text-ink-400">utc</span></span>
                <span class="text-ink-400 hidden sm:inline">|</span>
                <span>impacthon <span class="text-signal-mint">2026</span></span>
            </div>
        </div>
    </div>

    {{-- Navegación --}}
    <nav class="sticky top-0 z-50 border-b border-ink-300 bg-ink-50/85 backdrop-blur-lg">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logo/LogoProteinUX.svg') }}" alt="Proteinux" class="h-9 w-auto">
                    <span class="hidden font-mono text-[10px] uppercase tracking-[0.18em] text-ink-500 sm:inline">predicción de estructuras · v0.1</span>
                </a>

                <div class="flex items-center gap-1">
                    <a href="{{ route('home') }}"
                       class="px-3 py-2 font-mono text-[11px] uppercase tracking-[0.14em] transition-colors {{ request()->routeIs('home') ? 'text-signal-mint' : 'text-ink-700 hover:text-ink-900' }}">
                        / inicio
                    </a>
                    <a href="{{ route('proteins.index') }}"
                       class="px-3 py-2 font-mono text-[11px] uppercase tracking-[0.14em] transition-colors {{ request()->routeIs('proteins.*') ? 'text-signal-mint' : 'text-ink-700 hover:text-ink-900' }}">
                        / catálogo
                    </a>
                    <a href="{{ route('jobs.create') }}" class="ml-3 btn-primary">
                        <span class="status-dot bg-signal-mint"></span>
                        enviar trabajo
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Mensajes flash --}}
    @if(session('success'))
        <div class="mx-auto max-w-[1400px] px-4 pt-4 sm:px-6 lg:px-8">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    @if($errors->has('api'))
        <div class="mx-auto max-w-[1400px] px-4 pt-4 sm:px-6 lg:px-8">
            <x-alert type="error" :message="$errors->first('api')" />
        </div>
    @endif

    {{-- Contenido principal --}}
    <main>
        @yield('content')
    </main>

    {{-- Pie --}}
    <footer class="mt-16 border-t border-ink-300 bg-ink-100/60">
        <div class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid gap-6 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-500 sm:grid-cols-3">
                <div>
                    <div class="text-ink-800">proteinux</div>
                    <div class="mt-1">impacthon 2026 · cátedra camelia</div>
                    <div class="mt-1">medicina personalizada</div>
                </div>
                <div>
                    <div class="text-ink-800">infraestructura</div>
                    <div class="mt-1">cesga finis terrae iii</div>
                    <div class="mt-1">alphafold2 · 3dmol.js · laravel 13</div>
                </div>
                <div class="sm:text-right">
                    <div class="text-ink-800">build</div>
                    <div class="mt-1">{{ substr(md5(config('app.name').now()->format('Y-m-d')), 0, 12) }}</div>
                    <div class="mt-1">{{ now()->format('Y-m-d') }} · acceso abierto</div>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
