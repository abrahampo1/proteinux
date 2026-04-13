@extends('layouts.app')

@section('title', 'Registro')

@section('content')
<div class="mx-auto max-w-md px-4 py-12 sm:px-6 lg:px-8">
    <div class="panel p-6 sm:p-8">
        <h1 class="font-mono text-lg font-semibold uppercase tracking-wider text-ink-900">/ registro</h1>
        <p class="mt-2 text-sm text-ink-600">Crea una cuenta para participar en el foro, compartir predicciones y subir documentos.</p>

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <label for="name" class="label-tag">Nombre</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                       class="mt-1 block w-full border border-ink-300 bg-ink-50 px-3 py-2 font-mono text-sm text-ink-900 placeholder-ink-400 focus:border-signal-mint focus:outline-none focus:ring-1 focus:ring-signal-mint/40">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="username" class="label-tag">Nombre de usuario</label>
                <div class="mt-1 flex items-center">
                    <span class="inline-flex items-center border border-r-0 border-ink-300 bg-ink-100 px-3 py-2 font-mono text-sm text-ink-500">@</span>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required
                           pattern="[a-zA-Z0-9_\-]+"
                           class="block w-full border border-ink-300 bg-ink-50 px-3 py-2 font-mono text-sm text-ink-900 placeholder-ink-400 focus:border-signal-mint focus:outline-none focus:ring-1 focus:ring-signal-mint/40"
                           placeholder="mi_usuario">
                </div>
                <p class="mt-1 text-xs text-ink-500">Solo letras, numeros, guiones y guiones bajos.</p>
                @error('username')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="label-tag">Correo electronico</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
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

            <div>
                <label for="password_confirmation" class="label-tag">Confirmar contrasena</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="mt-1 block w-full border border-ink-300 bg-ink-50 px-3 py-2 font-mono text-sm text-ink-900 placeholder-ink-400 focus:border-signal-mint focus:outline-none focus:ring-1 focus:ring-signal-mint/40">
            </div>

            <button type="submit" class="btn-primary w-full justify-center">
                crear cuenta
            </button>

            <p class="text-center text-sm text-ink-600">
                Ya tienes cuenta?
                <a href="{{ route('login') }}" class="text-signal-mint hover:text-signal-mint-deep">Inicia sesion</a>
            </p>
        </form>
    </div>
</div>
@endsection
