@props(['type' => 'info', 'message'])

@php
    $classes = match($type) {
        'success' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400',
        'error'   => 'border-red-500/30 bg-red-500/10 text-red-400',
        'warning' => 'border-amber-500/30 bg-amber-500/10 text-amber-400',
        default   => 'border-sky-500/30 bg-sky-500/10 text-sky-400',
    };
@endphp

<div class="rounded-lg border px-4 py-3 text-sm {{ $classes }}">
    {{ $message }}
</div>
