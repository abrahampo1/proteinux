<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Proteinux') — Protein Structure Prediction</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://3Dmol.org/build/3Dmol-min.js" defer></script>

    @stack('head')
</head>
<body class="min-h-screen bg-slate-950 text-slate-200 antialiased">
    {{-- Navbar --}}
    <nav class="sticky top-0 z-50 border-b border-slate-800 bg-slate-950/80 backdrop-blur-lg">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-white">Proteinux</span>
                </a>

                <div class="flex items-center gap-1">
                    <a href="{{ route('home') }}"
                       class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        Home
                    </a>
                    <a href="{{ route('proteins.index') }}"
                       class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('proteins.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' }}">
                        Catalog
                    </a>
                    <a href="{{ route('jobs.create') }}"
                       class="ml-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-teal-500">
                        Submit Job
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mx-auto max-w-7xl px-4 pt-4">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    @if($errors->has('api'))
        <div class="mx-auto max-w-7xl px-4 pt-4">
            <x-alert type="error" :message="$errors->first('api')" />
        </div>
    @endif

    {{-- Main content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-auto border-t border-slate-800 py-8">
        <div class="mx-auto max-w-7xl px-4 text-center text-sm text-slate-500">
            <p>Proteinux &mdash; IMPACTHON 2026 &middot; Cathedra CAMELIA Medicina Personalizada</p>
            <p class="mt-1">Powered by CESGA Finis Terrae III Simulator</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
