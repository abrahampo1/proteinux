<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Proteinux') · Protein Structure Prediction</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600,700|ibm-plex-mono:400,500,600|ibm-plex-serif:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://3Dmol.org/build/3Dmol-min.js" defer></script>

    @stack('head')
</head>
<body class="min-h-screen antialiased">

    {{-- Top status strip --}}
    <div class="border-b border-ink-700 bg-ink-950/80 backdrop-blur">
        <div class="mx-auto flex max-w-[1400px] items-center justify-between px-4 py-1.5 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-400 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5">
                    <span class="status-dot bg-signal-mint shadow-[0_0_6px_var(--color-signal-mint)]"></span>
                    <span class="text-ink-300">node · cesga.ft3</span>
                </span>
                <span class="hidden sm:inline text-ink-500">|</span>
                <span class="hidden sm:inline">pipeline <span class="text-ink-200">af2-proteinux/2.3.1</span></span>
                <span class="hidden md:inline text-ink-500">|</span>
                <span class="hidden md:inline">gpu <span class="text-ink-200">a100/40gb</span></span>
            </div>
            <div class="flex items-center gap-4">
                <span class="hidden sm:inline">{{ now()->format('Y-m-d') }}<span class="text-ink-500"> · </span>{{ now()->format('H:i') }} <span class="text-ink-500">utc</span></span>
                <span class="text-ink-500 hidden sm:inline">|</span>
                <span>impacthon <span class="text-signal-mint">2026</span></span>
            </div>
        </div>
    </div>

    {{-- Navbar --}}
    <nav class="sticky top-0 z-50 border-b border-ink-700 bg-ink-950/85 backdrop-blur-lg">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logo/LogoProteinUX.svg') }}" alt="Proteinux" class="h-9 w-auto">
                    <span class="hidden font-mono text-[10px] uppercase tracking-[0.18em] text-ink-400 sm:inline">structure prediction · v0.1</span>
                </a>

                <div class="flex items-center gap-1">
                    <a href="{{ route('home') }}"
                       class="px-3 py-2 font-mono text-[11px] uppercase tracking-[0.14em] transition-colors {{ request()->routeIs('home') ? 'text-signal-mint' : 'text-ink-300 hover:text-ink-50' }}">
                        / overview
                    </a>
                    <a href="{{ route('proteins.index') }}"
                       class="px-3 py-2 font-mono text-[11px] uppercase tracking-[0.14em] transition-colors {{ request()->routeIs('proteins.*') ? 'text-signal-mint' : 'text-ink-300 hover:text-ink-50' }}">
                        / catalog
                    </a>
                    <a href="{{ route('jobs.create') }}" class="ml-3 btn-primary">
                        <span class="status-dot bg-signal-mint"></span>
                        submit job
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
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

    {{-- Main content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-16 border-t border-ink-700 bg-ink-950/60">
        <div class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid gap-6 font-mono text-[10px] uppercase tracking-[0.14em] text-ink-400 sm:grid-cols-3">
                <div>
                    <div class="text-ink-200">proteinux</div>
                    <div class="mt-1">impacthon 2026 · cathedra camelia</div>
                    <div class="mt-1">medicina personalizada</div>
                </div>
                <div>
                    <div class="text-ink-200">infrastructure</div>
                    <div class="mt-1">cesga finis terrae iii</div>
                    <div class="mt-1">alphafold2 · 3dmol.js · laravel 13</div>
                </div>
                <div class="sm:text-right">
                    <div class="text-ink-200">build</div>
                    <div class="mt-1">{{ substr(md5(config('app.name').now()->format('Y-m-d')), 0, 12) }}</div>
                    <div class="mt-1">{{ now()->format('Y-m-d') }} · open access</div>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
