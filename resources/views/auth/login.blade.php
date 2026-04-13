@extends('layouts.app')

@section('title', 'Acceso')

@section('content')
<div class="mx-auto max-w-md px-4 py-12 sm:px-6 lg:px-8">
    <div class="panel p-6 sm:p-8">
        <h1 class="font-mono text-lg font-semibold uppercase tracking-wider text-ink-900">/ acceso</h1>
        <p class="mt-2 text-sm text-ink-600">Inicia sesion en tu instancia de Proteinux.</p>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <label for="email" class="label-tag">Correo electronico</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 block w-full border border-ink-300 bg-ink-50 px-3 py-2 font-mono text-sm text-ink-900 placeholder-ink-400 focus:border-signal-mint focus:outline-none focus:ring-1 focus:ring-signal-mint/40">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="label-tag">Contrasena</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full border border-ink-300 bg-ink-50 px-3 py-2 font-mono text-sm text-ink-900 placeholder-ink-400 focus:border-signal-mint focus:outline-none focus:ring-1 focus:ring-signal-mint/40">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember"
                       class="h-4 w-4 border-ink-300 text-signal-mint focus:ring-signal-mint/40">
                <label for="remember" class="text-sm text-ink-600">Recordarme</label>
            </div>

            <button type="submit" class="btn-primary w-full justify-center">
                iniciar sesion
            </button>

            <p class="text-center text-sm text-ink-600">
                No tienes cuenta?
                <a href="{{ route('register') }}" class="text-signal-mint hover:text-signal-mint-deep">Registrate</a>
            </p>
        </form>
    </div>
</div>
@endsection
