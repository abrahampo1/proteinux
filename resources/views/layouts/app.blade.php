<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="ai-configured" content="{{ app(\App\Support\Ai\AiSettings::class)->isConfigured() ? 'true' : 'false' }}">
    <title>@yield('title', 'Proteinux') · Predicción de estructuras proteicas</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('logo/LogoMol.svg') }}">
    <link rel="mask-icon" href="{{ asset('logo/LogoMol.svg') }}" color="#0d9488">
    <link rel="apple-touch-icon" href="{{ asset('logo/LogoMol.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600,700|ibm-plex-mono:400,500,600|ibm-plex-serif:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://3Dmol.org/build/3Dmol-min.js" defer></script>

    @stack('head')

    {{-- Microsoft Clarity analytics --}}
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "w9nx0k6fda");
    </script>
</head>
<body class="min-h-screen antialiased">

    {{-- Banda superior de estado --}}
    <div class="border-b border-ink-300 bg-ink-100/70 backdrop-blur">
        <div class="mx-auto flex max-w-[1400px] items-center justify-between gap-3 px-4 py-1.5 font-mono text-[9px] uppercase tracking-[0.12em] text-ink-500 sm:gap-4 sm:px-6 sm:text-[10px] sm:tracking-[0.14em] lg:px-8">
            <div class="flex min-w-0 items-center gap-2 sm:gap-4">
                <span class="flex items-center gap-1.5">
                    <span class="status-dot bg-signal-mint shadow-[0_0_6px_rgba(13,148,136,0.55)]"></span>
                    <span class="text-ink-700">nodo · cesga.ft3</span>
                </span>
                <span class="hidden text-ink-400 sm:inline">|</span>
                <span class="hidden sm:inline">pipeline <span class="text-ink-800">af2-proteinux/2.3.1</span></span>
                <span class="hidden text-ink-400 md:inline">|</span>
                <span class="hidden md:inline">gpu <span class="text-ink-800">a100/40gb</span></span>
            </div>
            <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                <span class="hidden sm:inline">{{ now()->format('Y-m-d') }}<span class="text-ink-400"> · </span>{{ now()->format('H:i') }} <span class="text-ink-400">utc</span></span>
                <span class="hidden text-ink-400 sm:inline">|</span>
                <a href="https://impacthon-web.vercel.app" target="_blank" rel="noopener" class="transition-colors hover:text-ink-900">impacthon <span class="text-signal-mint">2026</span></a>
            </div>
        </div>
    </div>

    {{-- Navegación --}}
    <nav class="sticky top-0 z-50 border-b border-ink-300 bg-ink-50/85 backdrop-blur-lg">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between gap-3">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                    <img src="{{ asset('logo/LogoProteinUX.svg') }}" alt="Proteinux" class="h-8 w-auto sm:h-9">
                    <span class="hidden truncate font-mono text-[10px] uppercase tracking-[0.18em] text-ink-500 md:inline">predicción de estructuras · v0.1</span>
                </a>

                {{-- Enlaces en desktop/tablet --}}
                <div class="hidden items-center gap-1 md:flex">
                    <a href="{{ route('home') }}"
                       class="px-3 py-2 font-mono text-[11px] uppercase tracking-[0.14em] transition-colors {{ request()->routeIs('home') ? 'text-signal-mint' : 'text-ink-700 hover:text-ink-900' }}">
                        / inicio
                    </a>
                    <a href="{{ route('proteins.index') }}"
                       class="px-3 py-2 font-mono text-[11px] uppercase tracking-[0.14em] transition-colors {{ request()->routeIs('proteins.*') ? 'text-signal-mint' : 'text-ink-700 hover:text-ink-900' }}">
                        / catálogo
                    </a>
                    <a href="{{ route('settings.ai') }}"
                       class="px-3 py-2 font-mono text-[11px] uppercase tracking-[0.14em] transition-colors {{ request()->routeIs('settings.ai*') ? 'text-signal-mint' : 'text-ink-700 hover:text-ink-900' }}">
                        / ajustes ia
                    </a>
                    <a href="{{ route('jobs.create') }}" class="ml-3 btn-primary">
                        <span class="status-dot bg-signal-mint"></span>
                        enviar trabajo
                    </a>
                </div>

                {{-- Botón hamburguesa en móvil --}}
                <button type="button"
                        id="mobile-menu-toggle"
                        aria-label="Abrir menú de navegación"
                        aria-controls="mobile-menu"
                        aria-expanded="false"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center border border-ink-300 bg-ink-50 text-ink-700 transition-colors hover:border-signal-mint hover:text-signal-mint-deep focus:outline-none focus:ring-1 focus:ring-signal-mint/40 md:hidden">
                    <svg id="mobile-menu-icon-open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="square" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                    <svg id="mobile-menu-icon-close" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="square" d="M6 6l12 12M18 6l-12 12" />
                    </svg>
                </button>
            </div>

            {{-- Panel móvil desplegable --}}
            <div id="mobile-menu" class="hidden border-t border-ink-300 pb-4 pt-2 md:hidden">
                <div class="flex flex-col gap-1">
                    <a href="{{ route('home') }}"
                       class="border-l-2 px-3 py-3 font-mono text-[12px] uppercase tracking-[0.14em] transition-colors {{ request()->routeIs('home') ? 'border-signal-mint text-signal-mint' : 'border-transparent text-ink-700 hover:border-ink-400 hover:text-ink-900' }}">
                        / inicio
                    </a>
                    <a href="{{ route('proteins.index') }}"
                       class="border-l-2 px-3 py-3 font-mono text-[12px] uppercase tracking-[0.14em] transition-colors {{ request()->routeIs('proteins.*') ? 'border-signal-mint text-signal-mint' : 'border-transparent text-ink-700 hover:border-ink-400 hover:text-ink-900' }}">
                        / catálogo
                    </a>
                    <a href="{{ route('settings.ai') }}"
                       class="border-l-2 px-3 py-3 font-mono text-[12px] uppercase tracking-[0.14em] transition-colors {{ request()->routeIs('settings.ai*') ? 'border-signal-mint text-signal-mint' : 'border-transparent text-ink-700 hover:border-ink-400 hover:text-ink-900' }}">
                        / ajustes ia
                    </a>
                    <a href="{{ route('jobs.create') }}" class="mt-2 btn-primary w-full justify-center">
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
    <footer class="mt-12 border-t border-ink-300 bg-ink-100/60 sm:mt-16">
        <div class="mx-auto max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid gap-5 font-mono text-[10px] uppercase leading-relaxed tracking-[0.14em] text-ink-500 sm:grid-cols-3 sm:gap-6">
                <div>
                    <div class="text-ink-800">proteinux</div>
                    <div class="mt-1"><a href="https://impacthon-web.vercel.app" target="_blank" rel="noopener" class="transition-colors hover:text-ink-900">impacthon 2026</a> · cátedra camelia</div>
                    <div class="mt-1">medicina personalizada</div>
                </div>
                <div>
                    <div class="text-ink-800">infraestructura</div>
                    <div class="mt-1">cesga finis terrae iii</div>
                    <div class="mt-1 break-words">alphafold2 · 3dmol.js · laravel 13</div>
                </div>
                <div class="sm:text-right">
                    <div class="text-ink-800">build</div>
                    <div class="mt-1 break-all">{{ substr(md5(config('app.name').now()->format('Y-m-d')), 0, 12) }}</div>
                    <div class="mt-1">{{ now()->format('Y-m-d') }} · acceso abierto</div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            const toggle = document.getElementById('mobile-menu-toggle');
            const menu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('mobile-menu-icon-open');
            const iconClose = document.getElementById('mobile-menu-icon-close');
            if (!toggle || !menu) {
                return;
            }
            const close = () => {
                menu.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
                iconOpen?.classList.remove('hidden');
                iconClose?.classList.add('hidden');
            };
            const open = () => {
                menu.classList.remove('hidden');
                toggle.setAttribute('aria-expanded', 'true');
                iconOpen?.classList.add('hidden');
                iconClose?.classList.remove('hidden');
            };
            toggle.addEventListener('click', () => {
                menu.classList.contains('hidden') ? open() : close();
            });
            // Cerrar automáticamente al pasar a >= md (768px)
            const mql = window.matchMedia('(min-width: 768px)');
            mql.addEventListener('change', (e) => {
                if (e.matches) {
                    close();
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
