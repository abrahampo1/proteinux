@props(['text'])

<span class="group relative cursor-help">
    {{ $slot }}
    <span class="pointer-events-none absolute bottom-full left-1/2 z-50 mb-2 -translate-x-1/2 whitespace-normal rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-normal text-slate-300 opacity-0 shadow-xl transition-opacity group-hover:opacity-100" style="max-width: 280px; width: max-content;">
        {{ $text }}
    </span>
</span>
