@props(['score'])

@php
    $score = round($score, 1);
    if ($score >= 90) {
        $cls = 'border-[#0053D6]/60 text-[#0053D6] bg-[#0053D6]/10';
        $label = 'muy alta';
    } elseif ($score >= 70) {
        $cls = 'border-sky-500/60 text-sky-700 bg-sky-50';
        $label = 'alta';
    } elseif ($score >= 50) {
        $cls = 'border-amber-500/60 text-amber-700 bg-amber-50';
        $label = 'media';
    } else {
        $cls = 'border-orange-500/60 text-orange-700 bg-orange-50';
        $label = 'baja';
    }
@endphp

<span class="inline-flex items-center gap-2 border px-3 py-1.5 font-mono text-[11px] uppercase tracking-wider {{ $cls }}">
    <span class="status-dot" style="background:currentColor"></span>
    pLDDT {{ $score }} · {{ $label }}
</span>
