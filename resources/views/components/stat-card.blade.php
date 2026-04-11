@props(['value', 'label', 'icon' => null])

<div class="border border-ink-700 bg-ink-900/60 p-5">
    <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-ink-400">{{ $label }}</div>
    <div class="mt-2 font-mono text-3xl text-ink-50 tabular-nums">{{ $value }}</div>
</div>
