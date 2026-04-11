@props(['type' => 'info', 'message'])

@php
    $classes = match($type) {
        'success' => 'border-signal-mint/60 bg-signal-mint/5 text-signal-mint-deep',
        'error'   => 'border-signal-rust/60 bg-signal-rust/5 text-signal-rust',
        'warning' => 'border-signal-amber/60 bg-signal-amber/5 text-signal-amber-dim',
        default   => 'border-sky-500/60 bg-sky-50 text-sky-700',
    };
    $prefix = match($type) {
        'success' => '[ ok ]',
        'error'   => '[ err ]',
        'warning' => '[ warn ]',
        default   => '[ info ]',
    };
@endphp

<div class="flex items-start gap-3 border px-4 py-3 font-mono text-xs {{ $classes }}">
    <span class="font-semibold uppercase tracking-wider">{{ $prefix }}</span>
    <span class="text-ink-800">{{ $message }}</span>
</div>
