@props(['text'])

<span class="group relative cursor-help">
    {{ $slot }}
    <span class="pointer-events-none absolute bottom-full left-1/2 z-50 mb-2 -translate-x-1/2 whitespace-normal border border-ink-600 bg-ink-900 px-3 py-2 font-mono text-[11px] leading-relaxed text-ink-200 opacity-0 shadow-xl transition-opacity group-hover:opacity-100" style="max-width: 280px; width: max-content;">
        {{ $text }}
    </span>
</span>
