@props(['type' => 'info', 'message'])

@php
    $classes = match($type) {
        'success' => 'border-signal-mint/50 bg-signal-mint/5 text-signal-mint',
        'error'   => 'border-signal-rust/50 bg-signal-rust/5 text-signal-rust',
        'warning' => 'border-signal-amber/50 bg-signal-amber/5 text-signal-amber',
        default   => 'border-sky-500/50 bg-sky-500/5 text-sky-300',
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
    <span class="text-ink-100">{{ $message }}</span>
</div>
