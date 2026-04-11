@props(['score'])

@php
    $score = round($score, 1);
    if ($score >= 90) {
        $cls = 'border-[#0053D6]/60 text-[#65CBF3] bg-[#0053D6]/15';
        $label = 'very high';
    } elseif ($score >= 70) {
        $cls = 'border-sky-500/60 text-sky-300 bg-sky-500/15';
        $label = 'high';
    } elseif ($score >= 50) {
        $cls = 'border-amber-500/60 text-amber-300 bg-amber-500/15';
        $label = 'medium';
    } else {
        $cls = 'border-orange-500/60 text-orange-300 bg-orange-500/15';
        $label = 'low';
    }
@endphp

<span class="inline-flex items-center gap-2 border px-3 py-1.5 font-mono text-[11px] uppercase tracking-wider {{ $cls }}">
    <span class="status-dot" style="background:currentColor"></span>
    pLDDT {{ $score }} · {{ $label }}
</span>
