@props(['score'])

@php
    $score = round($score, 1);
    if ($score >= 90) {
        $classes = 'bg-[#0053D6]/20 text-[#65CBF3] border-[#0053D6]/40';
        $label = 'Very High';
    } elseif ($score >= 70) {
        $classes = 'bg-sky-500/20 text-sky-300 border-sky-500/40';
        $label = 'High';
    } elseif ($score >= 50) {
        $classes = 'bg-amber-500/20 text-amber-300 border-amber-500/40';
        $label = 'Medium';
    } else {
        $classes = 'bg-orange-500/20 text-orange-300 border-orange-500/40';
        $label = 'Low';
    }
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $classes }}">
    <span class="h-2 w-2 rounded-full {{ $score >= 90 ? 'bg-[#0053D6]' : ($score >= 70 ? 'bg-sky-400' : ($score >= 50 ? 'bg-amber-400' : 'bg-orange-400')) }}"></span>
    pLDDT {{ $score }} &mdash; {{ $label }}
</span>
